<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // إضافة is_active و soft deletes لجدول users
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->foreign('deleted_by')->references('id')->on('users');
        });
        
        // إضافة is_active و soft deletes لجدول roles
        Schema::table('roles', function (Blueprint $table) {
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->foreign('deleted_by')->references('id')->on('users');
        });
        
        // إضافة is_active و soft deletes لجدول permissions
        Schema::table('permissions', function (Blueprint $table) {
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->foreign('deleted_by')->references('id')->on('users');
        });
        
        // إضافة soft deletes لجدول model_has_permissions
        Schema::table('model_has_permissions', function (Blueprint $table) {
            $table->softDeletes();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->foreign('deleted_by')->references('id')->on('users');
        });
        
        // إضافة soft deletes لجدول model_has_roles
        Schema::table('model_has_roles', function (Blueprint $table) {
            $table->softDeletes();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->foreign('deleted_by')->references('id')->on('users');
        });
        
        // إضافة soft deletes لجدول role_has_permissions
        Schema::table('role_has_permissions', function (Blueprint $table) {
            $table->softDeletes();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->foreign('deleted_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // إزالة الحقول من جدول users
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['deleted_by']);
            $table->dropColumn(['is_active', 'deleted_at', 'deleted_by']);
        });
        
        // إزالة الحقول من جدول roles
        Schema::table('roles', function (Blueprint $table) {
            $table->dropForeign(['deleted_by']);
            $table->dropColumn(['is_active', 'deleted_at', 'deleted_by']);
        });
        
        // إزالة الحقول من جدول permissions
        Schema::table('permissions', function (Blueprint $table) {
            $table->dropForeign(['deleted_by']);
            $table->dropColumn(['is_active', 'deleted_at', 'deleted_by']);
        });
        
        // إزالة الحقول من model_has_permissions
        Schema::table('model_has_permissions', function (Blueprint $table) {
            $table->dropForeign(['deleted_by']);
            $table->dropColumn(['deleted_at', 'deleted_by']);
        });
        
        // إزالة الحقول من model_has_roles
        Schema::table('model_has_roles', function (Blueprint $table) {
            $table->dropForeign(['deleted_by']);
            $table->dropColumn(['deleted_at', 'deleted_by']);
        });
        
        // إزالة الحقول من role_has_permissions
        Schema::table('role_has_permissions', function (Blueprint $table) {
            $table->dropForeign(['deleted_by']);
            $table->dropColumn(['deleted_at', 'deleted_by']);
        });
    }
};
