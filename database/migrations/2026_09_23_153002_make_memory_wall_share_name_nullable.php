<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('memory_wall_shares', function (Blueprint $table) {
            $table->string('name')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('memory_wall_shares')
            ->whereNull('name')
            ->update(['name' => '']);

        Schema::table('memory_wall_shares', function (Blueprint $table) {
            $table->string('name')->nullable(false)->change();
        });
    }
};
