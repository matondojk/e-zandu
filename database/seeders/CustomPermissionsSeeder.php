<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Str;

class CustomPermissionsSeeder extends Seeder
{
    public function run()
    {
        // 1. Definir Perfis
        $roles = [
            'Super Admin', 'Marketing', 'Vendedor', 'Financeiro', 'Estoque', 'Gerente'
        ];

        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName], ['guard_name' => 'web']);
        }
        
        // 2. Definir Mapeamento de Permissões (Group Name, Base Route/Slug)
        $routeMap = [
            // ✅ NOVO GRUPO DE ADMINISTRAÇÃO AVANÇADA
            'Administração' => [
                'admin/administradores' => ['view', 'create', 'edit', 'delete'], // Usuários
                'admin/perfis' => ['view', 'create', 'edit', 'delete'], // Perfis (Roles)
                'admin/permissoes' => ['view', 'create', 'edit', 'delete'], // Permissões
            ],
            // Rotas de IA e Análise
            'Análise IA' => [
                'admin/analise-inteligente' => ['view', 'manage'],
                'admin/analytics-dashboard' => ['view'],
                'admin/assistente-de-insights' => ['use'],
                'admin/chat-de-analise' => ['use'],
                'admin/analise-de-comportamento' => ['view'],
                'admin/relatorios-guardados' => ['view', 'delete'],
            ],
            // Vendas e Marketing
            'Vendas' => [
                'admin/vendas' => ['view', 'edit', 'delete'], // CRUD de pedidos
                'admin/coupons' => ['view', 'create', 'edit', 'delete'],
                'admin/campanhas' => ['view', 'create', 'edit', 'delete', 'send'],
                'admin/avaliacoes' => ['view', 'edit', 'delete'],
            ],
            // Clientes
            'Clientes' => [
                'admin/clientes' => ['view', 'create', 'edit', 'delete'],
                'admin/newsletters' => ['view', 'send'],
                // Removido 'admin/administradores' daqui para o novo grupo 'Administração'
                'admin/assinaturas' => ['view', 'edit'],
            ],
            // Produtos e Estoque
            'Estoque' => [
                'admin/produtos' => ['view', 'create', 'edit', 'delete'],
                'admin/categorias' => ['view', 'create', 'edit', 'delete'],
                'admin/marcas' => ['view', 'create', 'edit', 'delete'],
                'admin/fornecedores' => ['view', 'create', 'edit', 'delete'],
                'admin/armazens' => ['view', 'create', 'edit', 'delete'],
                'admin/compras' => ['view', 'create', 'edit', 'delete'],
            ],
            // Finanças e Configurações
            'Finanças' => [
                'admin/despesas-operacionais' => ['view', 'create', 'edit', 'delete'],
                'admin/metodos-de-pagamento' => ['view', 'create', 'edit', 'delete'],
                'admin/taxas-de-frete' => ['view', 'create', 'edit', 'delete'],
            ],
            // Configurações Globais
            'Configurações' => [
                'admin/personalizacao' => ['edit'],
                'admin/changelogs' => ['view'],
            ],
        ];

        // 3. Criar Permissões (com slug = route/action)
        foreach ($routeMap as $groupName => $routes) {
            foreach ($routes as $routeSlug => $actions) {
                foreach ($actions as $action) {
                    // Garante que o nome seja único e o slug seja o padrão: admin/rota:acao
                    $permissionName = Str::title($action) . ' ' . Str::afterLast($routeSlug, '/');
                    $slug = "{$routeSlug}:{$action}";
                    
                    Permission::firstOrCreate(
                        ['slug' => $slug], 
                        [
                            'name' => $permissionName,
                            'group_name' => $groupName,
                            'guard_name' => 'web'
                        ]
                    );
                }
            }
        }

        // 4. Atribuir Permissões aos Perfis
        $allPermissions = Permission::all();
        $superAdmin = Role::where('name', 'Super Admin')->first();
        $marketing = Role::where('name', 'Marketing')->first();
        $vendedor = Role::where('name', 'Vendedor')->first();
        $financeiro = Role::where('name', 'Financeiro')->first();
        $estoque = Role::where('name', 'Estoque')->first();
        $gerente = Role::where('name', 'Gerente')->first();

        // Super Admin (Todos)
        $superAdmin->permissions()->sync($allPermissions->pluck('id'));
        $gerente->permissions()->sync($allPermissions->pluck('id')); // Gerente também tem acesso total

        // Marketing (Filtros existentes)
        $marketing->permissions()->sync($allPermissions->filter(fn($p) => 
            Str::contains($p->slug, ['admin/campanhas', 'admin/coupons', 'admin/analise-inteligente', 'admin/analise-de-comportamento'])
        )->pluck('id'));

        // Vendedor (Filtros existentes)
        $vendedor->permissions()->sync($allPermissions->filter(fn($p) => 
            Str::contains($p->slug, ['admin/vendas:view', 'admin/vendas:edit', 'admin/clientes', 'admin/avaliacoes', 'admin/coupons:view'])
        )->pluck('id'));

        // Financeiro (Filtros existentes)
        $financeiro->permissions()->sync($allPermissions->filter(fn($p) => 
            Str::contains($p->slug, ['admin/vendas:view', 'admin/despesas', 'admin/metodos-de-pagamento', 'admin/taxas-de-frete', 'admin/assinaturas'])
        )->pluck('id'));

        // Estoque (Filtros existentes)
        $estoque->permissions()->sync($allPermissions->filter(fn($p) => 
            Str::contains($p->slug, ['admin/produtos', 'admin/categorias', 'admin/marcas', 'admin/fornecedores', 'admin/armazens', 'admin/compras'])
        )->pluck('id'));
    }
}