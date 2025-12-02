<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
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
        DB::table('brand_product')->truncate(); 
        DB::table('brands')->truncate(); 
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        // --- FIM DA LIMPEZA ---

        // 1. Encontrar um User ID para as reviews
        $userForReview = User::where('role', 'customer')->first() ?? User::first();
        $userId = $userForReview?->id;
        
        if (!$userId) {
            $this->command->warn("Aviso: Não foi possível encontrar um utilizador para atribuir reviews.");
        }

        // 2. Criar Categorias (Com ícones FontAwesome)
        $categoriesData = [
            'Smartphones' => ['icon' => 'fa-solid fa-mobile-screen-button'],
            'Portáteis' => ['icon' => 'fa-solid fa-laptop'],
            'Desktops & All-in-One' => ['icon' => 'fa-solid fa-desktop'],
            'Componentes' => ['icon' => 'fa-solid fa-memory'],
            'Periféricos' => ['icon' => 'fa-solid fa-keyboard'],
            'Armazenamento' => ['icon' => 'fa-solid fa-hard-drive'],
            'Mobiliário de Escritório' => ['icon' => 'fa-solid fa-chair'],
        ];

        $categoryIds = [];
        foreach ($categoriesData as $name => $data) {
            $category = Category::create([
                'name' => $name,
                'slug' => Str::slug($name), 
                'description' => "Os melhores produtos da categoria {$name}.",
                'icon' => $data['icon']
            ]);
            $categoryIds[$name] = $category->id;
        }

        // 3. Criar Marcas
        $brandsData = [
            'Samsung', 'HP', 'FLAT', 'Wave', 'Venture', 'Xpresscam', 'Genérico'
        ];
        
        $brandIds = [];
        foreach ($brandsData as $name) {
            $brand = Brand::create([
                'name' => $name,
                'slug' => Str::slug($name),
                'description' => "Produtos tecnológicos da marca {$name}."
            ]);
            $brandIds[$name] = $brand->id;
        }

        // 4. Lista de Produtos (Tech)
        $productsData = [
            // --- SMARTPHONES ---
            [
                'name' => 'Smartphone FLAT 2C 2GB+64GB Preto',
                'categories' => ['Smartphones'],
                'brands' => ['FLAT'],
                'price' => 45000.00,
                'short_description' => 'Smartphone de entrada com 2GB RAM e 64GB de armazenamento.',
                'description' => 'O FLAT 2C é a escolha ideal para quem procura funcionalidade a um preço acessível. Com um design compacto em preto, oferece 64GB de armazenamento para as suas fotos e aplicações essenciais, suportado por 2GB de RAM para tarefas diárias.',
                'image_url' => 'https://ncrangola.vtexassets.com/arquivos/ids/172657-1000-1000/6931548318828-.jpg',
                'additional_images' => []
            ],
            [
                'name' => 'Smartphone Wave 8C 2GB+64GB Azul',
                'categories' => ['Smartphones'],
                'brands' => ['Wave'],
                'price' => 48000.00,
                'short_description' => 'Telemóvel Wave 8C Azul, económico e funcional.',
                'description' => 'Destaque-se com o Smartphone Wave 8C na cor Azul. Equipado com 2GB de RAM e 64GB de memória interna, este dispositivo oferece um equilíbrio perfeito entre estilo e utilidade para o dia-a-dia.',
                'image_url' => 'https://ncrangola.vtexassets.com/arquivos/ids/172153/Smartphone-.jpg?v=638923999896200000',
                'additional_images' => [
                    'https://ncrangola.vtexassets.com/arquivos/ids/172154/Smartphone-1.jpg?v=638924000100100000',
                    'https://ncrangola.vtexassets.com/arquivos/ids/172155/Smartphone-2.jpg?v=638924000302500000',
                    'https://ncrangola.vtexassets.com/arquivos/ids/172156/Smartphone-3.jpg?v=638924000404570000',
                    'https://ncrangola.vtexassets.com/arquivos/ids/172157/Smartphone-4.jpg?v=638924000607800000',
                    'https://ncrangola.vtexassets.com/arquivos/ids/172159/Smartphone-6.jpg?v=638924001125030000'
                ]
            ],
            [
                'name' => 'Smartphone Galaxy A26 DS 6GB/128GB Preto',
                'categories' => ['Smartphones'],
                'brands' => ['Samsung'],
                'price' => 220000.00,
                'short_description' => 'Samsung Galaxy A26 Dual Sim, 6GB RAM, 128GB Armazenamento.',
                'description' => 'Experimente a qualidade Samsung com o Galaxy A26. Com um processador rápido, 6GB de RAM para multitarefa fluida e 128GB de espaço. O sistema Dual Sim permite gerir a sua vida pessoal e profissional num único dispositivo elegante.',
                'image_url' => 'https://ncrangola.vtexassets.com/arquivos/ids/170672/Galaxy-1.jpg?v=638868676433170000',
                'additional_images' => [
                    'https://ncrangola.vtexassets.com/arquivos/ids/170671/Galaxy-.jpg?v=638868675725630000',
                    'https://ncrangola.vtexassets.com/arquivos/ids/170673/Galaxy-2.jpg?v=638868678036900000',
                    'https://ncrangola.vtexassets.com/arquivos/ids/170674/Galaxy-3.jpg?v=638868678439630000',
                    'https://ncrangola.vtexassets.com/arquivos/ids/170676/Galaxy-5.jpg?v=638868678844130000',
                    'https://ncrangola.vtexassets.com/arquivos/ids/170675/Galaxy-4.jpg?v=638868678742030000'
                ]
            ],
            [
                'name' => 'Smartphone FLAT 2 6GB+256GB Preto',
                'categories' => ['Smartphones'],
                'brands' => ['FLAT'],
                'price' => 95000.00,
                'promotional_price' => 89900.00,
                'short_description' => 'Versão avançada do FLAT com 256GB de memória.',
                'description' => 'O FLAT 2 eleva a fasquia com 6GB de RAM e uns impressionantes 256GB de armazenamento interno. Ideal para quem guarda muitos ficheiros, vídeos e fotos, sem comprometer o orçamento.',
                'image_url' => 'https://ncrangola.vtexassets.com/arquivos/ids/172654-1000-1000/6931548318880-.jpg?v=638947374995100000',
                'additional_images' => []
            ],
            [
                'name' => 'Smartphone Galaxy S25 12GB/256GB DS Azul Escuro',
                'categories' => ['Smartphones'],
                'brands' => ['Samsung'],
                'price' => 1100000.00,
                'short_description' => 'Topo de gama Samsung S25, 12GB RAM, IA integrada.',
                'description' => 'O auge da tecnologia móvel. O Galaxy S25 em Azul Escuro oferece desempenho extremo com 12GB de RAM e câmaras de nível profissional. Ecrã imersivo e bateria de longa duração para os utilizadores mais exigentes.',
                'image_url' => 'https://ncrangola.vtexassets.com/arquivos/ids/173224/Smartphone-.jpg?v=638980379283170000',
                'additional_images' => [
                    'https://ncrangola.vtexassets.com/arquivos/ids/173225/Smartphone-1.jpg?v=638980379491200000',
                    'https://ncrangola.vtexassets.com/arquivos/ids/173226/Smartphone-2.jpg?v=638980379693400000'
                ]
            ],

            // --- PORTÁTEIS ---
            [
                'name' => 'Computador Portátil 15.6" I3-N305u FHD Uslim 8GB 256GB SSD W11H Prata',
                'categories' => ['Portáteis'],
                'brands' => ['HP'], // Assumindo HP pela nomenclatura comum ou genérico
                'price' => 350000.00,
                'short_description' => 'Portátil fino e leve com Intel i3, 8GB RAM e SSD.',
                'description' => 'Desempenho fiável num design ultra-fino. Este portátil de 15.6 polegadas Full HD vem equipado com processador Intel Core i3, 8GB de RAM e um SSD rápido de 256GB. Vem com Windows 11 Home pré-instalado.',
                'image_url' => 'https://ncrangola.vtexassets.com/arquivos/ids/173280/Computador-.jpg?v=638984719942800000',
                'additional_images' => [
                    'https://ncrangola.vtexassets.com/arquivos/ids/173281/Computador-1.jpg?v=638984720148070000',
                    'https://ncrangola.vtexassets.com/arquivos/ids/173283/Computador-3.jpg?v=638984720552770000',
                    'https://ncrangola.vtexassets.com/arquivos/ids/173282/Computador-2.jpg?v=638984720350570000'
                ]
            ],
            [
                'name' => 'Computador Portátil 16" PAV U5-125U 300Nits 16GB 1TB SSD W11H Prata',
                'categories' => ['Portáteis'],
                'brands' => ['HP'], // PAV refere-se a Pavilion
                'price' => 890000.00,
                'short_description' => 'Portátil de alto desempenho Pavilion, 16GB RAM, 1TB SSD.',
                'description' => 'Potência pura. Este portátil HP Pavilion de 16" oferece um ecrã brilhante de 300 nits, processador avançado U5, 16GB de RAM para multitarefa pesada e um armazenamento massivo de 1TB SSD.',
                'image_url' => 'https://ncrangola.vtexassets.com/arquivos/ids/169468/B1BA2EA-.jpg?v=638820567112600000',
                'additional_images' => [
                    'https://ncrangola.vtexassets.com/arquivos/ids/169469/B1BA2EA-1.jpg?v=638820567318930000',
                    'https://ncrangola.vtexassets.com/arquivos/ids/169470/B1BA2EA-2.jpg?v=638820567522500000',
                    'https://ncrangola.vtexassets.com/arquivos/ids/169471/B1BA2EA-3.jpg?v=638820567927170000'
                ]
            ],
            [
                'name' => 'Computador Portátil 460 16" U5-125U FHD 16GB 512GB PCIE W11PRO',
                'categories' => ['Portáteis'],
                'brands' => ['HP'], // Série ProBook 460
                'price' => 750000.00,
                'short_description' => 'Portátil Profissional 16", Windows 11 Pro.',
                'description' => 'Focado na produtividade empresarial, este modelo 460 vem com Windows 11 Pro, ecrã de 16 polegadas e hardware robusto (16GB RAM, 512GB SSD) para garantir segurança e eficiência no trabalho.',
                'image_url' => 'https://ncrangola.vtexassets.com/arquivos/ids/170485/A37ZWET-.jpg?v=638859203930970000',
                'additional_images' => [
                    'https://ncrangola.vtexassets.com/arquivos/ids/170486/A37ZWET-2.jpg?v=638859204440400000',
                    'https://ncrangola.vtexassets.com/arquivos/ids/170487/A37ZWET-3.jpg?v=638859206344970000'
                ]
            ],
            [
                'name' => 'Computador Portátil 250 15.6" I7-1355U FHD 8G 512 SSD W11P',
                'categories' => ['Portáteis'],
                'brands' => ['HP'], // Série 250
                'price' => 620000.00,
                'short_description' => 'HP 250 G9 com processador i7 e Windows 11 Pro.',
                'description' => 'O equilíbrio ideal entre custo e potência. Equipado com um poderoso processador Intel Core i7 de 13ª geração, este portátil está pronto para as aplicações mais exigentes do escritório.',
                'image_url' => 'https://ncrangola.vtexassets.com/arquivos/ids/168351/Computador-.jpg?v=638798816036830000',
                'additional_images' => [
                    'https://ncrangola.vtexassets.com/arquivos/ids/168352/Computador-1.jpg?v=638798816244930000',
                    'https://ncrangola.vtexassets.com/arquivos/ids/168353/Computador-2.jpg?v=638798816348000000',
                    'https://ncrangola.vtexassets.com/arquivos/ids/168354/Computador-3.jpg?v=638798816550670000'
                ]
            ],

            // --- DESKTOPS & AIO ---
            [
                'name' => 'Computador All-in-One 23.8" FullHD U5-125U 8G 512GB SSD W11H Branco',
                'categories' => ['Desktops & All-in-One'],
                'brands' => ['HP'],
                'price' => 680000.00,
                'short_description' => 'All-in-One elegante em branco, ecrã 23.8".',
                'description' => 'Economize espaço sem perder estilo. Este computador "Tudo em Um" na cor branca possui um ecrã vibrante de 23.8", perfeito para receções, escritórios modernos ou uso doméstico.',
                'image_url' => 'https://ncrangola.vtexassets.com/arquivos/ids/173208-1000-1000/C3AP2EA2.jpg?v=638980334295900000',
                'additional_images' => [
                    'https://ncrangola.vtexassets.com/arquivos/ids/173209/C3AP2EA.jpg?v=638980334296530000',
                    'https://ncrangola.vtexassets.com/arquivos/ids/173210/C3AP2EA1.jpg?v=638980334296530000'
                ]
            ],
            [
                'name' => 'Computador Desktop All-In-One 870 27" I7-13700 QHD 32GB/512GB SSD W11P Suporte Ajustável',
                'categories' => ['Desktops & All-in-One'],
                'brands' => ['HP'],
                'price' => 1450000.00,
                'short_description' => 'AIO Premium 27" QHD com 32GB RAM e i7.',
                'description' => 'Uma estação de trabalho completa. Ecrã QHD de 27 polegadas, processador i7 de alto desempenho e uns incríveis 32GB de RAM. Inclui suporte ajustável para máxima ergonomia.',
                'image_url' => 'https://ncrangola.vtexassets.com/arquivos/ids/168588-1000-1000/628J7ET-1.jpg?v=638803158808630000',
                'additional_images' => []
            ],
            [
                'name' => 'Computador Desktop All-in-one 27" FHD U7-155h Touch 300nits Omni X 32GB 1TB SSD W11H Prata',
                'categories' => ['Desktops & All-in-One'],
                'brands' => ['HP'],
                'price' => 1550000.00,
                'short_description' => 'AIO Omni X Touchscreen 27", 1TB SSD.',
                'description' => 'Interaja diretamente com o seu trabalho através do ecrã tátil de 27". O modelo Omni X combina design futurista, armazenamento massivo de 1TB e 32GB de RAM.',
                'image_url' => 'https://ncrangola.vtexassets.com/arquivos/ids/169576/Computador-.jpg?v=638823119284930000',
                'additional_images' => [
                    'https://ncrangola.vtexassets.com/arquivos/ids/169577/Computador-1.jpg?v=638823119692100000',
                    'https://ncrangola.vtexassets.com/arquivos/ids/169578/Computador-2.jpg?v=638823120294670000',
                    'https://ncrangola.vtexassets.com/arquivos/ids/169579/Computador-3.jpg?v=638823120399930000'
                ]
            ],
            [
                'name' => 'Computador Desktop 290 G9 MT I3-14100 8GB 512GB SSD W11P + Monitor 21.5" S3 Pro 332Pv',
                'categories' => ['Desktops & All-in-One'],
                'brands' => ['HP'],
                'price' => 420000.00,
                'promotional_price' => 399000.00,
                'short_description' => 'Bundle Torre HP 290 G9 + Monitor 21.5".',
                'description' => 'Solução completa para o seu escritório. Inclui a torre HP 290 G9 Microtower com i3 e SSD rápido, mais um monitor de 21.5 polegadas. Pronto a ligar e trabalhar.',
                'image_url' => 'https://ncrangola.vtexassets.com/arquivos/ids/172676/Computador-.jpg?v=638948228686830000',
                'additional_images' => [
                    'https://ncrangola.vtexassets.com/arquivos/ids/172677/Computador-1.jpg?v=638948228897070000',
                    'https://ncrangola.vtexassets.com/arquivos/ids/172678/Computador-2.jpg?v=638948229002730000',
                    'https://ncrangola.vtexassets.com/arquivos/ids/172679/Computador-3.jpg?v=638948229306170000'
                ]
            ],

            // --- COMPONENTES & PERIFÉRICOS ---
            [
                'name' => 'Memória RAM DDR2 512MB PC2-5300 667MHZ Sodimm',
                'categories' => ['Componentes'],
                'brands' => ['Genérico'],
                'price' => 3500.00,
                'short_description' => 'Módulo de memória RAM legado DDR2 512MB.',
                'description' => 'Memória específica para manutenção de sistemas mais antigos. Formato SODIMM para portáteis compatíveis com DDR2 667MHz.',
                'image_url' => 'https://ncrangola.vtexassets.com/arquivos/ids/163349-1000-1000/mod-port-512-ddr2-pc2-5300-667mhz%20-1-.jpg.jpg?v=638745236663530000',
                'additional_images' => []
            ],
            [
                'name' => 'Webcam 300K USB-A Built-in Microfone Xpresscam300',
                'categories' => ['Periféricos'],
                'brands' => ['Xpresscam'],
                'price' => 8000.00,
                'short_description' => 'Webcam básica USB com microfone integrado.',
                'description' => 'Solução simples e económica para videochamadas. Ligação USB-A plug-and-play e microfone incorporado para facilitar a comunicação.',
                'image_url' => 'https://ncrangola.vtexassets.com/arquivos/ids/167357/436001305790.jpg?v=638778052290700000',
                'additional_images' => [
                    'https://ncrangola.vtexassets.com/arquivos/ids/167358/436001305790-2.jpg?v=638778052593300000',
                    'https://ncrangola.vtexassets.com/arquivos/ids/167359/436001305790-3.jpg?v=638778053095530000'
                ]
            ],
            [
                'name' => 'Pen Drive 32GB Venture Usb 2.0',
                'categories' => ['Armazenamento'],
                'brands' => ['Venture'],
                'price' => 4500.00,
                'short_description' => 'Pen Drive 32GB USB 2.0 portátil.',
                'description' => 'Transporte os seus documentos importantes, músicas e fotos para todo o lado com esta Pen Drive de 32GB. Compacta e resistente.',
                'image_url' => 'https://ncrangola.vtexassets.com/arquivos/ids/170532-1000-1000/90881200.jpg?v=638860264092230000',
                'additional_images' => []
            ],
            [
                'name' => 'Cadeira Medium Executiva 2 Níveis Desmontada Preta',
                'categories' => ['Mobiliário de Escritório'],
                'brands' => ['Genérico'],
                'price' => 45000.00,
                'short_description' => 'Cadeira de escritório executiva ajustável preta.',
                'description' => 'Conforto para o seu dia de trabalho. Cadeira executiva com regulação de altura (2 níveis), rodas resistentes e acabamento em preto profissional. Fácil de montar.',
                'image_url' => 'https://ncrangola.vtexassets.com/arquivos/ids/169131-1000-1000/Cadeira-.jpg?v=638810170460770000',
                'additional_images' => [
                    'https://ncrangola.vtexassets.com/arquivos/ids/169134/Cadeira-1.jpg?v=638810170460930000',
                    'https://ncrangola.vtexassets.com/arquivos/ids/169137/Cadeira-2.jpg?v=638810170460930000'
                ]
            ],
        ];
        
        $createdProducts = [];

        // 5. Criar Produtos, Associar Categorias, Marcas e Imagens
        foreach ($productsData as $data) {
            $categoriesToAttach = $data['categories'];
            $brandsToAttach = $data['brands'] ?? []; 
            $additionalImages = $data['additional_images'];
            
            // Remove os dados auxiliares antes de criar o modelo
            unset($data['categories'], $data['brands'], $data['additional_images']);

            $product = Product::create(array_merge($data, [
                'slug' => Str::slug($data['name']),
                'sku' => 'TEC-' . rand(10000, 99999),
                'unit' => 'Unidade',
                // 'promotional_price' é incluído aqui se existir em $data
            ]));
            
            $createdProducts[] = $product;

            // Anexar Categorias
            $ids = collect($categoriesToAttach)->map(function ($categoryName) use ($categoryIds) {
                return $categoryIds[$categoryName] ?? null;
            })->filter()->all();
            $product->categories()->attach($ids);

            // Anexar Marcas (Se existirem)
            $brandIdsToAttach = collect($brandsToAttach)->map(function ($brandName) use ($brandIds) {
                return $brandIds[$brandName] ?? null;
            })->filter()->all();

            if (!empty($brandIdsToAttach)) {
                $product->brands()->attach($brandIdsToAttach);
            }

            // Adicionar Imagens Adicionais
            // A primeira imagem já está em image_url, adicionamos as restantes na tabela product_images
            $uniqueImages = array_unique($additionalImages);
            $sortOrder = 1;
            foreach ($uniqueImages as $imgUrl) {
                // Evitar duplicar a imagem principal na galeria, se por acaso estiver lá
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
                    "Excelente performance! Superou as expectativas.",
                    "Boa relação qualidade/preço.",
                    "Entrega rápida e produto bem embalado.",
                    "Funciona perfeitamente para o que preciso no escritório.",
                    "Design muito bonito e moderno."
                ],
                'pending' => [
                    "Ainda a configurar, mas parece bom.",
                ],
                'rejected' => [
                    "Não era compatível com o meu sistema.",
                ],
            ];

            foreach ($createdProducts as $product) {
                // Nem todos os produtos terão reviews
                if (rand(0, 1)) {
                    $numberOfReviews = rand(1, 3); 
                    for ($i = 0; $i < $numberOfReviews; $i++) {
                        $statusOptions = ['approved', 'approved', 'approved', 'pending'];
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
}