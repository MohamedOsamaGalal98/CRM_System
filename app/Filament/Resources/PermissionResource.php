<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PermissionResource\Pages;
use App\Filament\Resources\PermissionResource\RelationManagers;
use App\Models\Permission;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Gate;

class PermissionResource extends Resource
{
    protected static ?string $model = Permission::class;

    protected static ?string $navigationIcon = 'heroicon-o-key';

    protected static ?string $navigationGroup = 'User Management';

    protected static ?int $navigationSort = 3;

    public static function canViewAny(): bool
    {
        return Gate::allows('view_any_permissions');
    }

    public static function canView($record): bool
    {
        return Gate::allows('view_permissions');
    }

    public static function canCreate(): bool
    {
        return Gate::allows('create_permissions');
    }

    public static function canEdit($record): bool
    {
        return Gate::allows('update_permissions');
    }

    public static function canDelete($record): bool
    {
        return Gate::allows('delete_permissions');
    }

    public static function canDeleteAny(): bool
    {
        return Gate::allows('bulk_delete_permissions');
    }

    public static function canForceDelete($record): bool
    {
        return Gate::allows('force_delete_permissions');
    }

    public static function canForceDeleteAny(): bool
    {
        return Gate::allows('force_delete_permissions');
    }

    public static function canRestore($record): bool
    {
        return Gate::allows('restore_permissions');
    }

    public static function canRestoreAny(): bool
    {
        return Gate::allows('bulk_restore_permissions');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->unique(Permission::class, 'name', ignoreRecord: true),
                Forms\Components\Toggle::make('is_active')
                    ->label('Is Active')
                    ->helperText('Enable or disable this permission')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('guard_name')
                    ->label('Guard')
                    ->badge()
                    ->color('info')
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
                    }),
                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Active')
                    ->sortable()
                    ->onColor('success')
                    ->offColor('danger')
                    ->onIcon('heroicon-s-check-circle')
                    ->offIcon('heroicon-s-x-circle'),
                Tables\Columns\TextColumn::make('roles_count')
                    ->counts('roles')
                    ->label('Roles')
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
            ])
            ->filters([
                ...Gate::allows('view_deleted_permissions') ? [Tables\Filters\TrashedFilter::make()] : [],
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status')
                    ->placeholder('All permissions')
                    ->trueLabel('Active permissions only')
                    ->falseLabel('Inactive permissions only'),
            ])
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
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->withoutGlobalScope(\App\Models\ActiveScope::class);
            
        if (Gate::allows('view_deleted_permissions')) {
            $query->withoutGlobalScope(SoftDeletingScope::class);
        }
        
        return $query;
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
            'index' => Pages\ListPermissions::route('/'),
            'create' => Pages\CreatePermission::route('/create'),
            'edit' => Pages\EditPermission::route('/{record}/edit'),
        ];
    }
}
