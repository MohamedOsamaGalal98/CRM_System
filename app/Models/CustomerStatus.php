<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class CustomerStatus extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'customer_id',
        'status_id',
        'sales_id',
        'notes',
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
     * Check if customer status is truly active (not soft deleted and is_active = true)
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->is_active && !$this->trashed();
    }

    /**
     * Scope to get only active customer statuses
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->whereNull($query->getModel()->getTable() . '.deleted_at')->where($query->getModel()->getTable() . '.is_active', 1);
    }

    /**
     * Get the customer that owns the customer status.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * Get the status that owns the customer status.
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'status_id');
    }

    /**
     * Get the sales representative that owns the customer status.
     */
    public function sales(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sales_id');
    }

    /**
     * Get the user who deleted this customer status.
     */
    public function deletedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($model) {
            if (!$model->isForceDeleting()) {
                $model->is_active = false;
                $model->deleted_by = Auth::id();
                $model->save();
            }
        });
    }
}
