<?php

use App\Enums\TeamType;
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
        Schema::table('teams', function (Blueprint $table) {
            $table->string('type')->default(TeamType::Wedding->value)->after('has_memory_wall');

            // Apply changes to 'users' table by updating user_type to 'team_member'
            DB::table('users')
                ->where('user_type', 'wedding_user')
                ->update(['user_type' => 'team_member']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->dropColumn('type');

            DB::table('users')
                ->where('user_type', 'team_member')
                ->update(['user_type' => 'wedding_user']);
        });
    }
};
