<?php

namespace App\Filament\Resources\RoleResource\Pages;

use App\Filament\Resources\RoleResource;
use App\Filament\Resources\RoleResource\Widgets\RoleStatsWidget;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\Components\Tab;
use App\Models\Role;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Gate;

class ListRoles extends ListRecords
{
    protected static string $resource = RoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            RoleStatsWidget::class,
        ];
    }

    public function getTabs(): array
    {
        $tabs = [
            'active' => Tab::make('Active Roles')
                ->icon('heroicon-o-check-circle')
                ->badge(Role::whereNull('deleted_at')->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNull('deleted_at')),
        ];

        if (Gate::allows('view_deleted_roles')) {
            $tabs['deleted'] = Tab::make('Deleted Roles')
                ->icon('heroicon-o-trash')
                ->badge(Role::onlyTrashed()->count())
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
                Tables\Columns\TextColumn::make('permissions_count')
                    ->counts('permissions')
                    ->label('Permissions')
                    ->badge()
                    ->color('success')
                    ->sortable(),
                Tables\Columns\TextColumn::make('permissions.name')
                    ->badge()
                    ->separator(',')
                    ->color('success')
                    ->label('Permissions')
                    ->searchable()
                    ->limit(3)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (is_array($state) && count($state) > 3) {
                            return 'Total permissions: ' . count($state);
                        }
                        return null;
                    })
                    ->visible(fn () => $this->activeTab === 'active'),
                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Active')
                    ->sortable()
                    ->onColor('success')
                    ->offColor('danger')
                    ->onIcon('heroicon-s-check-circle')
                    ->offIcon('heroicon-s-x-circle')
                    ->disabled(fn ($record) => $record->trashed())
                    ->afterStateUpdated(function ($record, $state) {
                        if ($record->trashed()) {
                            \Filament\Notifications\Notification::make()
                                ->title('Cannot Activate Deleted Role')
                                ->body("Role '{$record->name}' is deleted and cannot be activated. Please restore it first.")
                                ->danger()
                                ->send();
                            return false;
                        }
                        
                        \Filament\Notifications\Notification::make()
                            ->title($state ? 'Role Activated' : 'Role Deactivated')
                            ->body("Role '{$record->name}' has been " . ($state ? 'activated' : 'deactivated'))
                            ->color($state ? 'success' : 'warning')
                            ->send();
                    }),
                Tables\Columns\TextColumn::make('users_count')
                    ->counts('users')
                    ->label('Users')
                    ->badge()
                    ->color('success')
                    ->sortable(),
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
                    ->placeholder('All roles')
                    ->trueLabel('Active roles only')
                    ->falseLabel('Inactive roles only'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->visible(fn ($record) => Gate::allows('view_roles') && $this->activeTab !== 'deleted' && $record->is_active),
                Tables\Actions\EditAction::make()
                    ->visible(fn ($record) => Gate::allows('update_roles') && $this->activeTab !== 'deleted' && $record->is_active),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn () => Gate::allows('delete_roles') && $this->activeTab !== 'deleted'),
                Tables\Actions\RestoreAction::make()
                    ->visible(fn () => Gate::allows('restore_roles') && $this->activeTab === 'deleted'),
                Tables\Actions\ForceDeleteAction::make()
                    ->visible(fn () => Gate::allows('force_delete_roles') && $this->activeTab === 'deleted'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => Gate::allows('bulk_delete_roles') && $this->activeTab !== 'deleted'),
                    Tables\Actions\RestoreBulkAction::make()
                        ->visible(fn () => Gate::allows('bulk_restore_roles') && $this->activeTab === 'deleted'),
                    Tables\Actions\ForceDeleteBulkAction::make()
                        ->visible(fn () => Gate::allows('bulk_delete_roles') && $this->activeTab === 'deleted'),
                ]),
            ]);
    }

    public function getEloquentQuery(): Builder
    {
        $query = Role::query()
            ->with(['permissions', 'users'])
            ->withoutGlobalScope(\App\Models\ActiveScope::class);
            
        if (Gate::allows('view_deleted_roles')) {
            $query->withoutGlobalScope(\Illuminate\Database\Eloquent\SoftDeletingScope::class);
        }
        
        return $query;
    }
}
