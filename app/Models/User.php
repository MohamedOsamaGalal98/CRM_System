<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use App\Models\ActiveScope;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'email_verified_at',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    /**
     * Check if user can login (active and not deleted)
     */
    public function canLogin(): bool
    {
        return $this->is_active && !$this->trashed();
    }

    /**
     * Check if user is available for selection (active and not deleted)
     */
    public function isAvailable(): bool
    {
        return $this->is_active && !$this->trashed();
    }

    /**
     * Get only active and non-deleted roles for this user
     */
    public function activeRoles()
    {
        return $this->roles()->whereNull('roles.deleted_at')->where('roles.is_active', 1);
    }

    /**
     * Get only active and non-deleted permissions for this user
     */
    public function activePermissions()
    {
        return $this->permissions()->whereNull('permissions.deleted_at')->where('permissions.is_active', 1);
    }

    /**
     * Scope to get only available users (active and not deleted)
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
        
        // عند الاستعادة، لا نجعله نشط تلقائياً (يحتاج موافقة يدوية)
        static::restored(function ($model) {
            // مسح الـ cache
            \Illuminate\Support\Facades\Cache::forget("user_active_{$model->id}");
            
            // السجل يبقى غير نشط حتى يتم تفعيله يدوياً
            \Filament\Notifications\Notification::make()
                ->title('User Restored')
                ->body("User '{$model->name}' has been restored but remains inactive. Please activate manually if needed.")
                ->warning()
                ->send();
        });
        
        // مسح cache عند تحديث حالة المستخدم
        static::updated(function ($model) {
            if ($model->isDirty('is_active')) {
                \Illuminate\Support\Facades\Cache::forget("user_active_{$model->id}");
            }
        });
    }
    
    /**
     * العلاقة مع المستخدم الذي قام بالحذف
     */
    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}
