<?php

namespace App\Filament\Resources\CustomerResource\Pages;

use App\Filament\Resources\CustomerResource;
use App\Models\Customer;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms;
use Illuminate\Support\Facades\Gate;

class ListCustomers extends ListRecords
{
    protected static string $resource = CustomerResource::class;

    public ?string $activeTab = 'active';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        $tabs = [
            'active' => Tab::make('Active Customers')
                ->icon('heroicon-o-check-circle')
                ->badge(Customer::whereNull('deleted_at')->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNull('deleted_at')),
        ];

        if (Gate::allows('view_deleted_customers')) {
            $tabs['deleted'] = Tab::make('Deleted Customers')
                ->icon('heroicon-o-trash')
                ->badge(Customer::onlyTrashed()->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->onlyTrashed());
        }

        return $tabs;
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable(),
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Customer')
                    ->getStateUsing(fn ($record) => trim($record->first_name . ' ' . $record->last_name)),
                Tables\Columns\TextColumn::make('sales.name')
                    ->label('Sales')
                    ->visible(fn () => $this->activeTab !== 'deleted'),
                Tables\Columns\TextColumn::make('phone'),
                Tables\Columns\TextColumn::make('email'),
                Tables\Columns\TextColumn::make('rejection_status')
                    ->label('Rejection')
                    ->visible(fn () => $this->activeTab !== 'deleted'),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->label('Deleted At')
                    ->dateTime()
                    ->sortable()
                    ->visible(fn () => $this->activeTab === 'deleted'),
                Tables\Columns\TextColumn::make('deletedBy.name')
                    ->label('Deleted By')
                    ->sortable()
                    ->visible(fn () => $this->activeTab === 'deleted'),
                Tables\Columns\TextColumn::make('created_at')
                    ->date(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->since(),
            ])
            ->filters([
                Tables\Filters\Filter::make('has_sales')
                    ->label('With Sales')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('sales_id'))
                    ->visible(fn () => $this->activeTab !== 'deleted'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn () => $this->activeTab !== 'deleted'),
                
                Tables\Actions\RestoreAction::make()
                    ->visible(fn () => $this->activeTab === 'deleted'),
                
                Tables\Actions\ForceDeleteAction::make()
                    ->visible(fn () => $this->activeTab === 'deleted'),
                
                Tables\Actions\DeleteAction::make()
                    ->visible(fn () => $this->activeTab !== 'deleted'),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('Change Sales')
                    ->action(function ($records, array $data) {
                        foreach ($records as $record) {
                            $record->update(['sales_id' => $data['sales_id']]);
                        }
                    })
                    ->form([
                        Forms\Components\Select::make('sales_id')
                            ->relationship('sales', 'name')
                            ->required(),
                    ])
                    ->visible(fn () => $this->activeTab !== 'deleted'),
                
                Tables\Actions\RestoreBulkAction::make()
                    ->visible(fn () => $this->activeTab === 'deleted'),
                
                Tables\Actions\ForceDeleteBulkAction::make()
                    ->visible(fn () => $this->activeTab === 'deleted'),
                
                Tables\Actions\DeleteBulkAction::make()
                    ->visible(fn () => $this->activeTab !== 'deleted'),
            ]);
    }
}
