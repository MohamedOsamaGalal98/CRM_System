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
        Schema::create('custom_field_values', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('custom_field_id');
            $table->text('value')->nullable();
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            
            $table->foreign('customer_id')->references('id')->on('customers');
            $table->foreign('custom_field_id')->references('id')->on('custom_fields');
            $table->foreign('deleted_by')->references('id')->on('users');
            
            $table->unique(['customer_id', 'custom_field_id'], 'customer_custom_field_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_field_values');
    }
};
