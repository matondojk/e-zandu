<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand; // ✅ Model Brand mantido
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
        DB::table('brand_product')->truncate(); // Limpar pivot de marcas
        DB::table('brands')->truncate(); // Limpar marcas
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        // --- FIM DA LIMPEZA ---

        // 1. Encontrar um User ID
        $userForReview = User::where('role', 'customer')->first() ?? User::first();
        $userId = $userForReview?->id;
        
        if (!$userId) {
            $this->command->warn("Aviso: Não foi possível encontrar um utilizador para atribuir reviews.");
        }

        // 2. ✅ NOVO: Criar Categorias (em Português)
        $categoriesData = [
            'Iluminação' => ['icon' => 'heroicon-o-light-bulb'],
            'Decoração' => ['icon' => 'heroicon-o-sparkles'],
            'Mobiliário' => ['icon' => 'heroicon-o-home'], // heroicon-o-archive-box ou similar
        ];
        $categoryIds = [];
        foreach ($categoriesData as $name => $data) {
            $category = Category::create([
                'name' => $name,
                'slug' => Str::slug($name), 
                'description' => "Produtos relacionados à categoria {$name}.",
                'icon' => $data['icon']
            ]);
            $categoryIds[$name] = $category->id;
        }

        // 3. Criar Marcas (Mantido, embora os novos produtos não as usem)
        $brandsData = [
            'Superdry', 'Converse', 'Designers Remix', 'Woo', 'Jack & Jones', 
            'Lee Jeans', 'Diesel', 'VANS', 'New Balance', 'NLY Trend',
            // Adicionar marcas genéricas de mobiliário se necessário
            'CasaChique', 'DecorArt', 'Luz&Forma'
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

        // 4. ✅ NOVO: Lista de Produtos (com dados em PT e preços em KZ)
        // Nota: Usei uma taxa de conversão aproximada e arredondei os valores.
        $productsData = [
            [
                'name' => 'Candeeiro de Mesa "Petite"',
                'description' => 'Um candeeiro de mesa pequeno e elegante, ideal para secretárias, mesas de cabeceira ou como ponto de luz decorativo. O seu design minimalista em metal adapta-se facilmente a qualquer estilo de decoração, proporcionando uma luz suave e acolhedora.',
                'short_description' => 'Candeeiro de mesa compacto e elegante.',
                'price' => 34000.00, // Preço estimado
                'image_url' => 'https://portotheme.com/html/molla/assets/images/demos/demo-2/products/product-7-1.jpg',
                'categories' => ['Iluminação'],
                'additional_images' => [
                    'https://portotheme.com/html/molla/assets/images/demos/demo-2/products/product-7-2.jpg',
                ]
            ],
            [
                'name' => 'Suporte de Lenha "Madra"',
                'description' => 'Mantenha a sua lenha organizada e acessível com o Suporte de Lenha Madra. Feito com materiais metálicos robustos e um design industrial moderno, é uma peça funcional e decorativa para a sua lareira ou sala de estar.',
                'short_description' => 'Suporte de lenha robusto com design industrial.',
                'price' => 340900.00, // $401,00 * ~850
                'image_url' => 'https://portotheme.com/html/molla/assets/images/demos/demo-2/products/product-8-1.jpg',
                'categories' => ['Decoração'],
                'additional_images' => [
                    'https://portotheme.com/html/molla/assets/images/demos/demo-2/products/product-8-2.jpg',
                ]
            ],
            [
                'name' => 'Poltrona de Jardim (Verde)',
                'description' => 'Desfrute do ar livre com esta poltrona de jardim. Construída com uma estrutura metálica resistente e um assento confortável, é ideal para varandas, terraços ou jardins. O seu design moderno em verde adiciona um toque de frescura ao seu espaço exterior.',
                'short_description' => 'Poltrona de jardim confortável e resistente.',
                'price' => 79900.00, // $94,00 * ~850
                'image_url' => 'https://portotheme.com/html/molla/assets/images/demos/demo-2/products/product-9-1.jpg',
                'categories' => ['Mobiliário'],
                'additional_images' => [
                    'https://portotheme.com/html/molla/assets/images/demos/demo-2/products/product-9-2.jpg',
                ]
            ],
            [
                'name' => 'Candeeiro de Suspensão Grande "Carronade"',
                'description' => 'O candeeiro de suspensão Carronade é uma peça de iluminação impressionante. Com um design industrial moderno, inspirado em canhões, e de grande dimensão, torna-se o ponto focal de qualquer sala de jantar ou de estar. Estrutura em metal de alta qualidade.',
                'short_description' => 'Candeeiro de suspensão de grande porte, estilo industrial.',
                'price' => 340900.00, // $401,00 * ~850
                'image_url' => 'https://portotheme.com/html/molla/assets/images/demos/demo-2/products/product-10-1.jpg',
                'categories' => ['Iluminação'],
                'additional_images' => [
                    'https://portotheme.com/html/molla/assets/images/demos/demo-2/products/product-10-2.jpg',
                ]
            ],
            [
                'name' => 'Puff (Beanbag) de Exterior Original',
                'description' => 'Relaxe no seu jardim, terraço ou à beira da piscina com este confortável puff de exterior. Feito com tecido resistente às intempéries e à água, molda-se perfeitamente ao seu corpo para um conforto inigualável. Leve e fácil de mover.',
                'short_description' => 'Puff de exterior confortável e resistente à água.',
                'price' => 220000.00, // $259,00 * ~850
                'image_url' => 'https://portotheme.com/html/molla/assets/images/demos/demo-2/products/product-11-1.jpg',
                'categories' => ['Decoração', 'Mobiliário'],
                'additional_images' => [
                    'https://portotheme.com/html/molla/assets/images/demos/demo-2/products/product-11-2.jpg',
                ]
            ],
            [
                'name' => 'Sofá de 2 Lugares (Cinzento)',
                'description' => 'Este sofá de 2 lugares combina design contemporâneo e conforto excepcional. Com linhas simples, estofamento em tecido cinzento de qualidade e pés de madeira robustos, é perfeito para salas de estar pequenas, escritórios ou como complemento.',
                'short_description' => 'Sofá compacto e moderno de dois lugares em tecido.',
                'price' => 2640000.00, // $3.107,00 * ~850
                'image_url' => 'https://portotheme.com/html/molla/assets/images/demos/demo-2/products/product-12-1.jpg',
                'categories' => ['Mobiliário'],
                'additional_images' => [
                    'https://portotheme.com/html/molla/assets/images/demos/demo-2/products/product-12-2.jpg',
                ]
            ],
            [
                'name' => 'Poltrona "Wingback" (de Orelhas)',
                'description' => 'Uma poltrona clássica "Wingback" que adiciona um toque de elegância e sofisticação a qualquer espaço. O seu design icónico com "orelhas" laterais e estofamento de alta qualidade oferece um conforto superior e um excelente apoio, ideal para leitura.',
                'short_description' => 'Poltrona clássica de "orelhas" com design intemporal.',
                'price' => 2113000.00, // $2.486,00 * ~850
                'image_url' => 'https://portotheme.com/html/molla/assets/images/demos/demo-2/products/product-13-2.jpg',
                'categories' => ['Mobiliário'],
                'additional_images' => [
                    'https://portotheme.com/html/molla/assets/images/demos/demo-2/products/product-13-1.jpg',
                ]
            ],
            [
                'name' => 'Conjunto de 3 Almofadas (Tons Neutros)',
                'description' => 'Renove a sua sala ou quarto com este elegante conjunto de 3 almofadas. Com padrões e texturas complementares em tons neutros, adicionam conforto e estilo instantaneamente ao seu sofá ou cama. Inclui capas e enchimentos.',
                'short_description' => 'Conjunto de 3 almofadas decorativas em tons neutros.',
                'price' => 169000.00, // $199,00 * ~850
                'image_url' => 'https://portotheme.com/html/molla/assets/images/demos/demo-2/products/product-14-2.jpg',
                'categories' => ['Decoração'],
                'additional_images' => [
                    'https://portotheme.com/html/molla/assets/images/demos/demo-2/products/product-14-1.jpg',
                ]
            ],
            [
                'name' => 'Conjunto de 3 Almofadas (Padrão Geométrico)',
                'description' => 'Um conjunto versátil de 3 almofadas decorativas. Este trio apresenta um design moderno com padrões geométricos e texturas contrastantes, ideal para dar um toque de personalidade e cor ao seu sofá, poltrona ou cama.',
                'short_description' => 'Conjunto de 3 almofadas com padrão geométrico moderno.',
                'price' => 169000.00, // $199,00 * ~850
                'image_url' => 'https://portotheme.com/html/molla/assets/images/demos/demo-2/products/product-15-2.jpg',
                'categories' => ['Decoração'],
                'additional_images' => [
                    'https://portotheme.com/html/molla/assets/images/demos/demo-2/products/product-15-1.jpg',
                ]
            ],
            [
                'name' => 'Candeeiro de Mesa "Carronade"',
                'description' => 'Inspirado no design do candeeiro de suspensão, esta versão de mesa Carronade é perfeita para secretárias, aparadores ou mesas de cabeceira. Oferece luz direcional com um estilo industrial marcante, combinando madeira e metal.',
                'short_description' => 'Candeeiro de mesa com design industrial (madeira e metal).',
                'price' => 424000.00, // $499,00 * ~850
                'image_url' => 'https://portotheme.com/html/molla/assets/images/demos/demo-2/products/product-16-2.jpg',
                'categories' => ['Iluminação'],
                'additional_images' => [
                    'https://portotheme.com/html/molla/assets/images/demos/demo-2/products/product-16-1.jpg',
                ]
            ],
            [
                'name' => 'Poltrona Slim "Flow" (Branca)',
                'description' => 'A poltrona "Flow" destaca-se pelo seu design slim e minimalista, de inspiração nórdica. Com um assento curvo em polipropileno branco e pernas de madeira robustas, é uma peça de mobiliário elegante e confortável para qualquer canto da casa.',
                'short_description' => 'Poltrona de design "Flow" com assento branco e pernas de madeira.',
                'price' => 110000.00, // Preço estimado
                'image_url' => 'https://portotheme.com/html/molla/assets/images/demos/demo-2/products/product-3-1.jpg',
                'categories' => ['Mobiliário'],
                'additional_images' => [
                    'https://portotheme.com/html/molla/assets/images/demos/demo-2/products/product-3-2.jpg',
                ]
            ],
            [
                'name' => 'Sofá-Cama "Roots"',
                'description' => 'O sofá-cama "Roots" é a solução perfeita para espaços multifuncionais. De dia é um sofá estiloso e moderno em madeira, e de noite transforma-se facilmente numa cama confortável para visitas. Inclui futon (colchão).',
                'short_description' => 'Sofá-cama versátil e funcional em madeira com futon.',
                'price' => 320000.00, // Preço estimado
                'image_url' => 'https://portotheme.com/html/molla/assets/images/demos/demo-2/products/product-4-1.jpg',
                'categories' => ['Mobiliário'],
                'additional_images' => [
                    'https://portotheme.com/html/molla/assets/images/demos/demo-2/products/product-4-2.jpg',
                ]
            ],
            [
                'name' => 'Banco-Escadote "Butler"',
                'description' => 'O "Butler" é um item indispensável em qualquer casa. Funciona como um banco robusto para o dia-a-dia e transforma-se num pequeno escadote de dois degraus, ideal para alcançar prateleiras altas na cozinha, despensa ou escritório. Feito em madeira.',
                'short_description' => 'Banco/escadote multiusos 2-em-1 em madeira.',
                'price' => 45000.00, // Preço estimado
                'image_url' => 'https://portotheme.com/html/molla/assets/images/demos/demo-2/products/product-1-1.jpg',
                'categories' => ['Mobiliário', 'Decoração'],
                'additional_images' => [
                    'https://portotheme.com/html/molla/assets/images/demos/demo-2/products/product-1-2.jpg',
                ]
            ],
        ];
        
        $createdProducts = [];

        // 5. Criar Produtos, Associar Categorias, Marcas e Imagens
        foreach ($productsData as $data) {
            $categoriesToAttach = $data['categories'];
            // Os novos produtos não têm marcas, mas a lógica está pronta se adicionar
            $brandsToAttach = $data['brands'] ?? []; 
            $additionalImages = $data['additional_images'];
            $sizes = $data['sizes'] ?? null;
            $colors = $data['colors'] ?? null;

            // Remove os dados aninhados antes de criar o produto
            unset($data['categories'], $data['brands'], $data['additional_images'], $data['sizes'], $data['colors']);

            $product = Product::create(array_merge($data, [
                'slug' => Str::slug($data['name']),
                'sku' => 'SKU-' . rand(10000, 99999),
                'unit' => 'Unidade',
                'sizes' => $sizes, // Será null para estes produtos
                'colors' => $colors, // Será null para estes produtos
            ]));
            
            $createdProducts[] = $product;

            // Anexar Categorias
            $ids = collect($categoriesToAttach)->map(function ($categoryName) use ($categoryIds) {
                return $categoryIds[$categoryName] ?? null;
            })->filter()->all();
            $product->categories()->attach($ids);

            // Anexar Marcas (Se existirem no array $data)
            $brandIdsToAttach = collect($brandsToAttach)->map(function ($brandName) use ($brandIds) {
                return $brandIds[$brandName] ?? null;
            })->filter()->all();

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
                    "Material de boa qualidade, recomendo.",
                    "Exactamente como na descrição. Satisfeito."
                ],
                'pending' => [
                    "Ainda estou a testar, mas a primeira impressão é boa.",
                ],
                'rejected' => [
                    "Recebi um item com defeito.",
                ],
            ];

            foreach ($createdProducts as $product) {
                // Criar 1 ou 2 reviews por produto
                $numberOfReviews = rand(1, 2); 
                for ($i = 0; $i < $numberOfReviews; $i++) {
                    // Maior probabilidade de ser aprovado
                    $statusOptions = ['approved', 'approved', 'approved', 'pending'];
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