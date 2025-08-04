<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Filament\Resources\UserResource\Widgets\UserStatsWidget;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\Components\Tab;
use App\Models\User;
use App\Models\Role;
use Filament\Forms;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Gate;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            UserStatsWidget::class,
        ];
    }

    public function getTabs(): array
    {
        // تحقق من صلاحية رؤية السجلات المحذوفة
        $tabs = [
            'active' => Tab::make('Active Users')
                ->icon('heroicon-o-check-circle')
                ->badge(User::whereNull('deleted_at')->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNull('deleted_at')),
        ];

        if (Gate::allows('view_deleted_users')) {
            $tabs['deleted'] = Tab::make('Deleted Users')
                ->icon('heroicon-o-trash')
                ->badge(User::onlyTrashed()->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->onlyTrashed());
        }

        return $tabs;
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->tooltip('Click to copy')
                    ->color(fn ($record) => $record->is_active ? 'primary' : 'gray')
                    ->icon(fn ($record) => $record->is_active ? null : 'heroicon-s-no-symbol'),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('roles.name')
                    ->badge()
                    ->separator(',')
                    ->color('success')
                    ->label('Roles')
                    ->searchable(),
                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Active')
                    ->sortable()
                    ->onColor('success')
                    ->offColor('danger')
                    ->onIcon('heroicon-s-check-circle')
                    ->offIcon('heroicon-s-x-circle')
                    ->disabled(fn ($record) => $record->trashed()) // منع تفعيل السجلات المحذوفة
                    ->afterStateUpdated(function ($record, $state) {
                        // التحقق من أن السجل غير محذوف
                        if ($record->trashed()) {
                            \Filament\Notifications\Notification::make()
                                ->title('Cannot Activate Deleted User')
                                ->body("User '{$record->name}' is deleted and cannot be activated. Please restore it first.")
                                ->danger()
                                ->send();
                            return false;
                        }
                        
                        // إرسال إشعار بالتحديث
                        \Filament\Notifications\Notification::make()
                            ->title($state ? 'User Activated' : 'User Deactivated')
                            ->body("User '{$record->name}' has been " . ($state ? 'activated' : 'deactivated'))
                            ->color($state ? 'success' : 'warning')
                            ->send();
                    }),
                Tables\Columns\ToggleColumn::make('is_verified')
                    ->label('Email Verified')
                    ->sortable()
                    ->onColor('success')
                    ->offColor('danger')
                    ->onIcon('heroicon-s-check-circle')
                    ->offIcon('heroicon-s-x-circle')
                    ->updateStateUsing(function ($record, $state) {
                        $record->email_verified_at = $state ? now() : null;
                        $record->save();
                        \Filament\Notifications\Notification::make()
                            ->title($state ? 'Email Verified ✅' : 'Email Unverified ❌')
                            ->body($state ? 'User email has been verified successfully' : 'User email verification has been removed')
                            ->color($state ? 'success' : 'warning')
                            ->send();
                        return $state;
                    })
                    ->getStateUsing(fn ($record) => !is_null($record->email_verified_at))
                    ->searchable(),
                Tables\Columns\TextColumn::make('email_verified_at')
                    ->label('Verified At')
                    ->sortable()
                    ->placeholder('❌ Not verified')
                    ->formatStateUsing(function ($state, $record) {
                        if ($state) {
                            $localTime = $state->setTimezone('Africa/Cairo');
                            return '✅ ' . $localTime->format('M j, Y - H:i');
                        }
                        return '❌ Not verified';
                    })
                    ->color(fn ($record) => $record->email_verified_at ? 'success' : 'danger')
                    ->weight('medium')
                    ->toggleable()
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('deleted_at')
                    ->label('Deleted At')
                    ->dateTime('M j, Y g:i A')
                    ->timezone('Africa/Cairo')
                    ->sortable()
                    ->visible(fn () => $this->activeTab === 'deleted'),
                Tables\Columns\TextColumn::make('deletedBy.name')
                    ->label('Deleted By')
                    ->default('System')
                    ->visible(fn () => $this->activeTab === 'deleted'),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('M j, Y H:i')
                    ->timezone('Africa/Cairo')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->tooltip(fn ($record) => $record->created_at->setTimezone('Africa/Cairo')->diffForHumans()),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime('M j, Y H:i')
                    ->timezone('Africa/Cairo')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->tooltip(fn ($record) => $record->updated_at->setTimezone('Africa/Cairo')->diffForHumans()),
            ])
            ->filters([
                Tables\Filters\Filter::make('name')
                    ->form([
                        Forms\Components\TextInput::make('name')
                            ->label('User Name')
                            ->placeholder('Enter user name...'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['name'],
                                fn (Builder $query, $name): Builder => $query->where('name', 'like', "%{$name}%"),
                            );
                    }),
                Tables\Filters\Filter::make('email')
                    ->form([
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->placeholder('Enter email...'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['email'],
                                fn (Builder $query, $email): Builder => $query->where('email', 'like', "%{$email}%"),
                            );
                    }),
                Tables\Filters\SelectFilter::make('roles')
                    ->relationship('roles', 'name', fn (Builder $query) => $query->whereNull('roles.deleted_at')->where('roles.is_active', 1))
                    ->multiple()
                    ->preload()
                    ->label('User Roles'),
                Tables\Filters\TernaryFilter::make('email_verified_at')
                    ->label('Email Verified')
                    ->placeholder('All users')
                    ->trueLabel('Verified users only')
                    ->falseLabel('Unverified users only')
                    ->nullable(),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status')
                    ->placeholder('All users')
                    ->trueLabel('Active users only')
                    ->falseLabel('Inactive users only')
                    ->nullable(),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Created From'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Created Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
                Tables\Filters\Filter::make('updated_at')
                    ->form([
                        Forms\Components\DatePicker::make('updated_from')
                            ->label('Updated From'),
                        Forms\Components\DatePicker::make('updated_until')
                            ->label('Updated Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['updated_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('updated_at', '>=', $date),
                            )
                            ->when(
                                $data['updated_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('updated_at', '<=', $date),
                            );
                    }),
                Tables\Filters\SelectFilter::make('permissions')
                    ->relationship('permissions', 'name', fn (Builder $query) => $query->whereNull('permissions.deleted_at')->where('permissions.is_active', 1))
                    ->multiple()
                    ->preload()
                    ->label('Direct Permissions')
                    ->placeholder('Select permissions to filter by')
                    ->searchable(),
                Tables\Filters\Filter::make('admin_users')
                    ->label('Admin Users')
                    ->query(fn (Builder $query): Builder => $query->whereHas('roles', fn ($q) => $q->whereIn('name', ['Super Admin', 'Admin'])))
                    ->toggle(),
                Tables\Filters\Filter::make('sales_users')
                    ->label('Sales Users')
                    ->query(fn (Builder $query): Builder => $query->whereHas('roles', fn ($q) => $q->whereIn('name', ['Sales Manager', 'Sales'])))
                    ->toggle(),
                Tables\Filters\Filter::make('dataentry_users')
                    ->label('Data Entry Users')
                    ->query(fn (Builder $query): Builder => $query->whereHas('roles', fn ($q) => $q->whereIn('name', ['Dataentry Manager', 'Dataentry'])))
                    ->toggle(),
                Tables\Filters\Filter::make('users_without_roles')
                    ->label('Users Without Roles')
                    ->query(fn (Builder $query): Builder => $query->doesntHave('roles'))
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->visible(fn ($record) => Gate::allows('view_users') && $this->activeTab !== 'deleted' && $record->is_active),
                Tables\Actions\EditAction::make()
                    ->visible(fn ($record) => Gate::allows('update_users') && $this->activeTab !== 'deleted' && $record->is_active),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn () => Gate::allows('delete_users') && $this->activeTab !== 'deleted'),
                Tables\Actions\RestoreAction::make()
                    ->visible(fn () => Gate::allows('restore_users') && $this->activeTab === 'deleted')
                    ->after(function ($record) {
                        // إظهار تنبيه بأن السجل تم استعادته لكن لا يزال غير نشط
                        \Filament\Notifications\Notification::make()
                            ->title('User Restored')
                            ->body("User '{$record->name}' has been restored but remains inactive. Click the toggle to activate if needed.")
                            ->warning()
                            ->persistent()
                            ->actions([
                                \Filament\Notifications\Actions\Action::make('activate')
                                    ->label('Activate Now')
                                    ->button()
                                    ->action(function () use ($record) {
                                        $record->update(['is_active' => true]);
                                        \Filament\Notifications\Notification::make()
                                            ->title('User Activated')
                                            ->body("User '{$record->name}' is now active")
                                            ->success()
                                            ->send();
                                    }),
                            ])
                            ->send();
                    }),
                Tables\Actions\ForceDeleteAction::make()
                    ->visible(fn () => Gate::allows('force_delete_users') && $this->activeTab === 'deleted'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => $this->activeTab !== 'deleted'),
                    Tables\Actions\RestoreBulkAction::make()
                        ->visible(fn () => $this->activeTab === 'deleted'),
                    Tables\Actions\ForceDeleteBulkAction::make()
                        ->visible(fn () => $this->activeTab === 'deleted'),
                    Tables\Actions\BulkAction::make('verify_email')
                        ->label('✅ Verify Email')
                        ->icon('heroicon-o-check-badge')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Verify Selected Users Email')
                        ->modalDescription('Are you sure you want to verify the email for all selected users?')
                        ->action(function ($records) {
                            $count = 0;
                            foreach ($records as $record) {
                                if (is_null($record->email_verified_at)) {
                                    $record->update(['email_verified_at' => now()]);
                                    $count++;
                                }
                            }
                            \Filament\Notifications\Notification::make()
                                ->title('Email Verification Updated')
                                ->body("Successfully verified {$count} user(s)")
                                ->success()
                                ->send();
                        })
                        ->visible(fn () => $this->activeTab !== 'deleted'),
                    
                    Tables\Actions\BulkAction::make('unverify_email')
                        ->label('❌ Unverify Email')
                        ->icon('heroicon-o-x-mark')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Unverify Selected Users Email')
                        ->modalDescription('Are you sure you want to remove email verification for all selected users?')
                        ->action(function ($records) {
                            $count = 0;
                            foreach ($records as $record) {
                                if (!is_null($record->email_verified_at)) {
                                    $record->update(['email_verified_at' => null]);
                                    $count++;
                                }
                            }
                            \Filament\Notifications\Notification::make()
                                ->title('Email Verification Removed')
                                ->body("Successfully unverified {$count} user(s)")
                                ->warning()
                                ->send();
                        })
                        ->visible(fn () => $this->activeTab !== 'deleted'),
                    Tables\Actions\BulkAction::make('assign_role')
                        ->label('Assign Role')
                        ->icon('heroicon-o-shield-check')
                        ->color('info')
                        ->form([
                            Forms\Components\Select::make('role')
                                ->label('Select Role')
                                ->options(Role::whereNull('roles.deleted_at')->where('roles.is_active', 1)->pluck('name', 'name'))
                                ->required(),
                        ])
                        ->action(function (array $data, $records) {
                            foreach ($records as $record) {
                                $record->assignRole($data['role']);
                            }
                            \Filament\Notifications\Notification::make()
                                ->title('Role Assigned')
                                ->body("Role '{$data['role']}' has been assigned to selected users")
                                ->success()
                                ->send();
                        })
                        ->visible(fn () => $this->activeTab !== 'deleted'),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->persistFiltersInSession()
            ->persistSortInSession()
            ->persistSearchInSession();
    }

    public function getEloquentQuery(): Builder
    {
        return User::query()
            ->with(['roles', 'permissions']) // تحميل العلاقات مسبقاً
            ->withoutGlobalScope(\Illuminate\Database\Eloquent\SoftDeletingScope::class)
            ->withoutGlobalScope(\App\Models\ActiveScope::class);
    }
}
