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
        Schema::create('custom_fields', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type')->default('text'); 
            $table->json('options')->nullable(); 
            $table->boolean('is_required')->default(false);
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            
            $table->foreign('deleted_by')->references('id')->on('users');
            
            $table->index(['type', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_fields');
    }
};
