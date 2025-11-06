<?php

namespace App\Filament\Resources\LeadSourceResource\Pages;

use App\Filament\Resources\LeadSourceResource;
use App\Models\LeadSource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Gate;
use App\Filament\Resources\LeadSourceResource\Widgets\LeadSourceStatsWidget;

class ListLeadSources extends ListRecords
{
    protected static string $resource = LeadSourceResource::class;

    public ?string $activeTab = 'active';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            LeadSourceStatsWidget::class,
        ];
    }

    public function getTabs(): array
    {
        $tabs = [
            'active' => Tab::make('Active Lead Sources')
                ->icon('heroicon-o-check-circle')
                ->badge(LeadSource::whereNull('deleted_at')->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNull('deleted_at')),
        ];

        if (Gate::allows('view_deleted_lead_sources')) {
        $tabs['deleted'] = Tab::make('Deleted Lead Sources')
                ->icon('heroicon-o-trash')
                ->badge(LeadSource::onlyTrashed()->count())
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
                    ->sortable(),
                
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active')
                    ->sortable()
                    ->visible(fn () => $this->activeTab !== 'deleted'),
                
                // Tables\Columns\TextColumn::make('customers_count')
                //     ->label('Customers')
                //     ->counts('customers')
                //     ->sortable()
                //     ->visible(fn () => $this->activeTab !== 'deleted'),
                
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
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Tables\Filters\Filter::make('has_customers')
                //     ->label('With Customers Only')
                //     ->query(fn (Builder $query): Builder => $query->has('customers'))
                //     ->visible(fn () => $this->activeTab !== 'deleted'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn () => $this->activeTab !== 'deleted'),
                
                Tables\Actions\RestoreAction::make()
                    ->visible(fn () => $this->activeTab === 'deleted'),
                
                Tables\Actions\ForceDeleteAction::make()
                    ->visible(fn () => $this->activeTab === 'deleted'),
                
                 Tables\Actions\DeleteAction::make()
                    ->visible(function () {
                        return Gate::allows('delete_lead_sources') && $this->activeTab !== 'deleted';
                    })
                    ->action(function ($data, $record) {
                        // Note: Customer model is missing, so this will error if uncommented
                        // if ($record->customers()->count() > 0) {
                        //     Notification::make()
                        //         ->danger()
                        //         ->title('Lead Source is in use')
                        //         ->body('Lead Source is in use by customers.')
                        //         ->send();
                        //     return;
                        // }

                        Notification::make()
                            ->success()
                            ->title('Lead Source deleted')
                            ->body('Lead Source has been deleted.')
                            ->send();

                        $record->delete();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\RestoreBulkAction::make()
                        ->visible(fn () => $this->activeTab === 'deleted'),
                    
                    Tables\Actions\ForceDeleteBulkAction::make()
                        ->visible(fn () => $this->activeTab === 'deleted'),
                    
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation()
                        // ->before(function ($records) {
                        //     $leadSourceIds = $records->pluck('id');
                        //     $customerCount = DB::table('customer_lead_sources') // افترض جدول العلاقة
                        //         ->whereIn('lead_source_id', $leadSourceIds)
                        //         ->count();
                            
                        //     if ($customerCount > 0) {
                        //         Notification::make()
                        //             ->title('Cannot delete lead sources')
                        //             ->body("Some of these lead sources are assigned to customers. Please remove these assignments first.")
                        //             ->warning()
                        //             ->send();
                            
                        //             return false;
                        //     }
                        // })
                        ->visible(fn () => $this->activeTab !== 'deleted'),
                ]),
            ]);
    }

       public function getEloquentQuery(): Builder
    {
        $query = LeadSource::query()
            ->withoutGlobalScope(\App\Models\ActiveScope::class);
            
        if (Gate::allows('view_deleted_lead_sources')) {
            $query->withoutGlobalScope(\Illuminate\Database\Eloquent\SoftDeletingScope::class);
        }
        
        return $query;
    }
}