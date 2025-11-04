<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Filament\Forms\Components\Tabs;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'User Management';

    protected static ?int $navigationSort = 1;

    public static function canViewAny(): bool
    {
        return Gate::allows('view_any_users');
    }

    public static function canView($record): bool
    {
        return Gate::allows('view_users');
    }

    public static function canCreate(): bool
    {
        return Gate::allows('create_users');
    }

    public static function canEdit($record): bool
    {
        return Gate::allows('update_users');
    }

    public static function canDelete($record): bool
    {
        return Gate::allows('delete_users');
    }

    public static function canDeleteAny(): bool
    {
        return Gate::allows('bulk_delete_users');
    }

    public static function canForceDelete($record): bool
    {
        return Gate::allows('force_delete_users');
    }

    public static function canForceDeleteAny(): bool
    {
        return Gate::allows('force_delete_users');
    }

    public static function canRestore($record): bool
    {
        return Gate::allows('restore_users');
    }

    public static function canRestoreAny(): bool
    {
        return Gate::allows('bulk_restore_users');
    }

    public static function form(Form $form): Form
    {
        $tabs = [];
        $tabs[] = Tabs\Tab::make('User Info')
            ->icon('heroicon-o-user')
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->unique(User::class, 'email', ignoreRecord: true)
                    ->maxLength(255),
                Forms\Components\Toggle::make('is_active')
                    ->label('Is Active')
                    ->helperText('Enable or disable this user account')
                    ->default(true),
                Forms\Components\Toggle::make('is_verified')
                    ->label('Email Verified')
                    ->helperText('Check to mark email as verified')
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        $set('email_verified_at', $state ? now() : null);
                    })
                    ->dehydrated(false)
                    ->afterStateHydrated(function ($component, $state, $record) {
                        if ($record) {
                            $component->state(!is_null($record->email_verified_at));
                        }
                    })
                    ->default(false),
                Forms\Components\DateTimePicker::make('email_verified_at')
                    ->label('Email Verified At')
                    ->disabled()
                    ->timezone('Africa/Cairo')
                    ->displayFormat('M j, Y - H:i')
                    ->helperText('Automatically set when email verified toggle is enabled')
                    ->dehydrateStateUsing(function ($state, callable $get) {
                        return $get('is_verified') ? ($state ?: now()) : null;
                    }),
                Forms\Components\Section::make('Password Management')
                    ->description('Manage user password settings')
                    ->schema([
                        Forms\Components\Placeholder::make('password_info')
                            ->label('Current Password Status')
                            ->content(function ($record) {
                                if ($record && $record->password) {
                                    $passwordLength = strlen($record->password);
                                    $passwordAge = $record->updated_at->diffForHumans();
                                    return "🔒 Password is set (Hash length: {$passwordLength} chars)\n📅 Last updated: {$passwordAge}";
                                }
                                return '❌ No password set';
                            }),
                        Forms\Components\TextInput::make('password')
                            ->label('New Password')
                            ->password()
                            ->revealable()
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $context): bool => $context === 'create')
                            ->maxLength(255)
                            ->placeholder(fn (string $context) => $context === 'edit' ? 'Enter new password to change' : 'Enter password')
                            ->helperText('🔑 Leave blank in edit mode to keep current password'),
                        Forms\Components\Actions::make([
                            Forms\Components\Actions\Action::make('generate_password')
                                ->label('🎲 Generate Random Password')
                                ->icon('heroicon-o-key')
                                ->color('warning')
                                ->size('sm')
                                ->action(function (callable $set) {
                                    $randomPassword = Str::random(12);
                                    $set('password', $randomPassword);
                                    \Filament\Notifications\Notification::make()
                                        ->title('Password Generated!')
                                        ->body("New password: {$randomPassword}")
                                        ->success()
                                        ->persistent()
                                        ->send();
                                }),
                        ])->columnSpanFull(),
                    ]),
            ]);
        $tabs[] = Tabs\Tab::make('Roles')
            ->icon('heroicon-o-shield-check')
            ->schema([
                            Forms\Components\Select::make('roles')
                ->relationship('roles', 'name', fn (Builder $query) => $query->whereNull('roles.deleted_at')->where('roles.is_active', 1))
                ->multiple()
                ->preload()
            ]);
        return $form
            ->schema([
                Tabs::make('User Management')->tabs($tabs)->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->tooltip('Click to copy'),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->tooltip('Click to copy'),
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
                    ->afterStateUpdated(function ($record, $state) {
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
                    ->getStateUsing(fn ($record) => !is_null($record->email_verified_at)),
                
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
                    ->toggleable(),
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
            ->filters(array_merge(
                static::getTableFilters(),
                [
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
                    ->relationship('roles', 'name')
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
                ]
            ))
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
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
                        }),
                    
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
                        }),
                    Tables\Actions\BulkAction::make('assign_role')
                        ->label('Assign Role')
                        ->icon('heroicon-o-shield-check')
                        ->color('info')
                        ->form([
                            Forms\Components\Select::make('role')
                                ->label('Select Role')
                                ->options(Role::all()->pluck('name', 'name'))
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
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('60s')
            ->persistFiltersInSession()
            ->persistSortInSession()
            ->persistSearchInSession();
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->withoutGlobalScope(\App\Models\ActiveScope::class);
            
        if (Gate::allows('view_deleted_users')) {
            $query->withoutGlobalScope(SoftDeletingScope::class);
        }
        
        return $query;
    }

    protected static function getTableFilters(): array
    {
        $filters = [];
        
        if (Gate::allows('view_deleted_users')) {
            $filters[] = Tables\Filters\TrashedFilter::make();
        }
        
        return $filters;
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
