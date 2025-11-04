<?php

namespace App\Filament\Resources\StatusResource\Pages;

use App\Filament\Resources\StatusResource;
use App\Filament\Resources\StatusResource\Widgets\StatusStatsWidget;
use App\Models\Status;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\CustomerStatus;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Gate;

class ListStatuses extends ListRecords
{
    protected static string $resource = StatusResource::class;
    protected static ?string $title = 'Status';

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
            StatusStatsWidget::class,
        ];
    }

    public function getTabs(): array
    {
        $tabs = [
            'active' => Tab::make('Active Status')
                ->icon('heroicon-o-check-circle')
                ->badge(Status::whereNull('deleted_at')->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNull('deleted_at')),
        ];

        if (Gate::allows('view_deleted_statuses')) {
            $tabs['deleted'] = Tab::make('Deleted Status')
                ->icon('heroicon-o-trash')
                ->badge(Status::onlyTrashed()->count())
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
                
                Tables\Columns\IconColumn::make('is_default')
                    ->boolean()
                    ->label('Default')
                    ->sortable()
                    ->visible(fn () => $this->activeTab !== 'deleted'),
                
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active')
                    ->sortable()
                    ->visible(fn () => $this->activeTab !== 'deleted'),
                
                Tables\Columns\TextColumn::make('position')
                    ->sortable()
                    ->label('Position')
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
                Tables\Filters\Filter::make('default_only')
                    ->label('Default Status Only')
                    ->query(fn (Builder $query): Builder => $query->where('is_default', true))
                    ->visible(fn () => $this->activeTab !== 'deleted'),
                
                Tables\Filters\Filter::make('active_only')
                    ->label('Active Status Only')
                    ->query(fn (Builder $query): Builder => $query->where('is_active', true))
                    ->visible(fn () => $this->activeTab !== 'deleted'),
            ])
            ->actions([
                Tables\Actions\Action::make('set_default')
                    ->label('Set Default')
                    ->icon('heroicon-o-star')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(function (Status $record) {
                        Status::query()->update(['is_default' => false]);
                        $record->update(['is_default' => true]);
                        
                        Notification::make()
                            ->title('Default status updated')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Status $record): bool => (!$record->is_default) && ($this->activeTab !== 'deleted')),
                
                Tables\Actions\EditAction::make()
                    ->visible(fn () => $this->activeTab !== 'deleted'),
                
                Tables\Actions\RestoreAction::make()
                    ->visible(fn () => $this->activeTab === 'deleted'),
                
                Tables\Actions\ForceDeleteAction::make()
                    ->visible(fn () => $this->activeTab === 'deleted'),
                
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->before(function (Status $record) {
                        $customerStatusCount = CustomerStatus::where('status_id', $record->id)->count();
                        
                        if ($customerStatusCount > 0) {
                            Notification::make()
                                ->title('Cannot delete status')
                                ->body("This status is assigned to {$customerStatusCount} customer(s). Please reassign or remove these assignments first.")
                                ->danger()
                                ->send();
                            
                            return false;
                        }
                    })
                    ->visible(fn () => $this->activeTab !== 'deleted'),
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
                            $statusIds = $records->pluck('id');
                            $customerStatusCount = CustomerStatus::whereIn('status_id', $statusIds)->count();
                            
                            if ($customerStatusCount > 0) {
                                Notification::make()
                                    ->title('Cannot delete statuses')
                                    ->body("Some of these statuses are assigned to customers. Please reassign or remove these assignments first.")
                                    ->danger()
                                    ->send();
                                
                                return false;
                            }
                        })
                        ->visible(fn () => $this->activeTab !== 'deleted'),
                ]),
            ])
            ->reorderable('position')
            ->defaultSort('position');
    }
}
