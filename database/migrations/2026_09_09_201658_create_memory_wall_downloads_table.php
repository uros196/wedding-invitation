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
        Schema::create('memory_wall_downloads', function (Blueprint $table) {
            $table->id();

            // Scope every archive request to the wedding and requesting user.
            $table->foreignId('wedding_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->uuid()->unique();

            // Keep the storage location and public archive metadata with the request.
            $table->string('disk');
            $table->string('archive_path')->nullable();
            // Persist the active multipart upload so a cancelled or abandoned job can be cleaned up safely.
            $table->string('multipart_upload_id')->nullable();
            $table->string('archive_name');
            $table->string('status', 32)->index();

            // These counters drive the progress panel while the queue builds the ZIP.
            $table->unsignedBigInteger('total_bytes')->default(0);
            $table->unsignedBigInteger('processed_bytes')->default(0);
            $table->unsignedInteger('total_files')->default(0);
            $table->unsignedInteger('processed_files')->default(0);

            // Timestamps describe preparation, retention, and terminal failures.
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('expires_at')->nullable();

            // Keep cancellation request and completion timestamps separate for lifecycle and cleanup decisions.
            $table->timestamp('cancellation_requested_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['wedding_id', 'status', 'created_at']);
            $table->index(['status', 'expires_at']);
            $table->index(['status', 'cancellation_requested_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('memory_wall_downloads');
    }
};
