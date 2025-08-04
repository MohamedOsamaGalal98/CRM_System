<?php

namespace App\Filament\Resources\PermissionResource\Pages;

use App\Filament\Resources\PermissionResource;
use App\Filament\Resources\PermissionResource\Widgets\PermissionStatsWidget;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\Components\Tab;
use App\Models\Permission;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Gate;

class ListPermissions extends ListRecords
{
    protected static string $resource = PermissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            PermissionStatsWidget::class,
        ];
    }

    public function getTabs(): array
    {
        $tabs = [
            'active' => Tab::make('Active Permissions')
                ->icon('heroicon-o-check-circle')
                ->badge(Permission::whereNull('deleted_at')->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNull('deleted_at')),
        ];

        if (Gate::allows('view_deleted_permissions')) {
            $tabs['deleted'] = Tab::make('Deleted Permissions')
                ->icon('heroicon-o-trash')
                ->badge(Permission::onlyTrashed()->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->onlyTrashed());
        }

        return $tabs;
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->color(fn ($record) => $record->is_active ? 'primary' : 'gray')
                    ->icon(fn ($record) => $record->is_active ? null : 'heroicon-s-no-symbol'),
                Tables\Columns\TextColumn::make('guard_name')
                    ->label('Guard')
                    ->badge()
                    ->color('info')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('roles_count')
                    ->counts('roles')
                    ->label('Roles')
                    ->badge()
                    ->color('success')
                    ->sortable(),
                Tables\Columns\TextColumn::make('roles.name')
                    ->badge()
                    ->separator(',')
                    ->color('success')
                    ->label('Roles')
                    ->searchable()
                    ->limit(3)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (is_array($state) && count($state) > 3) {
                            return 'Total roles: ' . count($state);
                        }
                        return null;
                    })
                    ->getStateUsing(function ($record) {
                        return $record->roles()->where('is_active', 1)->pluck('name')->toArray();
                    }),
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
                                ->title('Cannot Activate Deleted Permission')
                                ->body("Permission '{$record->name}' is deleted and cannot be activated. Please restore it first.")
                                ->danger()
                                ->send();
                            return false;
                        }
                        
                        // إرسال إشعار بالتحديث
                        \Filament\Notifications\Notification::make()
                            ->title($state ? 'Permission Activated' : 'Permission Deactivated')
                            ->body("Permission '{$record->name}' has been " . ($state ? 'activated' : 'deactivated'))
                            ->color($state ? 'success' : 'warning')
                            ->send();
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->label('Deleted At')
                    ->dateTime('M j, Y g:i A')
                    ->sortable()
                    ->visible(fn () => $this->activeTab === 'deleted'),
                Tables\Columns\TextColumn::make('deletedBy.name')
                    ->label('Deleted By')
                    ->default('System')
                    ->visible(fn () => $this->activeTab === 'deleted'),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status')
                    ->placeholder('All permissions')
                    ->trueLabel('Active permissions only')
                    ->falseLabel('Inactive permissions only'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->visible(fn ($record) => Gate::allows('view_permissions') && $this->activeTab !== 'deleted' && $record->is_active),
                Tables\Actions\EditAction::make()
                    ->visible(fn ($record) => Gate::allows('update_permissions') && $this->activeTab !== 'deleted' && $record->is_active),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn () => Gate::allows('delete_permissions') && $this->activeTab !== 'deleted'),
                Tables\Actions\RestoreAction::make()
                    ->visible(fn () => Gate::allows('restore_permissions') && $this->activeTab === 'deleted'),
                Tables\Actions\ForceDeleteAction::make()
                    ->visible(fn () => Gate::allows('force_delete_permissions') && $this->activeTab === 'deleted'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => Gate::allows('bulk_delete_permissions') && $this->activeTab !== 'deleted'),
                    Tables\Actions\RestoreBulkAction::make()
                        ->visible(fn () => Gate::allows('bulk_restore_permissions') && $this->activeTab === 'deleted'),
                    Tables\Actions\ForceDeleteBulkAction::make()
                        ->visible(fn () => Gate::allows('bulk_delete_permissions') && $this->activeTab === 'deleted'),
                ]),
            ]);
    }

    public function getEloquentQuery(): Builder
    {
        return Permission::query()
            ->with(['roles', 'users']) // تحميل العلاقات مسبقاً
            ->withoutGlobalScope(\Illuminate\Database\Eloquent\SoftDeletingScope::class)
            ->withoutGlobalScope(\App\Models\ActiveScope::class);
    }
}
