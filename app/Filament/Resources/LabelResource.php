<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LabelResource\Pages;
use App\Filament\Resources\LabelResource\RelationManagers;
use App\Models\Label;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Gate;

class LabelResource extends Resource
{
    protected static ?string $model = Label::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationGroup = 'Settings';

    public static function canViewAny(): bool
    {
        return Gate::allows('view_any_labels');
    }

    public static function canView($record): bool
    {
        return Gate::allows('view_labels');
    }

    public static function canCreate(): bool
    {
        return Gate::allows('create_labels');
    }

    public static function canEdit($record): bool
    {
        return Gate::allows('update_labels');
    }

    public static function canDelete($record): bool
    {
        return Gate::allows('delete_labels');
    }

    public static function canDeleteAny(): bool
    {
        return Gate::allows('bulk_delete_labels');
    }

    public static function canForceDelete($record): bool
    {
        return Gate::allows('force_delete_labels');
    }

    public static function canForceDeleteAny(): bool
    {
        return Gate::allows('force_delete_labels');
    }

    public static function canRestore($record): bool
    {
        return Gate::allows('restore_labels');
    }

    public static function canRestoreAny(): bool
    {
        return Gate::allows('bulk_restore_labels');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                
                Forms\Components\ColorPicker::make('color')
                    ->nullable()
                    ->helperText('Choose a color to represent this label'),
                
                Forms\Components\Toggle::make('is_active')
                    ->label('Active')
                    ->default(true)
                    ->helperText('Inactive labels will be hidden from selection'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\ColorColumn::make('color')
                    ->label('Color'),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters(array_merge(
                static::getTableFilters(),
                [
                    Tables\Filters\TernaryFilter::make('is_active')
                        ->label('Active Status')
                        ->placeholder('All labels')
                        ->trueLabel('Active labels only')
                        ->falseLabel('Inactive labels only'),
                ]
            ))
              ->filters([
                ...Gate::allows('view_deleted_labels') ? [Tables\Filters\TrashedFilter::make()] : [],
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status')
                    ->placeholder('All labels')
                    ->trueLabel('Active labels only')
                    ->falseLabel('Inactive labels only'),
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->withoutGlobalScope(\App\Models\ActiveScope::class);
            
        if (Gate::allows('view_deleted_labels')) {
            $query->withoutGlobalScope(SoftDeletingScope::class);
        }
        
        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLabels::route('/'),
            'create' => Pages\CreateLabel::route('/create'),
            'edit' => Pages\EditLabel::route('/{record}/edit'),
        ];
    }
}
