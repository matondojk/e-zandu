<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand; // ✅ NOVO: Importar o Model Brand
use App\Models\ProductReview;
use App\Models\User;
use App\Models\ProductImage;
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
        DB::table('product_images')->truncate();
        DB::table('products')->truncate();
        DB::table('category_product')->truncate();
        DB::table('categories')->truncate();
        DB::table('brand_product')->truncate(); // ✅ NOVO: Limpar pivot de marcas
        DB::table('brands')->truncate(); // ✅ NOVO: Limpar marcas
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        // --- FIM DA LIMPEZA ---

        // 1. Encontrar um User ID
        $userForReview = User::where('role', 'customer')->first() ?? User::first();
        $userId = $userForReview?->id;
        
        if (!$userId) {
            $this->command->warn("Aviso: Não foi possível encontrar um utilizador para atribuir reviews.");
        }

        // 2. Criar Categorias
        $categoriesData = [
            'Camisetas e Blusas' => ['icon' => 'heroicon-o-user-circle'],
            'Casacos e Moletons' => ['icon' => 'heroicon-o-user-group'],
            'Calçados' => ['icon' => 'heroicon-o-hand-thumb-up'],
        ];
        $categoryIds = [];
        foreach ($categoriesData as $name => $data) {
            $category = Category::create([
                'name' => $name,
                'slug' => Str::slug($name), // Adicionado slug para consistência
                'description' => "Produtos relacionados à categoria {$name}.",
                'icon' => $data['icon']
            ]);
            $categoryIds[$name] = $category->id;
        }

        // 3. ✅ NOVO: Criar Marcas
        $brandsData = [
            'Superdry', 'Converse', 'Designers Remix', 'Woo', 'Jack & Jones', 
            'Lee Jeans', 'Diesel', 'VANS', 'New Balance', 'NLY Trend'
        ];
        $brandIds = [];
        foreach ($brandsData as $name) {
            $brand = Brand::create([
                'name' => $name,
                'slug' => Str::slug($name),
                'description' => "Produtos da marca {$name}."
            ]);
            $brandIds[$name] = $brand->id;
        }

        // 4. ✅ NOVO: Lista de Produtos (com 'brands' adicionada)
        $productsData = [
            [
                'name' => 'T-Shirt Osaka Entry Superdry',
                'description' => 'Uma t-shirt clássica da Superdry com a icónica estampa Osaka. Feita em algodão macio de alta qualidade, é perfeita para um look casual, confortável e cheio de estilo. Essencial para o dia-a-dia.',
                'short_description' => 'T-shirt clássica Superdry em algodão com estampa Osaka.',
                'price' => 7990.00,
                'image_url' => 'https://demos.flatsome.com/wp-content/uploads/2013/08/538228-0286_2.jpeg',
                'categories' => ['Camisetas e Blusas'],
                'brands' => ['Superdry'], // ✅
                'sizes' => ['P', 'M', 'G', 'GG'],
                'colors' => ['Preto', 'Branco'],
                'additional_images' => [
                    'https://demos.flatsome.com/wp-content/uploads/2013/08/538228-0286_3.jpeg',
                    'https://demos.flatsome.com/wp-content/uploads/2013/08/538228-0286_2.jpeg',
                ]
            ],
            [
                'name' => 'Tênis All Star Canvas Hi Converse',
                'description' => 'O lendário tênis de cano alto da Converse (Hi). Com um design intemporal em lona resistente e solado de borracha vulcanizada, é um item essencial e versátil em qualquer guarda-roupa.',
                'short_description' => 'O lendário tênis de cano alto da Converse em lona.',
                'price' => 12990.00,
                'image_url' => 'https://demos.flatsome.com/wp-content/uploads/2013/08/282004-0286_2.jpeg',
                'categories' => ['Calçados'],
                'brands' => ['Converse'], // ✅
                'sizes' => ['37', '38', '39', '40', '41', '42'],
                'colors' => ['Preto', 'Branco', 'Vermelho', 'Azul'],
                'additional_images' => [
                    'https://demos.flatsome.com/wp-content/uploads/2013/08/282004-0427_2.jpeg',
                    'https://demos.flatsome.com/wp-content/uploads/2013/08/282004-0046_2.jpeg',
                ]
            ],
            [
                'name' => 'Pulôver Fluro Big Designers Remix',
                'description' => 'Um pulôver oversized da Designers Remix com uma cor flúor vibrante. Perfeito para se destacar na multidão, combinando o máximo de conforto com um estilo urbano e arrojado.',
                'short_description' => 'Pulôver oversized em cor flúor vibrante.',
                'price' => 14500.00,
                'image_url' => 'https://demos.flatsome.com/wp-content/uploads/2013/08/252678-0029_1.jpeg?style=layout-grid-style-2',
                'categories' => ['Casacos e Moletons'],
                'brands' => ['Designers Remix'], // ✅
                'sizes' => ['P', 'M', 'G'],
                'colors' => ['Verde Flúor'],
                'additional_images' => [
                    'https://demos.flatsome.com/wp-content/uploads/2013/08/252678-0029_2.jpeg',
                ]
            ],
            [
                'name' => 'T-Shirt Premium Happy Ninja',
                'description' => 'Mostre o seu lado ninja com esta t-shirt premium. Feita com 100% algodão, apresenta um design \'Happy Ninja\' exclusivo na frente e nas costas. Conforto e atitude num só produto.',
                'short_description' => 'T-shirt 100% algodão com design exclusivo \'Happy Ninja\'.',
                'price' => 6990.00,
                'image_url' => 'https://demos.flatsome.com/wp-content/uploads/2013/06/T_7_front.jpg',
                'categories' => ['Camisetas e Blusas'],
                'brands' => ['Woo'], // ✅
                'sizes' => ['M', 'G', 'GG'],
                'colors' => ['Preto'],
                'additional_images' => [
                    'https://demos.flatsome.com/wp-content/uploads/2013/06/T_7_back.jpg?style=layout-grid-style-2',
                ]
            ],
            [
                'name' => 'Moletom com Capuz Ninja (Preto)',
                'description' => 'Conforto e estilo se encontram neste moletom com capuz. O design minimalista \'Ninja\' em preto torna-o uma peça versátil e essencial para dias mais frios. Possui bolso canguru e capuz ajustável.',
                'short_description' => 'Moletom com capuz e design minimalista \'Ninja\'.',
                'price' => 17990.00,
                'image_url' => 'https://demos.flatsome.com/wp-content/uploads/2013/06/hoodie_4_front.jpg',
                'categories' => ['Casacos e Moletons'],
                'brands' => ['Woo'], // ✅
                'sizes' => ['P', 'M', 'G', 'GG'],
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
                'brands' => ['Jack & Jones'], // ✅
                'sizes' => ['P', 'M', 'G', 'GG'],
                'colors' => ['Branco', 'Preto'],
                'additional_images' => [
                    'https://demos.flatsome.com/wp-content/uploads/2013/08/634021-5326_5.jpeg',
                ]
            ],
            [
                'name' => 'T-Shirt Jeansmaker Lee Jeans',
                'description' => 'Uma homenagem à tradição da Lee Jeans. Esta t-shirt \'Jeansmaker\' celebra a herança da marca com um design vintage e confortável em algodão premium.',
                'short_description' => 'T-shirt Lee Jeans com design \'Jeansmaker\' vintage.',
                'price' => 8100.00,
                'image_url' => 'https://demos.flatsome.com/wp-content/uploads/2013/08/863232-6132_5.jpeg',
                'categories' => ['Camisetas e Blusas'],
                'brands' => ['Lee Jeans'], // ✅
                'sizes' => ['M', 'G', 'GG'],
                'colors' => ['Cinza', 'Azul Marinho'],
                'additional_images' => [
                    'https://demos.flatsome.com/wp-content/uploads/2013/08/863232-6132_3.jpeg?style=layout-grid-style-2',
                ]
            ],
            [
                'name' => 'Tênis Magnete Exposure Diesel',
                'description' => 'Tênis de cano alto da Diesel, modelo Magnete Exposure. Design arrojado em denim (jeans) com o branding icónico da Diesel. Perfeito para um look urbano e moderno.',
                'short_description' => 'Tênis de cano alto Diesel em denim com design arrojado.',
                'price' => 21990.00,
                'image_url' => 'https://demos.flatsome.com/wp-content/uploads/2013/08/217212-0001_2-494x593.jpeg',
                'categories' => ['Calçados'],
                'brands' => ['Diesel'], // ✅
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
                'brands' => ['VANS'], // ✅
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
                'brands' => ['New Balance'], // ✅
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
                'brands' => ['NLY Trend'], // ✅
                'sizes' => ['P', 'M'],
                'colors' => ['Preto'],
                'additional_images' => [
                    'https://demos.flatsome.com/wp-content/uploads/2013/08/917542-0001_5.jpeg',
                ]
            ],
            // ... (restantes produtos sem marca definida)
        ];
        
        $createdProducts = [];

        // 5. Criar Produtos, Associar Categorias, Marcas e Imagens
        foreach ($productsData as $data) {
            $categoriesToAttach = $data['categories'];
            $brandsToAttach = $data['brands'] ?? []; // ✅ NOVO: Pega as marcas
            $additionalImages = $data['additional_images'];
            $sizes = $data['sizes'] ?? null;
            $colors = $data['colors'] ?? null;

            // Remove os dados aninhados antes de criar o produto
            unset($data['categories'], $data['brands'], $data['additional_images'], $data['sizes'], $data['colors']); // ✅ 'brands' removida

            $product = Product::create(array_merge($data, [
                'slug' => Str::slug($data['name']),
                'sku' => 'SKU-' . rand(10000, 99999),
                'unit' => 'Unidade',
                'sizes' => $sizes,
                'colors' => $colors,
            ]));
            
            $createdProducts[] = $product;

            // Anexar Categorias
            $ids = collect($categoriesToAttach)->map(function ($categoryName) use ($categoryIds) {
                return $categoryIds[$categoryName] ?? null;
            })->filter()->all();
            $product->categories()->attach($ids);

            // ✅ NOVO: Anexar Marcas
            $brandIdsToAttach = collect($brandsToAttach)->map(function ($brandName) use ($brandIds) {
                return $brandIds[$brandName] ?? null;
            })->filter()->all();

            // Isto assume que o seu modelo Product tem a relação brands()
            // definida (belongsToMany) como fizemos no BrandResource
            if (!empty($brandIdsToAttach)) {
                $product->brands()->attach($brandIdsToAttach);
            }

            // Adicionar Imagens Adicionais
            $uniqueImages = array_unique($additionalImages);
            $sortOrder = 1;
            foreach ($uniqueImages as $imgUrl) {
                if ($imgUrl !== $product->image_url) {
                    ProductImage::create([
                        'product_id' => $product->id,
                        'url' => $imgUrl,
                        'sort_order' => $sortOrder++,
                    ]);
                }
            }
        }
        
        // 6. Criar Reviews para os Produtos
        if ($userId) {
            $comments = [
                'approved' => [
                    "Excelente produto! Chegou antes do prazo.",
                    "Adorei! Cinco estrelas merecidas.",
                ],
                'pending' => [
                    "Ainda estou a testar, mas a primeira impressão é boa.",
                ],
                'rejected' => [
                    "Recebi um item com defeito.",
                ],
            ];

            foreach ($createdProducts as $product) {
                $numberOfReviews = rand(1, 2); // Reduzido para ser mais rápido
                for ($i = 0; $i < $numberOfReviews; $i++) {
                    $statusOptions = ['approved', 'approved', 'pending'];
                    $status = $statusOptions[array_rand($statusOptions)];
                    $commentArray = $comments[$status];
                    
                    ProductReview::create([
                        'product_id' => $product->id,
                        'user_id' => $userId, 
                        'rating' => rand(4, 5), // Reviews maioritariamente boas
                        'comment' => $commentArray[array_rand($commentArray)],
                        'status' => $status,
                    ]);
                }
            }
        }
    }
}