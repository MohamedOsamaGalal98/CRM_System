<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoleResource\Pages;
use App\Filament\Resources\RoleResource\RelationManagers;
use App\Models\Role;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Permission;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationGroup = 'User Management';

    protected static ?int $navigationSort = 2;

    public static function canViewAny(): bool
    {
        return Gate::allows('view_any_roles');
    }

    public static function canView($record): bool
    {
        return Gate::allows('view_roles');
    }

    public static function canCreate(): bool
    {
        return Gate::allows('create_roles');
    }

    public static function canEdit($record): bool
    {
        return Gate::allows('update_roles');
    }

    public static function canDelete($record): bool
    {
        return Gate::allows('delete_roles');
    }

    public static function canDeleteAny(): bool
    {
        return Gate::allows('bulk_delete_roles');
    }

    public static function canForceDelete($record): bool
    {
        return Gate::allows('force_delete_roles');
    }

    public static function canForceDeleteAny(): bool
    {
        return Gate::allows('force_delete_roles');
    }

    public static function canRestore($record): bool
    {
        return Gate::allows('restore_roles');
    }

    public static function canRestoreAny(): bool
    {
        return Gate::allows('bulk_restore_roles');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->unique(Role::class, 'name', ignoreRecord: true),
                Forms\Components\Toggle::make('is_active')
                    ->label('Is Active')
                    ->helperText('Enable or disable this role')
                    ->default(true),
                                                        Forms\Components\Select::make('permissions')
                ->relationship('permissions', 'name', fn (Builder $query) => $query->whereNull('permissions.deleted_at')->where('permissions.is_active', 1))
                ->multiple()
                ->options(Permission::whereNull('permissions.deleted_at')->where('permissions.is_active', 1)->pluck('name', 'id'))
                ->searchable()
                ->preload(),
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
                    }),
                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Active')
                    ->sortable()
                    ->onColor('success')
                    ->offColor('danger')
                    ->onIcon('heroicon-s-check-circle')
                    ->offIcon('heroicon-s-x-circle'),
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
            ])
            ->filters([
                ...Gate::allows('view_deleted_roles') ? [Tables\Filters\TrashedFilter::make()] : [],
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status')
                    ->placeholder('All roles')
                    ->trueLabel('Active roles only')
                    ->falseLabel('Inactive roles only'),
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
            
        if (Gate::allows('view_deleted_roles')) {
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
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }
}
