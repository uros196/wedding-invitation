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
        Schema::create('weddings', function (Blueprint $table) {
            $table->id();
            $table->string('bride_name')->nullable();
            $table->string('groom_name')->nullable();
            $table->date('wedding_date')->nullable();
            $table->datetime('rsvp_deadline')->nullable();
            $table->text('welcome_text')->nullable();
            $table->json('schedules')->nullable();
            $table->string('meta_title')->nullable()->after('welcome_text');
            $table->text('meta_description')->nullable()->after('meta_title');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weddings');
    }
};
