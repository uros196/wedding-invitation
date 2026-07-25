<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Throwable;

#[Signature('app:send-test-mail
    {--email= : The email address to send the test mail to}')]
#[Description('Send a test email using the configured mailer')]
class SendTestMail extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = $this->option('email') ?? $this->ask('Email');

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Please provide a valid email address.');

            return self::INVALID;
        }

        try {
            Mail::raw(
                'This is a test email sent from the wedding invitation application.',
                function ($message) use ($email): void {
                    $message
                        ->to($email)
                        ->subject('Wedding invitation test email');
                },
            );
        } catch (Throwable $exception) {
            report($exception);
            $this->error('The test email could not be sent. Check the mail configuration and application logs.');

            return self::FAILURE;
        }

        $this->info("Test email sent to {$email}.");

        return self::SUCCESS;
    }
}
