<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\UserType;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:create-management-user
    {--name= : The management user name}
    {--email= : The management user email address}
    {--password= : The management user password}')]
#[Description('Create a user that can access the management Filament panel.')]

/**
 * Creates the first user for the management panel.
 */
class CreateManagementUser extends Command
{
    /**
     * Execute the command.
     */
    public function handle(): int
    {
        $name = $this->option('name') ?? $this->ask('Name');
        $email = $this->option('email') ?? $this->ask('Email');
        $password = $this->option('password') ?? $this->secret('Password');

        if (! is_string($name) || blank($name)) {
            $this->error('The name is required.');

            return self::FAILURE;
        }

        if (! is_string($email) || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('A valid email address is required.');

            return self::FAILURE;
        }

        if (! is_string($password) || mb_strlen($password) < 8) {
            $this->error('The password must be at least 8 characters long.');

            return self::FAILURE;
        }

        if (User::where('email', $email)->exists()) {
            $this->error('A user with this email address already exists.');

            return self::FAILURE;
        }

        User::create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'user_type' => UserType::ManagementAdmin,
        ]);

        $this->info("Management user [{$email}] created successfully.");

        return self::SUCCESS;
    }
}
