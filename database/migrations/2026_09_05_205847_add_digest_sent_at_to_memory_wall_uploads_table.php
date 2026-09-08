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
        Schema::table('memory_wall_uploads', function (Blueprint $table) {
            $table->timestamp('digest_sent_at')->nullable()->after('completed_at');
            $table->index(
                ['wedding_id', 'status', 'digest_sent_at', 'completed_at'],
                'memory_wall_uploads_digest_lookup_index',
            );
        });

        DB::table('memory_wall_uploads')
            ->where('status', 'completed')
            ->whereNotNull('completed_at')
            ->update(['digest_sent_at' => DB::raw('completed_at')]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('memory_wall_uploads', function (Blueprint $table) {
            $table->dropIndex('memory_wall_uploads_digest_lookup_index');
            $table->dropColumn('digest_sent_at');
        });
    }
};
