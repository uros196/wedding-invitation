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
        Schema::table('memory_wall_uploads', function (Blueprint $table) {
            $table->index(['wedding_id', 'mime_type'], 'memory_wall_uploads_wedding_mime_type_index');
            $table->index(['wedding_id', 'expected_size'], 'memory_wall_uploads_wedding_expected_size_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('memory_wall_uploads', function (Blueprint $table) {
            $table->dropIndex('memory_wall_uploads_wedding_mime_type_index');
            $table->dropIndex('memory_wall_uploads_wedding_expected_size_index');
        });
    }
};
