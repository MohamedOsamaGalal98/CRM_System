<?php

namespace App\Filament\Resources;

use App\Models\Customer;
use Filament\Resources\Resource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Resources\CustomerResource\Pages;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'CRM';

    public static function canViewAny(): bool
    {
        return \Illuminate\Support\Facades\Gate::allows('view_any_customers');
    }

    public static function canView($record): bool
    {
        return \Illuminate\Support\Facades\Gate::allows('view_customers');
    }

    public static function canCreate(): bool
    {
        return \Illuminate\Support\Facades\Gate::allows('create_customers');
    }

    public static function canEdit($record): bool
    {
        return \Illuminate\Support\Facades\Gate::allows('update_customers');
    }

    public static function canDelete($record): bool
    {
        return \Illuminate\Support\Facades\Gate::allows('delete_customers');
    }

    public static function canDeleteAny(): bool
    {
        return \Illuminate\Support\Facades\Gate::allows('bulk_delete_customers');
    }

    public static function canForceDelete($record): bool
    {
        return \Illuminate\Support\Facades\Gate::allows('force_delete_customers');
    }

    public static function canForceDeleteAny(): bool
    {
        return \Illuminate\Support\Facades\Gate::allows('force_delete_customers');
    }

    public static function canRestore($record): bool
    {
        return \Illuminate\Support\Facades\Gate::allows('restore_customers');
    }

    public static function canRestoreAny(): bool
    {
        return \Illuminate\Support\Facades\Gate::allows('bulk_restore_customers');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Sales Info')
                ->schema([
                    TextInput::make('sales.name')
                        ->label('Sales Name')
                        ->disabled()
                        ->dehydrated(false),
                    Select::make('sales_id')
                        ->label('Sales')
                        ->relationship('sales', 'name')
                        ->required(),
                ])->columns(2),

            Forms\Components\Section::make('Customer Details')
                ->schema([
                    Forms\Components\TextInput::make('first_name'),
                    Forms\Components\TextInput::make('last_name'),
                    Forms\Components\TextInput::make('email')->email(),
                    Forms\Components\TextInput::make('phone'),
                    Forms\Components\TextInput::make('social_url'),
                    Forms\Components\Textarea::make('description'),
                ])->columns(2),

            Forms\Components\Section::make('Lead Details')
                ->schema([
                    Forms\Components\Select::make('lead_source_id')
                        ->label('Lead Source')
                        ->options([])
                        ->nullable(),
                    Forms\Components\Select::make('labels')
                        ->relationship('labels', 'name')
                        ->multiple()
                        ->nullable(),
                ])->columns(2),

            Forms\Components\Section::make('Rejection Details')
                ->schema([
                    Forms\Components\Select::make('rejection_status')
                        ->options([
                            'price' => 'Price',
                            'contract' => 'Contract',
                            'trust' => 'Trust',
                            'unqualified' => 'Unqualified',
                            'other' => 'Other',
                        ])
                        ->nullable(),
                ])->columns(1),
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
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters(
                static::getTableFilters()
            )
            ->actions([
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
        return [];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
            
        if (\Illuminate\Support\Facades\Gate::allows('view_deleted_customers')) {
            $query->withoutGlobalScope(SoftDeletingScope::class);
        }
        
        return $query;
    }

    protected static function getTableFilters(): array
    {
        $filters = [];
        
        if (\Illuminate\Support\Facades\Gate::allows('view_deleted_customers')) {
            $filters[] = Tables\Filters\TrashedFilter::make();
        }
        
        return $filters;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }
}
