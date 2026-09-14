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
        Schema::create('memory_wall_download_items', function (Blueprint $table) {
            $table->id();

            // Keep the archive request and optional source upload relationships.
            $table->foreignId('memory_wall_download_id')->constrained()->cascadeOnDelete();
            $table->foreignId('memory_wall_upload_id')->nullable()->constrained()->nullOnDelete();

            // Snapshot the source object and its collision-safe ZIP entry name.
            $table->string('disk');
            $table->text('path');
            $table->string('original_name');
            $table->string('archive_name');
            $table->unsignedBigInteger('size');
            $table->string('mime_type')->nullable();
            $table->timestamps();

            // A source upload can appear only once in one archive snapshot.
            $table->unique(['memory_wall_download_id', 'memory_wall_upload_id'], 'memory_wall_unique');
            $table->index('memory_wall_download_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('memory_wall_download_items');
    }
};
