<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Expense;
use Illuminate\Support\Carbon;

class ExpenseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Expense::truncate();

        Expense::create([
            'description' => 'Renda do Escritório - Mês Corrente',
            'amount' => 150000.00,
            'expense_date' => Carbon::now()->startOfMonth(),
            'notes' => 'Pagamento da renda mensal do escritório central.'
        ]);

        Expense::create([
            'description' => 'Anúncios Facebook Ads - Lançamento',
            'amount' => 50000.00,
            'expense_date' => Carbon::now()->subDays(5),
            'notes' => 'Impulso inicial para a campanha de lançamento E-Zandu.'
        ]);
        
        Expense::create([
            'description' => 'Energia (Sequele)',
            'amount' => 25000.00,
            'expense_date' => Carbon::now()->subDays(2),
        ]);
    }
}