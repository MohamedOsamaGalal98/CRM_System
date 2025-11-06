<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Filament\Notifications\Notification;
use App\Models\User;

class CustomField extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'is_active',
    ];

    /**
     * العلاقة مع المستخدم الذي قام بالحذف
     */
    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    protected static function boot()
    {
        parent::boot();

        // عند الحذف الناعم، اجعل السجل غير نشط تلقائياً
        static::deleting(function ($model) {
            if (!$model->isForceDeleting()) {
                $model->is_active = false;
                $model->save();
            }

            if (Auth::check()) {
                $model->deleted_by = Auth::id();
                $model->save();
            }
        });

        // عند الاستعادة، لا نجعله نشط تلقائياً (يحتاج موافقة يدوية)
        static::restored(function ($model) {
            // مسح الـ cache
            Cache::forget("custom_field_active_{$model->id}");

            // السجل يبقى غير نشط حتى يتم تفعيله يدوياً
            Notification::make()
                ->title('Custom Field Restored')
                ->body("Custom Field '{$model->name}' has been restored but remains inactive. Please activate manually if needed.")
                ->warning()
                ->send();
        });

        // مسح cache عند تحديث حالة الـ custom field
        static::updated(function ($model) {
            if ($model->isDirty('is_active')) {
                Cache::forget("custom_field_active_{$model->id}");
            }
        });
    }
}