<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class CreateCustomerUser extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'make:customer {--name=Customer} {--email=} {--phone=}';

    /**
     * The console command description.
     */
    protected $description = 'Cria um usuário do tipo customer para teste da integração com Mautic';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $name = $this->option('name') ?? 'Customer';
        $email = $this->option('email') ?? 'customer_' . Str::random(4) . '@example.com';
        $phone = $this->option('phone') ?? '+244900000000';

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'password' => Hash::make('password123'), // senha default
            'role' => 'customer',
        ]);

        $this->info("✅ Usuário customer criado com sucesso!");
        $this->info("ID: {$user->id}");
        $this->info("Nome: {$user->name}");
        $this->info("Email: {$user->email}");
    }
}
