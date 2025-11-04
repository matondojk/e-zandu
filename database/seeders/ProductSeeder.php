<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;
use App\Models\ProductReview;
use App\Models\User;
use App\Models\ProductImage; // ✅ NOVO: Importar o Model de Imagens
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // --- LIMPEZA DE TABELAS ---
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('product_reviews')->truncate();
        DB::table('product_cross_sell')->truncate();
        DB::table('product_images')->truncate(); // ✅ NOVO: Limpar imagens
        DB::table('products')->truncate();
        DB::table('category_product')->truncate();
        DB::table('categories')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        // --- FIM DA LIMPEZA ---

        // 1. Encontrar um User ID (Cliente ou Admin) para atribuir as reviews
        $userForReview = User::where('role', 'customer')->first() ?? User::first();
        $userId = $userForReview?->id;
        
        if (!$userId) {
            $this->command->warn("Aviso: Não foi possível encontrar um utilizador para atribuir reviews. Certifique-se de que a tabela 'users' não está vazia.");
        }

        // 2. ✅ NOVO: Categorias em Português
        $categoriesData = [
            'Camisetas e Blusas' => ['icon' => 'heroicon-o-user-circle'], // Ícone genérico
            'Casacos e Moletons' => ['icon' => 'heroicon-o-user-group'], // Ícone genérico
            'Calçados' => ['icon' => 'heroicon-o-hand-thumb-up'], // Ícone genérico
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

        // 3. ✅ NOVO: Lista de Produtos com base na sua entrada
        $productsData = [
            [
                'name' => 'T-Shirt Osaka Entry Superdry',
                'description' => 'Uma t-shirt clássica da Superdry com a icónica estampa Osaka. Feita em algodão macio de alta qualidade, é perfeita para um look casual, confortável e cheio de estilo. Essencial para o dia-a-dia.',
                'short_description' => 'T-shirt clássica Superdry em algodão com estampa Osaka.',
                'price' => 7990.00,
                'image_url' => 'https://demos.flatsome.com/wp-content/uploads/2013/08/538228-0286_2.jpeg',
                'categories' => ['Camisetas e Blusas'],
                'sizes' => ['P', 'M', 'G', 'GG'],
                'colors' => ['Preto', 'Branco'],
                'additional_images' => [
                    'https://demos.flatsome.com/wp-content/uploads/2013/08/538228-0286_3.jpeg',
                    'https://demos.flatsome.com/wp-content/uploads/2013/08/538228-0286_2.jpeg',
                    'https://demos.flatsome.com/wp-content/uploads/2013/08/538228-0286_5.jpeg',
                    'https://demos.flatsome.com/wp-content/uploads/2013/08/538228-0286_1.jpeg',
                ]
            ],
            [
                'name' => 'Tênis All Star Canvas Hi Converse',
                'description' => 'O lendário tênis de cano alto da Converse (Hi). Com um design intemporal em lona resistente e solado de borracha vulcanizada, é um item essencial e versátil em qualquer guarda-roupa.',
                'short_description' => 'O lendário tênis de cano alto da Converse em lona.',
                'price' => 12990.00,
                'image_url' => 'https://demos.flatsome.com/wp-content/uploads/2013/08/282004-0286_2.jpeg',
                'categories' => ['Calçados'],
                'sizes' => ['37', '38', '39', '40', '41', '42'],
                'colors' => ['Preto', 'Branco', 'Vermelho', 'Azul'],
                'additional_images' => [
                    'https://demos.flatsome.com/wp-content/uploads/2013/08/282004-0427_2.jpeg',
                    'https://demos.flatsome.com/wp-content/uploads/2013/08/282004-0046_2.jpeg',
                    'https://demos.flatsome.com/wp-content/uploads/2013/08/282004-0001_3.jpeg',
                    'https://demos.flatsome.com/wp-content/uploads/2013/08/282004-0259_2.jpeg',
                ]
            ],
            [
                'name' => 'Pulôver Fluro Big Designers Remix',
                'description' => 'Um pulôver oversized da Designers Remix com uma cor flúor vibrante. Perfeito para se destacar na multidão, combinando o máximo de conforto com um estilo urbano e arrojado.',
                'short_description' => 'Pulôver oversized em cor flúor vibrante.',
                'price' => 14500.00,
                'image_url' => 'https://demos.flatsome.com/wp-content/uploads/2013/08/252678-0029_1.jpeg?style=layout-grid-style-2',
                'categories' => ['Casacos e Moletons'],
                'sizes' => ['P', 'M', 'G'],
                'colors' => ['Verde Flúor'],
                'additional_images' => [
                    'https://demos.flatsome.com/wp-content/uploads/2013/08/252678-0029_2.jpeg',
                    'https://demos.flatsome.com/wp-content/uploads/2013/08/252678-0029_5.jpeg',
                    'https://demos.flatsome.com/wp-content/uploads/2013/08/252678-0029_3.jpeg',
                ]
            ],
            [
                'name' => 'T-Shirt Premium Happy Ninja', // Nome ajustado
                'description' => 'Mostre o seu lado ninja com esta t-shirt premium. Feita com 100% algodão, apresenta um design \'Happy Ninja\' exclusivo na frente e nas costas. Conforto e atitude num só produto.',
                'short_description' => 'T-shirt 100% algodão com design exclusivo \'Happy Ninja\'.',
                'price' => 6990.00,
                'image_url' => 'https://demos.flatsome.com/wp-content/uploads/2013/06/T_7_front.jpg',
                'categories' => ['Camisetas e Blusas'],
                'sizes' => ['M', 'G', 'GG'],
                'colors' => ['Preto'],
                'additional_images' => [
                    'https://demos.flatsome.com/wp-content/uploads/2013/06/T_7_back.jpg?style=layout-grid-style-2',
                    'https://demos.flatsome.com/wp-content/uploads/2013/06/T_6_front.jpg',
                ]
            ],
            [
                'name' => 'Moletom com Capuz Ninja (Preto)', // Nome ajustado
                'description' => 'Conforto e estilo se encontram neste moletom com capuz. O design minimalista \'Ninja\' em preto torna-o uma peça versátil e essencial para dias mais frios. Possui bolso canguru e capuz ajustável.',
                'short_description' => 'Moletom com capuz e design minimalista \'Ninja\'.',
                'price' => 17990.00,
                'image_url' => 'https://demos.flatsome.com/wp-content/uploads/2013/06/hoodie_4_front.jpg',
                'categories' => ['Casacos e Moletons'],
                'sizes' => ['P', 'M', 'G', 'GG'],
                'colors' => ['Preto'],
                'additional_images' => []
            ],
            [
                'name' => 'Moletom com Capuz Básico (Preto)',
                'description' => 'Um moletom preto essencial. Com capuz ajustável e bolso canguru, é a peça perfeita para conforto diário. Feito de algodão macio e durável, um verdadeiro coringa.',
                'short_description' => 'Moletom preto essencial com capuz e bolso canguru.',
                'price' => 15990.00,
                'image_url' => 'https://demos.flatsome.com/wp-content/uploads/2013/06/hoodie_7_front.jpg',
                'categories' => ['Casacos e Moletons'],
                'sizes' => ['P', 'M', 'G'],
                'colors' => ['Preto'],
                'additional_images' => []
            ],
            [
                'name' => 'T-Shirt Clássica (Vermelha)',
                'description' => 'Uma t-shirt clássica de cor vermelha vibrante. Essencial para qualquer look, feita em algodão puro para máximo conforto. Mostra o design na frente e nas costas.',
                'short_description' => 'T-shirt clássica vermelha em algodão puro.',
                'price' => 5990.00,
                'image_url' => 'https://demos.flatsome.com/wp-content/uploads/2013/06/T_1_front-494x593.jpg',
                'categories' => ['Camisetas e Blusas'],
                'sizes' => ['P', 'M'],
                'colors' => ['Vermelho'],
                'additional_images' => [
                    'https://demos.flatsome.com/wp-content/uploads/2013/06/T_1_back-494x593.jpg',
                ]
            ],
            [
                'name' => 'Moletom com Capuz Ninja (Cinza)',
                'description' => 'Este moletom com capuz cinza apresenta o popular design \'Ninja\'. Com múltiplos designs disponíveis (frente e costas), é perfeito para fãs da marca que procuram estilo e conforto.',
                'short_description' => 'Moletom com capuz cinza e design \'Ninja\'.',
                'price' => 17990.00,
                'image_url' => 'https://demos.flatsome.com/wp-content/uploads/2013/06/hoodie_2_front-494x593.jpg',
                'categories' => ['Casacos e Moletons'],
                'sizes' => ['P', 'M', 'G', 'GG'],
                'colors' => ['Cinza Mescla'],
                'additional_images' => [
                    'https://demos.flatsome.com/wp-content/uploads/2013/06/hoodie_3_front-494x593.jpg',
                    'https://demos.flatsome.com/wp-content/uploads/2013/06/hoodie_2_back-494x593.jpg',
                    'https://demos.flatsome.com/wp-content/uploads/2013/06/hoodie_3_back-494x593.jpg',
                ]
            ],
            [
                'name' => 'Moletom Woo (Preto)',
                'description' => 'Represente a marca Woo com este moletom preto de alta qualidade. Confortável, quente e com o logo icónico \'Woo\' estampado no peito.',
                'short_description' => 'Moletom preto da marca Woo com logo icónico.',
                'price' => 16500.00,
                'image_url' => 'https://demos.flatsome.com/wp-content/uploads/2013/06/hoodie_6_front-494x593.jpg',
                'categories' => ['Casacos e Moletons'],
                'sizes' => ['P', 'M', 'G', 'GG'],
                'colors' => ['Preto'],
                'additional_images' => []
            ],
            [
                'name' => 'T-Shirt Ninja Minimalista',
                'description' => 'Para quem prefere um estilo mais discreto. Esta t-shirt preta apresenta um logo \'Ninja\' minimalista no peito, combinando simplicidade e estilo em 100% algodão.',
                'short_description' => 'T-shirt com logo \'Ninja\' minimalista e discreto.',
                'price' => 6490.00,
                'image_url' => 'https://demos.flatsome.com/wp-content/uploads/2013/06/T_5_front-494x593.jpg',
                'categories' => ['Camisetas e Blusas'],
                'sizes' => ['P', 'M', 'G'],
                'colors' => ['Preto'],
                'additional_images' => []
            ],
            [
                'name' => 'T-Shirt Bjorn SS Jack & Jones',
                'description' => 'T-shirt de manga curta (SS) da Jack & Jones, modelo Bjorn. Apresenta uma estampa gráfica moderna e arrojada no peito, ideal para looks urbanos e descontraídos.',
                'short_description' => 'T-shirt de manga curta Jack & Jones com estampa gráfica.',
                'price' => 7200.00,
                'image_url' => 'https://demos.flatsome.com/wp-content/uploads/2013/08/634021-5326_1.jpeg',
                'categories' => ['Camisetas e Blusas'],
                'sizes' => ['P', 'M', 'G', 'GG'],
                'colors' => ['Branco', 'Preto'],
                'additional_images' => [
                    'https://demos.flatsome.com/wp-content/uploads/2013/08/634021-5326_5.jpeg',
                    'https://demos.flatsome.com/wp-content/uploads/2013/08/634021-5326_3.jpeg',
                ]
            ],
            [
                'name' => 'T-Shirt Jeansmaker Lee Jeans',
                'description' => 'Uma homenagem à tradição da Lee Jeans. Esta t-shirt \'Jeansmaker\' celebra a herança da marca com um design vintage e confortável em algodão premium.',
                'short_description' => 'T-shirt Lee Jeans com design \'Jeansmaker\' vintage.',
                'price' => 8100.00,
                'image_url' => 'https://demos.flatsome.com/wp-content/uploads/2013/08/863232-6132_5.jpeg',
                'categories' => ['Camisetas e Blusas'],
                'sizes' => ['M', 'G', 'GG'],
                'colors' => ['Cinza', 'Azul Marinho'],
                'additional_images' => [
                    'https://demos.flatsome.com/wp-content/uploads/2013/08/863232-6132_3.jpeg?style=layout-grid-style-2',
                    'https://demos.flatsome.com/wp-content/uploads/2013/08/863232-6132_1.jpeg?style=layout-grid-style-2',
                ]
            ],
            [
                'name' => 'T-Shirt Randal Jack & Jones',
                'description' => 'A t-shirt Randal da Jack & Jones combina um corte clássico com uma estampa subtil. Feita em algodão de primeira, é uma peça chave para o dia-a-dia.',
                'short_description' => 'T-shirt Jack & Jones com corte clássico e estampa subtil.',
                'price' => 7200.00,
                'image_url' => 'https://demos.flatsome.com/wp-content/uploads/2013/08/634050-2891_5.jpeg?style=layout-grid-style-2',
                'categories' => ['Camisetas e Blusas'],
                'sizes' => ['P', 'M', 'G'],
                'colors' => ['Azul', 'Branco'],
                'additional_images' => [
                    'https://demos.flatsome.com/wp-content/uploads/2013/08/634050-2891_3.jpeg?style=layout-grid-style-2',
                    'https://demos.flatsome.com/wp-content/uploads/2013/08/634050-2891_2.jpeg?style=layout-grid-style-2',
                ]
            ],
            [
                'name' => 'Tênis All Star Print Ox Converse (Cano Baixo)',
                'description' => 'O clássico All Star de cano baixo (Ox) numa versão com estampa exclusiva. Conforto e estilo icónico para quem gosta de se destacar com um toque de originalidade.',
                'short_description' => 'Clássico All Star de cano baixo com estampa exclusiva.',
                'price' => 13500.00,
                'image_url' => 'https://demos.flatsome.com/wp-content/uploads/2013/08/282243-0008_2.jpeg?style=layout-grid-style-2',
                'categories' => ['Calçados'],
                'sizes' => ['38', '39', '40'],
                'colors' => ['Estampado'],
                'additional_images' => [
                    'https://demos.flatsome.com/wp-content/uploads/2013/08/282243-0008_1-629x800.jpeg',
                ]
            ],
            [
                'name' => 'Tênis Magnete Exposure Diesel',
                'description' => 'Tênis de cano alto da Diesel, modelo Magnete Exposure. Design arrojado em denim (jeans) com o branding icónico da Diesel. Perfeito para um look urbano e moderno.',
                'short_description' => 'Tênis de cano alto Diesel em denim com design arrojado.',
                'price' => 21990.00,
                'image_url' => 'https://demos.flatsome.com/wp-content/uploads/2013/08/217212-0001_2-494x593.jpeg',
                'categories' => ['Calçados'],
                'sizes' => ['40', '41', '42'],
                'colors' => ['Jeans'],
                'additional_images' => [
                    'https://demos.flatsome.com/wp-content/uploads/2013/08/217212-0001_1-494x593.jpeg',
                ]
            ],
            [
                'name' => 'Tênis U Old Skool VANS',
                'description' => 'O icónico tênis de skate da VANS com a sidestripe lateral. O Old Skool nunca sai de moda, oferecendo durabilidade, conforto e um estilo inconfundível.',
                'short_description' => 'Icónico tênis de skate VANS com a sidestripe lateral.',
                'price' => 14990.00,
                'image_url' => 'https://demos.flatsome.com/wp-content/uploads/2013/08/282245-0286_2-494x593.jpeg',
                'categories' => ['Calçados'],
                'sizes' => ['38', '39', '40', '41', '42'],
                'colors' => ['Preto/Branco'],
                'additional_images' => [
                    'https://demos.flatsome.com/wp-content/uploads/2013/08/282245-0286_1-494x593.jpeg',
                ]
            ],
            [
                'name' => 'Tênis U420GKW New Balance',
                'description' => 'O modelo U420 da New Balance combina um design retro-running com conforto moderno. Leve, estiloso e com materiais premium, é ideal para o uso diário.',
                'short_description' => 'Tênis New Balance com design retro-running, leve e estiloso.',
                'price' => 16500.00,
                'image_url' => 'https://demos.flatsome.com/wp-content/uploads/2013/08/741227-0076_2-494x593.jpeg',
                'categories' => ['Calçados'],
                'sizes' => ['39', '40', '41'],
                'colors' => ['Cinza/Branco'],
                'additional_images' => [
                    'https://demos.flatsome.com/wp-content/uploads/2013/08/741227-0076_1-494x593.jpeg',
                ]
            ],
            [
                'name' => 'Top Beyond NLY Trend',
                'description' => 'Um top moderno da NLY Trend, modelo Beyond. Com um corte elegante, costas abertas e tecido leve, é perfeito para compor looks de verão ou para a noite.',
                'short_description' => 'Top moderno da NLY Trend com corte elegante e costas abertas.',
                'price' => 8990.00,
                'image_url' => 'https://demos.flatsome.com/wp-content/uploads/2013/08/917542-0001_1.jpeg',
                'categories' => ['Camisetas e Blusas'],
                'sizes' => ['P', 'M'],
                'colors' => ['Preto'],
                'additional_images' => [
                    'https://demos.flatsome.com/wp-content/uploads/2013/08/917542-0001_5.jpeg',
                    'https://demos.flatsome.com/wp-content/uploads/2013/08/917542-0001_3.jpeg',
                ]
            ],
            [
                'name' => 'Sweater Brooklyn Manga Longa',
                'description' => 'Sweater de manga longa com estampa \'Brooklyn\'. Uma peça confortável e estilosa, ideal para meia-estação, feita em malha de algodão leve.',
                'short_description' => 'Sweater de manga longa em malha com estampa \'Brooklyn\'.',
                'price' => 9990.00,
                'image_url' => 'https://demos.flatsome.com/wp-content/uploads/2013/08/258836-0426_1-494x593.jpeg',
                'categories' => ['Camisetas e Blusas'],
                'sizes' => ['P', 'M', 'G'],
                'colors' => ['Branco'],
                'additional_images' => []
            ],
        ];
        
        // Array para guardar os produtos criados para as reviews
        $createdProducts = [];

        // 4. Criar Produtos, Associar Categorias e Adicionar Imagens
        foreach ($productsData as $data) {
            $categoriesToAttach = $data['categories'];
            $additionalImages = $data['additional_images'];
            $sizes = $data['sizes'] ?? null;
            $colors = $data['colors'] ?? null;

            // Remove os dados aninhados antes de criar o produto
            unset($data['categories'], $data['additional_images'], $data['sizes'], $data['colors']);

            $product = Product::create(array_merge($data, [
                'slug' => Str::slug($data['name']),
                'sku' => 'SKU-' . rand(10000, 99999),
                'unit' => 'Unidade',
                'sizes' => $sizes,
                'colors' => $colors,
            ]));
            
            $createdProducts[] = $product; // Adicionar à lista para reviews

            // Anexar Categorias
            $ids = collect($categoriesToAttach)->map(function ($categoryName) use ($categoryIds) {
                return $categoryIds[$categoryName] ?? null;
            })->filter()->all();

            $product->categories()->attach($ids);

            // ✅ NOVO: Adicionar Imagens Adicionais
            $uniqueImages = array_unique($additionalImages);
            $sortOrder = 1;
            foreach ($uniqueImages as $imgUrl) {
                // Evita adicionar a imagem principal novamente
                if ($imgUrl !== $product->image_url) {
                    ProductImage::create([
                        'product_id' => $product->id,
                        'url' => $imgUrl,
                        'sort_order' => $sortOrder++,
                    ]);
                }
            }
        }
        
        // 5. Criar Reviews para os Produtos (Lógica mantida do seu seeder original)
        if ($userId) {
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
                $numberOfReviews = rand(2, 3);
                for ($i = 0; $i < $numberOfReviews; $i++) {
                    $statusOptions = ['approved', 'approved', 'pending', 'rejected'];
                    $status = $statusOptions[array_rand($statusOptions)];
                    $commentArray = $comments[$status];
                    
                    ProductReview::create([
                        'product_id' => $product->id,
                        'user_id' => $userId, 
                        'rating' => rand(3, 5),
                        'comment' => $commentArray[array_rand($commentArray)],
                        'status' => $status,
                    ]);
                }
            }
        }
    }
}