<?php

namespace App\Filament\Resources\LabelResource\Pages;

use App\Filament\Resources\LabelResource;
use App\Filament\Resources\LabelResource\Widgets\LabelStatsWidget;
use App\Models\Label;
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

class ListLabels extends ListRecords
{
    protected static string $resource = LabelResource::class;

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
            LabelStatsWidget::class,
        ];
    }

    public function getTabs(): array
    {
        $tabs = [
            'active' => Tab::make('Active Labels')
                ->icon('heroicon-o-check-circle')
                ->badge(Label::whereNull('deleted_at')->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNull('deleted_at')),
        ];

        if (Gate::allows('view_deleted_labels')) {
        $tabs['deleted'] = Tab::make('Deleted Labels')
                ->icon('heroicon-o-trash')
                ->badge(Label::onlyTrashed()->count())
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
                
                Tables\Columns\ColorColumn::make('color')
                    ->label('Color')
                    ->default('#000000'),
                
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active')
                    ->sortable()
                    ->visible(fn () => $this->activeTab !== 'deleted'),
                
                Tables\Columns\TextColumn::make('customers_count')
                    ->label('Customers')
                    ->counts('customers')
                    ->sortable()
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
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('has_customers')
                    ->label('With Customers Only')
                    ->query(fn (Builder $query): Builder => $query->has('customers'))
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
                    ->visible(function () {
                        return Gate::allows('delete_permissions') && $this->activeTab !== 'deleted';
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
                        ->before(function ($records) {
                            $labelIds = $records->pluck('id');
                            $customerCount = DB::table('customer_labels')
                                ->whereIn('label_id', $labelIds)
                                ->count();
                            
                            if ($customerCount > 0) {
                                Notification::make()
                                    ->title('Cannot delete labels')
                                    ->body("Some of these labels are assigned to customers. Please remove these assignments first.")
                                    ->warning()
                                    ->send();
                                
                                return false;
                            }
                        })
                        ->visible(fn () => $this->activeTab !== 'deleted'),
                ]),
            ]);
    }

       public function getEloquentQuery(): Builder
    {
        $query = Label::query()
            ->with(['roles', 'users'])
            ->withoutGlobalScope(\App\Models\ActiveScope::class);
            
        if (Gate::allows('view_deleted_labels')) {
            $query->withoutGlobalScope(\Illuminate\Database\Eloquent\SoftDeletingScope::class);
        }
        
        return $query;
    }
}
