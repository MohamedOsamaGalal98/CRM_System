<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LeadSourceResource\Pages;
use App\Filament\Resources\LeadSourceResource\RelationManagers;
use App\Models\LeadSource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Gate;

class LeadSourceResource extends Resource
{
    protected static ?string $model = LeadSource::class;

    protected static ?string $navigationIcon = 'heroicon-o-funnel';

    protected static ?string $navigationGroup = 'Settings';

    public static function canViewAny(): bool
    {
        return Gate::allows('view_any_lead_sources');
    }

    public static function canView($record): bool
    {
        return Gate::allows('view_lead_sources');
    }

    public static function canCreate(): bool
    {
        return Gate::allows('create_lead_sources');
    }

    public static function canEdit($record): bool
    {
        return Gate::allows('update_lead_sources');
    }

    public static function canDelete($record): bool
    {
        return Gate::allows('delete_lead_sources');
    }

    public static function canDeleteAny(): bool
    {
        return Gate::allows('bulk_delete_lead_sources');
    }

    public static function canForceDelete($record): bool
    {
        return Gate::allows('force_delete_lead_sources');
    }

    public static function canForceDeleteAny(): bool
    {
        return Gate::allows('force_delete_lead_sources');
    }

    public static function canRestore($record): bool
    {
        return Gate::allows('restore_lead_sources');
    }

    public static function canRestoreAny(): bool
    {
        return Gate::allows('bulk_restore_lead_sources');
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
                    ->helperText('Enable or disable this lead source')
                    ->default(true),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->withoutGlobalScope(\App\Models\ActiveScope::class);

        if (Gate::allows('view_deleted_lead_sources')) {
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
            'index' => Pages\ListLeadSources::route('/'),
            'create' => Pages\CreateLeadSource::route('/create'),
            'edit' => Pages\EditLeadSource::route('/{record}/edit'),
        ];
    }
}