<?php

declare(strict_types=1);

use App\Enums\UserType;
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
        Schema::table('users', function (Blueprint $table): void {

            $table->after('remember_token', function (Blueprint $table): void {
                $table->string('user_type')->default(UserType::WeddingUser);
                $table->foreignId('team_id')->nullable()->constrained()->nullOnDelete();
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropForeign(['team_id']);
            $table->dropColumn(['team_id', 'user_type']);
        });
    }
};
