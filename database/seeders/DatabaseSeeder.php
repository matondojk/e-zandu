<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. DESATIVA A VERIFICAÇÃO DE CHAVES ESTRANGEIRAS
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // 2. TRUNCATE GLOBAL (Ordem CORRIGIDA: Dependentes primeiro, Pais depois)
        
        // --- LIMPEZA DE PEDIDOS/CLIENTES (MÁXIMA PRIORIDADE) ---
        DB::table('cart_items')->truncate();
        DB::table('carts')->truncate();
        DB::table('stock_alerts')->truncate(); 
        DB::table('users')->truncate(); // ✅ AGORA LIMPO ANTES DE SER REFERENCIADO
        
        // --- LIMPEZA DE ESTOQUE/COMPRAS/PRODUTOS ---
        DB::table('stock_movements')->truncate();
        DB::table('purchases')->truncate(); 
        DB::table('product_cross_sell')->truncate();
        DB::table('category_product')->truncate();

        // --- LIMPEZA DE BASES ---
        DB::table('products')->truncate();
        DB::table('categories')->truncate();
        DB::table('suppliers')->truncate(); 
        DB::table('stocks')->truncate();
        DB::table('users')->truncate();
        DB::table('settings')->truncate();

        // 3. CHAMA OS SEEDERS (APENAS CRIAÇÃO DE DADOS)
        $this->call([
            // 1. Configurações e Utilizadores de Sistema
            SettingSeeder::class,
            CustomPermissionsSeeder::class,
            // UserSeeder::class,
            CouponSeeder::class,

            // 2. Base do ERP (Fornecedores e Armazéns)
            SupplierSeeder::class,
            StockSeeder::class,
            
            // 3. Catálogo (Categorias e Produtos)
            ProductSeeder::class, 
            ShippingRateSeeder::class, 

            // 4. Clientes e Fluxo de Estoque
            CustomerSeeder::class,
            PaymentMethodSeeder::class,
            PurchaseSeeder::class, 
            PurchaseItemSeeder::class, 
            StockMovementSeeder::class,
            ExpenseSeeder::class,            
            // 5. Vendas/Pedidos (Cria carrinhos ativos e finalizados)
            CartSeeder::class, 
            ChangelogSeeder::class,
        ]);
        
        // 4. REATIVA A VERIFICAÇÃO DE CHAVES ESTRANGEIRAS
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
