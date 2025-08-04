<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Permission as SpatiePermission;
use App\Models\ActiveScope;

class Permission extends SpatiePermission
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'guard_name',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * العلاقة مع المستخدم الذي قام بالحذف
     */
    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Boot method لتسجيل المستخدم الذي قام بالحذف
     */
    /**
     * Check if permission is available for selection (active and not deleted)
     */
    public function isAvailable(): bool
    {
        return $this->is_active && !$this->trashed();
    }

    /**
     * Get only active and non-deleted roles for this permission
     */
    public function activeRoles()
    {
        return $this->roles()->whereNull('roles.deleted_at')->where('roles.is_active', 1);
    }

    /**
     * Get only active and non-deleted users for this permission
     */
    public function activeUsers()
    {
        return $this->users()->whereNull('users.deleted_at')->where('users.is_active', 1);
    }

    /**
     * Scope to get only available permissions (active and not deleted)
     */
    public function scopeAvailable($query)
    {
        return $query->whereNull($query->getModel()->getTable() . '.deleted_at')->where($query->getModel()->getTable() . '.is_active', 1);
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
            
            if (\Illuminate\Support\Facades\Auth::check()) {
                $model->deleted_by = \Illuminate\Support\Facades\Auth::id();
                $model->save();
            }
        });
        
        // عند الاستعادة، السجل يبقى غير نشط حتى يتم تفعيله يدوياً
        static::restored(function ($model) {
            \Filament\Notifications\Notification::make()
                ->title('Permission Restored')
                ->body("Permission '{$model->name}' has been restored but remains inactive. Please activate manually if needed.")
                ->warning()
                ->send();
        });
    }
}
