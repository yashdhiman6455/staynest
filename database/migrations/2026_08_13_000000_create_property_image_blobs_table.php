<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Store property image binaries in the database so uploaded images
     * survive Render redeployments and are visible in every environment
     * that shares the database (local and production).
     */
    public function up(): void
    {
        Schema::create('property_image_blobs', function (Blueprint $table) {
            $table->string('path')->primary();
            $table->longText('data')->charset('binary');
            $table->string('mime')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_image_blobs');
    }
};
