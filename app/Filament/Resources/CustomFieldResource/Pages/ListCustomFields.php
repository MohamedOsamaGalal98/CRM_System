<?php

namespace App\Filament\Resources\CustomFieldResource\Pages;

use App\Filament\Resources\CustomFieldResource;
use App\Models\CustomField;
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
use App\Filament\Resources\CustomFieldResource\Widgets\CustomFieldStatsWidget;

class ListCustomFields extends ListRecords
{
    protected static string $resource = CustomFieldResource::class;

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
            CustomFieldStatsWidget::class,
        ];
    }

    public function getTabs(): array
    {
        $tabs = [
            'active' => Tab::make('Active Custom Fields')
                ->icon('heroicon-o-check-circle')
                ->badge(CustomField::whereNull('deleted_at')->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNull('deleted_at')),
        ];

        if (Gate::allows('view_deleted_custom_fields')) {
        $tabs['deleted'] = Tab::make('Deleted Custom Fields')
                ->icon('heroicon-o-trash')
                ->badge(CustomField::onlyTrashed()->count())
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
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn () => $this->activeTab !== 'deleted'),
                
                Tables\Actions\RestoreAction::make()
                    ->visible(fn () => $this->activeTab === 'deleted'),
                
                Tables\Actions\ForceDeleteAction::make()
                    ->visible(fn () => $this->activeTab === 'deleted'),
                
                 Tables\Actions\DeleteAction::make()
                    ->visible(function () {
                        return Gate::allows('delete_custom_fields') && $this->activeTab !== 'deleted';
                    })
                    ->action(function ($data, $record) {
                        Notification::make()
                            ->success()
                            ->title('Custom Field deleted')
                            ->body('Custom Field has been deleted.')
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
                        ->visible(fn () => $this->activeTab !== 'deleted'),
                ]),
            ]);
    }

       public function getEloquentQuery(): Builder
    {
        $query = CustomField::query()
            ->withoutGlobalScope(\App\Models\ActiveScope::class);
            
        if (Gate::allows('view_deleted_custom_fields')) {
            $query->withoutGlobalScope(\Illuminate\Database\Eloquent\SoftDeletingScope::class);
        }
        
        return $query;
    }
}