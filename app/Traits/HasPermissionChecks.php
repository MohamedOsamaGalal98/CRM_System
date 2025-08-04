<?php

namespace App\Traits;

use Illuminate\Support\Facades\Gate;

trait HasPermissionChecks
{
    /**
     * Check if user can view any records
     */
    public static function canViewAny(): bool
    {
        $model = static::getModel();
        $modelName = strtolower(class_basename($model));
        return Gate::allows("view_any_{$modelName}s");
    }

    /**
     * Check if user can view a specific record
     */
    public static function canView($record): bool
    {
        $model = static::getModel();
        $modelName = strtolower(class_basename($model));
        return Gate::allows("view_{$modelName}s");
    }

    /**
     * Check if user can create records
     */
    public static function canCreate(): bool
    {
        $model = static::getModel();
        $modelName = strtolower(class_basename($model));
        return Gate::allows("create_{$modelName}s");
    }

    /**
     * Check if user can edit a record
     */
    public static function canEdit($record): bool
    {
        $model = static::getModel();
        $modelName = strtolower(class_basename($model));
        return Gate::allows("update_{$modelName}s");
    }

    /**
     * Check if user can delete a record
     */
    public static function canDelete($record): bool
    {
        $model = static::getModel();
        $modelName = strtolower(class_basename($model));
        return Gate::allows("delete_{$modelName}s");
    }

    /**
     * Check if user can bulk delete records
     */
    public static function canDeleteAny(): bool
    {
        $model = static::getModel();
        $modelName = strtolower(class_basename($model));
        return Gate::allows("bulk_delete_{$modelName}s");
    }

    /**
     * Check if user can force delete a record
     */
    public static function canForceDelete($record): bool
    {
        $model = static::getModel();
        $modelName = strtolower(class_basename($model));
        return Gate::allows("force_delete_{$modelName}s");
    }

    /**
     * Check if user can bulk force delete records
     */
    public static function canForceDeleteAny(): bool
    {
        $model = static::getModel();
        $modelName = strtolower(class_basename($model));
        return Gate::allows("bulk_delete_{$modelName}s");
    }

    /**
     * Check if user can restore a record
     */
    public static function canRestore($record): bool
    {
        $model = static::getModel();
        $modelName = strtolower(class_basename($model));
        return Gate::allows("restore_{$modelName}s");
    }

    /**
     * Check if user can bulk restore records
     */
    public static function canRestoreAny(): bool
    {
        $model = static::getModel();
        $modelName = strtolower(class_basename($model));
        return Gate::allows("bulk_restore_{$modelName}s");
    }

    /**
     * Check if user can view deleted records
     */
    public static function canViewDeleted(): bool
    {
        $model = static::getModel();
        $modelName = strtolower(class_basename($model));
        return Gate::allows("view_deleted_{$modelName}s");
    }

    /**
     * Check if user can export records
     */
    public static function canExport(): bool
    {
        $model = static::getModel();
        $modelName = strtolower(class_basename($model));
        return Gate::allows("export_{$modelName}s");
    }

    /**
     * Check if user can import records
     */
    public static function canImport(): bool
    {
        $model = static::getModel();
        $modelName = strtolower(class_basename($model));
        return Gate::allows("import_{$modelName}s");
    }
}
