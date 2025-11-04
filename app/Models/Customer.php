<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Auth;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name', 
        'email',
        'phone',
        'social_url',
        'description',
        'profile_image',
        'employee_id',
        'rejection_status',
        'sales_id',
        'lead_source_id',
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
     * Check if customer is truly active (not soft deleted and is_active = true)
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->is_active && !$this->trashed();
    }

    /**
     * Scope to get only active customers
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->whereNull($query->getModel()->getTable() . '.deleted_at')->where($query->getModel()->getTable() . '.is_active', 1);
    }

    /**
     * Get the sales representative that owns the customer.
     */
    public function sales(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sales_id');
    }

    /**
     * Get the employee assigned to the customer.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    /**
     * Get the lead source that the customer came from.
     * Note: LeadSource model needs to be created when needed
     */
    // public function leadSource(): BelongsTo
    // {
    //     return $this->belongsTo(LeadSource::class, 'lead_source_id');
    // }

    /**
     * Get the user who deleted this customer.
     */
    public function deletedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Get the customer statuses for the customer.
     */
    public function customerStatuses(): HasMany
    {
        return $this->hasMany(CustomerStatus::class, 'customer_id')
                    ->where('is_active', 1)
                    ->whereNull('deleted_at');
    }

    /**
     * Get the current active status for the customer.
     */
    public function currentStatus(): HasOne
    {
        return $this->hasOne(CustomerStatus::class, 'customer_id')
                    ->where('is_active', 1)
                    ->whereNull('deleted_at')
                    ->latest();
    }

    /**
     * Get the labels for the customer.
     */
    public function labels(): BelongsToMany
    {
        return $this->belongsToMany(Label::class, 'customer_labels')
                    ->withTimestamps();
    }

    /**
     * Get the custom field values for the customer.
     */
    public function customFieldValues(): HasMany
    {
        return $this->hasMany(CustomFieldValue::class, 'customer_id')
                    ->where('is_active', 1)
                    ->whereNull('deleted_at');
    }

    /**
     * Get a specific custom field value by field name or ID.
     *
     * @param string|int $fieldIdentifier
     * @return mixed
     */
    public function getCustomFieldValue($fieldIdentifier)
    {
        if (is_numeric($fieldIdentifier)) {
            // Search by custom_field_id
            $customFieldValue = $this->customFieldValues()
                ->where('custom_field_id', $fieldIdentifier)
                ->first();
        } else {
            // Search by custom field name
            $customFieldValue = $this->customFieldValues()
                ->whereHas('customField', function ($query) use ($fieldIdentifier) {
                    $query->where('name', $fieldIdentifier);
                })
                ->first();
        }

        return $customFieldValue ? $customFieldValue->formatted_value : null;
    }

    /**
     * Get the full name of the customer.
     */
    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
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
