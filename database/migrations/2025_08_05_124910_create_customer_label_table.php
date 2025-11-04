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
        Schema::create('customer_label', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('label_id');
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            
            $table->foreign('customer_id')->references('id')->on('customers');
            $table->foreign('label_id')->references('id')->on('labels');
            $table->foreign('deleted_by')->references('id')->on('users');
            
            $table->unique(['customer_id', 'label_id'], 'customer_label_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_label');
    }
};
