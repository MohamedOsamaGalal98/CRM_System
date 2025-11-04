<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StatusResource\Pages;
use App\Filament\Resources\StatusResource\RelationManagers;
use App\Models\Status;
use App\Models\CustomerStatus;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;

class StatusResource extends Resource
{
    protected static ?string $model = Status::class;
    protected static ?string $title = "Status";
    protected static ?string $navigationLabel = 'Status';

    protected static ?string $navigationIcon = 'heroicon-o-flag';

    protected static ?string $navigationGroup = 'Settings';

    public static function canViewAny(): bool
    {
        return \Illuminate\Support\Facades\Gate::allows('view_any_statuses');
    }

    public static function canView($record): bool
    {
        return \Illuminate\Support\Facades\Gate::allows('view_statuses');
    }

    public static function canCreate(): bool
    {
        return \Illuminate\Support\Facades\Gate::allows('create_statuses');
    }

    public static function canEdit($record): bool
    {
        return \Illuminate\Support\Facades\Gate::allows('update_statuses');
    }

    public static function canDelete($record): bool
    {
        return \Illuminate\Support\Facades\Gate::allows('delete_statuses');
    }

    public static function canDeleteAny(): bool
    {
        return \Illuminate\Support\Facades\Gate::allows('bulk_delete_statuses');
    }

    public static function canForceDelete($record): bool
    {
        return \Illuminate\Support\Facades\Gate::allows('force_delete_statuses');
    }

    public static function canForceDeleteAny(): bool
    {
        return \Illuminate\Support\Facades\Gate::allows('force_delete_statuses');
    }

    public static function canRestore($record): bool
    {
        return \Illuminate\Support\Facades\Gate::allows('restore_statuses');
    }

    public static function canRestoreAny(): bool
    {
        return \Illuminate\Support\Facades\Gate::allows('bulk_restore_statuses');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                
                Forms\Components\TextInput::make('position')
                    ->numeric()
                    ->default(0)
                    ->label('Position (for ordering)'),
                
                Forms\Components\Toggle::make('is_default')
                    ->label('Set as Default Status')
                    ->helperText('Only one status can be default at a time')
                    ->afterStateUpdated(function ($state, $record) {
                        if ($state && $record) {
                            Status::where('id', '!=', $record->id)->update(['is_default' => false]);
                        }
                    }),
                
                Forms\Components\Toggle::make('is_active')
                    ->label('Active')
                    ->default(true)
                    ->helperText('Inactive status will be hidden from selection'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('position')
                    ->sortable()
                    ->label('Position'),
                Tables\Columns\IconColumn::make('is_default')
                    ->boolean()
                    ->label('Default'),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters(static::getTableFilters())
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
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
        $query = parent::getEloquentQuery();
        
        if (\Illuminate\Support\Facades\Gate::allows('view_deleted_statuses')) {
            $query->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
        }
        
        return $query;
    }

    protected static function getTableFilters(): array
    {
        $filters = [];
        
        if (Gate::allows('view_deleted_statuses')) {
            $filters[] = Tables\Filters\TrashedFilter::make();
        }
        
        return $filters;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStatuses::route('/'),
            'create' => Pages\CreateStatus::route('/create'),
            'edit' => Pages\EditStatus::route('/{record}/edit'),
        ];
    }
}
