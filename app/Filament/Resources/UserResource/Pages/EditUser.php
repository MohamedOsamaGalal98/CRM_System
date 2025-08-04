<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Cache;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        // مسح cache المستخدم عند تحديث بياناته
        Cache::forget("user_active_{$this->record->id}");
        
        // إشعار بالنجاح
        \Filament\Notifications\Notification::make()
            ->title('User Updated Successfully')
            ->body("User '{$this->record->name}' has been updated")
            ->success()
            ->send();
    }
}
