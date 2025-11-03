<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;
use App\Models\ProductReview; // ✅ NOVO: Importar o Model de Review
use App\Models\User;         // ✅ NOVO: Importar o Model de User
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // --- LIMPEZA DE TABELAS ---
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('product_reviews')->truncate(); // ✅ NOVO: Limpar reviews
        DB::table('product_cross_sell')->truncate();
        DB::table('products')->truncate();
        DB::table('category_product')->truncate();
        DB::table('categories')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        // --- FIM DA LIMPEZA ---

        // 1. Encontrar um User ID (Cliente ou Admin) para atribuir as reviews
        // Tentamos encontrar um cliente, senão usamos o primeiro utilizador
        $userForReview = User::where('role', 'customer')->first() ?? User::first();
        $userId = $userForReview?->id;
        
        // Se não houver utilizadores, paramos o seeder para evitar erros
        if (!$userId) {
            echo "Aviso: Não foi possível encontrar um utilizador para atribuir reviews. Certifique-se de que a tabela 'users' não está vazia.\n";
            return; 
        }

        $categoriesData = [
            'Calçados' => ['icon' => 'heroicon-o-shoe-print'],
            'Eletrónicos' => ['icon' => 'heroicon-o-cpu-chip'],
            'Smartphones' => ['icon' => 'heroicon-o-device-phone-mobile'],
            'Acessórios' => ['icon' => 'heroicon-o-watch'],
            'Notebooks' => ['icon' => 'heroicon-o-computer-desktop'],
            'Áudio' => ['icon' => 'heroicon-o-speaker-wave'],
            'Gaming' => ['icon' => 'heroicon-o-game-controller'],
            'Câmeras' => ['icon' => 'heroicon-o-video-camera'],
            'Livros' => ['icon' => 'heroicon-o-book-open'],
        ];

        $categoryIds = [];
        foreach ($categoriesData as $name => $data) {
            $category = Category::create([
                'name' => $name,
                'description' => "Produtos relacionados à categoria {$name}.",
                'icon' => $data['icon']
            ]);
            $categoryIds[$name] = $category->id;
        }

        // 2. Definir Produtos com suas Categorias (incluindo short_description para o WA)
        $productsData = [
            [
                'name' => 'Tênis Nike Air Max 270',
                'description' => 'Tênis esportivo com tecnologia Air Max para máximo conforto e amortecimento. Ideal para corrida e uso diário, oferecendo estilo e performance.',
                'short_description' => 'Tênis esportivo com Air Max para máximo conforto.',
                'price' => 599.90,
                'image_url' => 'https://millenniumshoes.com/cdn/shop/products/AH8050-002-PHCFH001-2000_1200x.jpg?v=1735839208',
                'categories' => ['Calçados'],
                'sizes' => ['38', '39', '40', '41'],
                'colors' => ['Preto', 'Branco']
            ],
            [
                'name' => 'iPhone 15 Pro 128GB',
                'description' => 'Smartphone Apple com chip A17 Pro, câmera tripla de 48MP, tela de 6.1" e estrutura de titânio. O máximo em performance e fotografia móvel.',
                'short_description' => 'Smartphone Apple com chip A17 Pro e câmera tripla de 48MP.',
                'price' => 5499.00,
                'promotional_price' => 4999.00, // Com promoção
                'image_url' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSde-eeFinX3yv1QaVA6qxgro_OV4yW-4Y-nQ&s',
                'categories' => ['Eletrónicos', 'Smartphones']
            ],
            [
                'name' => 'Samsung Galaxy Watch 6',
                'description' => 'Smartwatch com monitoramento de saúde, GPS, tela AMOLED vibrante e bateria de 40 horas. Perfeito para acompanhar seus exercícios e notificações.',
                'short_description' => 'Smartwatch completo com monitoramento de saúde e GPS.',
                'price' => 1299.00,
                'image_url' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRms9Gb903h0sKxOAA6k3avVFR-ec3psG77Sw&s',
                'categories' => ['Acessórios', 'Eletrónicos']
            ],
            [
                'name' => 'MacBook Air M2 13"',
                'image_url' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRms9Gb903h0sKxOAA6k3avVFR-ec3psG77Sw&s',
                'description' => 'Notebook Apple com chip M2, 8GB RAM, 256GB SSD e tela Retina. O equilíbrio perfeito entre potência e portabilidade.',
                'short_description' => 'Notebook ultrafino com chip M2 e 256GB SSD.',
                'price' => 8499.00,
                'categories' => ['Eletrónicos', 'Notebooks']
            ],
            [
                'name' => 'Sony WH-1000XM5',
                'description' => 'Fone de ouvido over-ear com cancelamento de ruído ativo líder de mercado e 30h de bateria. Imersão sonora total.',
                'short_description' => 'Fone over-ear com cancelamento de ruído ativo.',
                'price' => 1899.90,
                'image_url' => 'https://www.sony.pt/image/6145c1d32e6ac8e63a46c912dc33c5bb?fmt=pjpeg&bgcolor=FFFFFF&bgc=FFFFFF&wid=2515&hei=1320',
                'categories' => ['Eletrónicos', 'Áudio']
            ],
            [
                'name' => 'PlayStation 5',
                'description' => 'Console de videogame com SSD ultrarrápido, carregamento quase instantâneo e controle DualSense com feedback háptico.',
                'short_description' => 'Console de videogame PS5 com SSD ultrarrápido.',
                'price' => 3899.00,
                'image_url' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSwJfMhv54G1WXxhmOnvaNukui-8EXqwWlkjg&s',
                'categories' => ['Gaming', 'Eletrónicos']
            ],
            [
                'name' => 'GoPro HERO12 Black',
                'description' => 'Câmera de ação 4K à prova d\'água com estabilização HyperSmooth 6.0. Capture todos os seus momentos de aventura com a melhor qualidade.',
                'short_description' => 'Câmera de ação 4K à prova d\'água.',
                'price' => 2199.00,
                'promotional_price' => 2000.00,
                'image_url' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTRlPF8Df_H-2YNsqpOKYFaJCguAPsQn9j3Zg&s',
                'categories' => ['Eletrónicos', 'Câmeras']
            ],
            [
                'name' => 'Apple AirPods Pro 2ª Geração',
                'description' => 'Fones de ouvido sem fio com cancelamento ativo de ruído e case com carregamento. Áudio imersivo e adaptativo.',
                'short_description' => 'Fones sem fio com cancelamento de ruído.',
                'price' => 1499.00,
                'image_url' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRqR6f8FBtjmt-seSHErMA3EszvnEFT_AxAag&s',
                'categories' => ['Eletrónicos', 'Áudio', 'Acessórios']
            ]
        ];
        
        // Array para guardar os produtos criados para as reviews
        $createdProducts = [];

        // 3. Criar Produtos e Associar Categorias
        foreach ($productsData as $data) {
            $categoriesToAttach = $data['categories'];

            // Campos de variação que devem ser salvos como JSON (se tiver casts no Model Product)
            $sizes = $data['sizes'] ?? null;
            $colors = $data['colors'] ?? null;

            // Garante que o slug é criado se não estiver presente nos dados
            $data['slug'] = $data['slug'] ?? Str::slug($data['name']);

            unset($data['categories'], $data['sizes'], $data['colors']);

            $product = Product::create(array_merge($data, [
                'sizes' => $sizes,
                'colors' => $colors,
            ]));
            
            $createdProducts[] = $product; // Adicionar à lista

            $ids = collect($categoriesToAttach)->map(function ($categoryName) use ($categoryIds) {
                return $categoryIds[$categoryName] ?? null;
            })->filter()->all();

            $product->categories()->attach($ids);
        }
        
        // 4. ✅ NOVO: Criar Reviews para os Produtos
        $comments = [
            'approved' => [
                "Excelente produto! Chegou antes do prazo e a qualidade é fantástica.",
                "Adorei! Cinco estrelas merecidas, recomendo a todos.",
                "Muito satisfeito com a compra. O desempenho é impecável.",
                "Melhor que o esperado. É exatamente o que eu procurava.",
                "Produto de alta qualidade e o atendimento da loja foi ótimo.",
            ],
            'pending' => [
                "Ainda estou a testar, mas a primeira impressão é boa. A entrega demorou um pouco.",
                "Recebi ontem. O design é bonito, mas a embalagem podia ser melhor.",
                "Bom custo-benefício, mas gostaria que tivessem mais opções de cor.",
            ],
            'rejected' => [
                "Este produto é péssimo, não vale o preço. Exijo o meu dinheiro de volta.",
                "Recebi um item com defeito e o suporte não responde.",
            ],
        ];

        foreach ($createdProducts as $product) {
            // Criar 2 a 3 reviews por produto
            $numberOfReviews = rand(2, 3);

            for ($i = 0; $i < $numberOfReviews; $i++) {
                // Alternar entre status 'approved', 'pending' e, ocasionalmente, 'rejected'
                $statusOptions = ['approved', 'approved', 'pending', 'rejected'];
                $status = $statusOptions[array_rand($statusOptions)];
                
                $commentArray = $comments[$status];
                
                ProductReview::create([
                    'product_id' => $product->id,
                    'user_id' => $userId, 
                    'rating' => rand(3, 5), // Reviews positivas (3, 4 ou 5)
                    'comment' => $commentArray[array_rand($commentArray)],
                    'status' => $status,
                ]);
            }
        }
    }
}