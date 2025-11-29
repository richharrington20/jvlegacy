<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\Person;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateAdminAccount extends Command
{
    protected $signature = 'account:create-admin {email} {password} {--name=Admin}';
    protected $description = 'Create a new admin account with investor login access';

    public function handle()
    {
        $email = $this->argument('email');
        $password = $this->argument('password');
        $name = $this->option('name');

        // Check if account already exists
        if (Account::where('email', $email)->exists()) {
            $this->error("Account with email {$email} already exists!");
            return 1;
        }

        // Split name into first and last
        $nameParts = explode(' ', $name, 2);
        $firstName = $nameParts[0];
        $lastName = $nameParts[1] ?? '';

        // Create person record
        $person = Person::create([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
        ]);

        // Create account with GUARDIAN type (type_id = 2) for global admin access
        $account = Account::create([
            'email' => $email,
            'password' => bcrypt($password),
            'type_id' => 2, // GUARDIAN = System Admin
            'person_id' => $person->id,
            'deleted' => 0,
        ]);

        $this->info("Admin account created successfully!");
        $this->line("Email: {$email}");
        $this->line("Password: {$password}");
        $this->line("Type: GUARDIAN (Global Admin)");
        $this->line("Account ID: {$account->id}");
        $this->line("");
        $this->info("You can now login at: https://sys.jaevee.co.uk/investor/login");

        return 0;
    }
}

