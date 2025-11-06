<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomFieldResource\Pages;
use App\Filament\Resources\CustomFieldResource\RelationManagers;
use App\Models\CustomField;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Gate;

class CustomFieldResource extends Resource
{
    protected static ?string $model = CustomField::class;

    protected static ?string $navigationLabel = 'Custom Fields';

    protected static ?string $navigationIcon = 'heroicon-o-adjustments-horizontal';

    protected static ?string $navigationGroup = 'Settings';

    public static function canViewAny(): bool
    {
        return Gate::allows('view_any_custom_fields');
    }

    public static function canView($record): bool
    {
        return Gate::allows('view_custom_fields');
    }

    public static function canCreate(): bool
    {
        return Gate::allows('create_custom_fields');
    }

    public static function canEdit($record): bool
    {
        return Gate::allows('update_custom_fields');
    }

    public static function canDelete($record): bool
    {
        return Gate::allows('delete_custom_fields');
    }

    public static function canDeleteAny(): bool
    {
        return Gate::allows('bulk_delete_custom_fields');
    }

    public static function canForceDelete($record): bool
    {
        return Gate::allows('force_delete_custom_fields');
    }

    public static function canForceDeleteAny(): bool
    {
        return Gate::allows('force_delete_custom_fields');
    }

    public static function canRestore($record): bool
    {
        return Gate::allows('restore_custom_fields');
    }

    public static function canRestoreAny(): bool
    {
        return Gate::allows('bulk_restore_custom_fields');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Toggle::make('is_active')
                    ->label('Is Active')
                    ->helperText('Enable or disable this custom field')
                    ->default(true),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->withoutGlobalScope(\App\Models\ActiveScope::class);

        if (Gate::allows('view_deleted_custom_fields')) {
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
            'index' => Pages\ListCustomFields::route('/'),
            'create' => Pages\CreateCustomField::route('/create'),
            'edit' => Pages\EditCustomField::route('/{record}/edit'),
        ];
    }
}