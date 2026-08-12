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
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->string('property_type');
            $table->string('location');
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->decimal('price_per_night', 10, 2);
            $table->unsignedInteger('guests');
            $table->unsignedInteger('bedrooms')->default(0);
            $table->unsignedInteger('bathrooms')->default(0);
            $table->string('image')->nullable();
            $table->enum('status', ['published', 'draft'])->default('published');
            $table->timestamps();

            $table->index(['status', 'property_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
