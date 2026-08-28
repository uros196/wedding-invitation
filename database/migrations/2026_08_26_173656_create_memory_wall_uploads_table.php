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
        Schema::create('memory_wall_uploads', function (Blueprint $table) {
            $table->id();

            // Every session belongs to one wedding and points at its stable media row.
            $table->foreignId('wedding_id')->constrained()->cascadeOnDelete();
            $table->foreignId('media_id')->nullable()->constrained('media')->nullOnDelete();

            // The client ID makes retries idempotent; the token hash protects later session calls.
            $table->uuid()->unique();
            $table->uuid('client_upload_id');
            $table->char('upload_token_hash', 64);

            // These fields identify the remote multipart object and the expected part layout.
            $table->string('multipart_upload_id')->nullable();
            $table->string('object_path');

            // Client metadata is stored for display and is checked again against S3 on completion.
            $table->string('original_name');
            $table->string('extension', 16);
            $table->string('mime_type');
            $table->unsignedBigInteger('expected_size');
            $table->unsignedInteger('part_size');
            $table->unsignedInteger('total_parts');

            // Incomplete sessions remain hidden from the public gallery.
            $table->string('status', 32)->index();
            $table->text('error_message')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->unique(['wedding_id', 'client_upload_id']);
            $table->index(['wedding_id', 'status']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('memory_wall_uploads');
    }
};
