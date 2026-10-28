<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

#[Signature('admin:promote {email : The email address of the user} {--revoke : Remove administrator access instead}')]
#[Description('Grant or revoke administrator access for a user')]
class PromoteAdmin extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = Str::lower((string) $this->argument('email'));
        $user = User::query()->where('email', $email)->first();

        if ($user === null) {
            $this->error("No user found with the email {$email}.");

            return self::FAILURE;
        }

        $isAdmin = ! $this->option('revoke');

        // is_admin is intentionally not mass assignable during registration.
        $user->forceFill(['is_admin' => $isAdmin])->save();

        $this->info($isAdmin
            ? "{$user->email} can now manage products."
            : "{$user->email} is no longer an administrator.");

        return self::SUCCESS;
    }
}
