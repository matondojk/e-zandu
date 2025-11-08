<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Changelog;
use Illuminate\Support\Carbon;

class ChangelogSeeder extends Seeder
{
    public function run(): void
    {
        Changelog::truncate();
        $now = Carbon::now();

        Changelog::create([
            'version' => '1.2.1',
            'title' => 'Melhorias de Performance e Segurança',
            'status' => 'launched',
            'created_at' => $now,
            'features' => [
                ['description' => 'Otimização de consultas no Dashboard (Analytics)', 'plan_availability' => 'both'],
                ['description' => 'Correção de segurança no upload de imagens', 'plan_availability' => 'both'],
            ]
        ]);

        Changelog::create([
            'version' => '1.2.0',
            'title' => 'Integração de Marketing e WhatsApp (Plano PRO)',
            'status' => 'launched',
            'created_at' => $now->subDays(2),
            'features' => [
                ['description' => 'Novo Módulo: Campanhas de Marketing (IA)', 'plan_availability' => 'pro'],
                ['description' => 'Novo Módulo: Integração com a API do WhatsApp', 'plan_availability' => 'pro'],
                ['description' => 'Correções no fecho de caixa', 'plan_availability' => 'both'],
            ]
        ]);
        
        Changelog::create([
            'version' => '1.1.1',
            'title' => 'Ajustes no Módulo de IA',
            'status' => 'launched',
            'created_at' => $now->subWeek(),
            'features' => [
                ['description' => 'Melhoria na velocidade de resposta do "Chat de Análise"', 'plan_availability' => 'essencial'],
                ['description' => 'Novas sugestões de prompt no módulo "Análise Inteligente"', 'plan_availability' => 'essencial'],
            ]
        ]);

        Changelog::create([
            'version' => '1.1.0',
            'title' => 'Novos Módulos de Inteligência Artificial (Plano Essencial)',
            'status' => 'launched',
            'created_at' => $now->subWeeks(2),
            'features' => [
                ['description' => 'Novo Módulo: Análise Inteligente (BI)', 'plan_availability' => 'essencial'],
                ['description' => 'Novo Módulo: Chat de Análise com IA', 'plan_availability' => 'essencial'],
                ['description' => 'Novo Módulo: Assistente de Insights no Dashboard', 'plan_availability' => 'essencial'],
                ['description' => 'Melhoria na exportação de relatórios PDF/Excel', 'plan_availability' => 'both'],
            ]
        ]);
        
        Changelog::create([
            'version' => '1.0.1',
            'title' => 'Correções Iniciais e Ajustes de Stock',
            'status' => 'launched',
            'created_at' => $now->subWeeks(3),
            'features' => [
                ['description' => 'Ajuste no cálculo de stock negativo em Compras', 'plan_availability' => 'trial'],
                ['description' => 'Melhoria na impressão de talões de venda', 'plan_availability' => 'trial'],
            ]
        ]);

        Changelog::create([
            'version' => '1.0.0',
            'title' => 'Lançamento Oficial E-Zandu (Plano Trial/Core)',
            'status' => 'launched',
            'created_at' => $now->subMonth(),
            'features' => [
                ['description' => 'Gestão de Produtos e Categorias', 'plan_availability' => 'trial'],
                ['description' => 'Gestão de Clientes e Vendas (PDV)', 'plan_availability' => 'trial'],
                ['description' => 'Configurações da Loja e Métodos de Pagamento', 'plan_availability' => 'trial'],
                ['description' => 'Controlo de Despesas Operacionais', 'plan_availability' => 'trial'],
                ['description' => 'Gestão de Fornecedores e Compras', 'plan_availability' => 'trial'],
            ]
        ]);
    }
}