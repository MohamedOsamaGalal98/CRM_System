<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class CustomFieldValue extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'customer_id',
        'custom_field_id',
        'value',
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
     * Check if custom field value is truly active (not soft deleted and is_active = true)
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->is_active && !$this->trashed();
    }

    /**
     * Scope to get only active custom field values
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->whereNull($query->getModel()->getTable() . '.deleted_at')->where($query->getModel()->getTable() . '.is_active', 1);
    }

    /**
     * Get the customer that owns the custom field value.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * Get the custom field that owns the custom field value.
     */
    public function customField(): BelongsTo
    {
        return $this->belongsTo(CustomField::class, 'custom_field_id');
    }

    /**
     * Get the user who deleted this custom field value.
     */
    public function deletedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Get the formatted value based on the custom field type.
     *
     * @return mixed
     */
    public function getFormattedValueAttribute()
    {
        $field = $this->customField;
        
        if (!$field) {
            return $this->value;
        }

        switch ($field->type) {
            case 'date':
                return $this->value ? \Carbon\Carbon::parse($this->value)->format('Y-m-d') : null;
            case 'number':
                return is_numeric($this->value) ? (float) $this->value : null;
            case 'email':
                return filter_var($this->value, FILTER_VALIDATE_EMAIL) ? $this->value : null;
            default:
                return $this->value;
        }
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
