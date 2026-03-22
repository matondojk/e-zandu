<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use App\Models\Setting; // Certifique-se de que este Model existe ou remova se não usar
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Barryvdh\DomPDF\Facade\Pdf; // Certifique-se de ter o dompdf instalado

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?string $navigationLabel = 'Produtos';
    protected static ?string $modelLabel = 'Produto';
    protected static ?string $pluralModelLabel = 'Produtos';
    protected static ?string $navigationGroup = 'Catálogo';
    protected static ?int $navigationSort = 1;
    protected static ?string $slug = 'produtos';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form
            ->columns(3)
            ->schema([

                // === COLUNA DA ESQUERDA (2/3 da largura) ===
                Group::make()
                    ->columnSpan(2)
                    ->schema([

                        // 1. INFORMAÇÕES BÁSICAS
                        Section::make('Informações Essenciais')
                            ->columns(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nome do Produto')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpanFull(),

                                Textarea::make('short_description')
                                    ->label('Descrição Curta (WhatsApp)')
                                    ->rows(2)
                                    ->helperText('Máximo de 150 caracteres.')
                                    ->maxLength(150)
                                    ->required()
                                    ->columnSpanFull(),

                                Textarea::make('description')
                                    ->label('Descrição Detalhada')
                                    ->required()
                                    ->rows(4)
                                    ->columnSpanFull(),

                                Select::make('categories')
                                    ->label('Categorias')
                                    ->relationship('categories', 'name')
                                    ->multiple()
                                    ->preload()
                                    ->searchable()
                                    ->required()
                                    ->columnSpanFull(),

                                Select::make('brands')
                                    ->label('Marcas')
                                    ->relationship('brands', 'name')
                                    ->multiple()
                                    ->preload()
                                    ->searchable()
                                    ->columnSpanFull(),
                            ]),

                        // 2. IMAGEM PRINCIPAL
                        Section::make('Imagem Principal (Destaque)')
                            ->schema([
                                // O Filament salva automaticamente o caminho relativo (ex: products/main/foto.jpg)
                                FileUpload::make('image_url')
                                    ->label('Imagem de Capa')
                                    ->image()
                                    ->directory('products/main')
                                    ->disk('public')
                                    ->required()
                                    ->columnSpanFull(),
                            ]),

                        // 3. GALERIA DE IMAGENS (Upload em Massa)
                        // SECÇÃO 3: GALERIA DE IMAGENS
                        // SECÇÃO 3: GALERIA DE IMAGENS
                        Section::make('Galeria de Imagens Secundárias')
                            ->description('Arraste várias imagens para o campo tracejado. Elas serão copiadas para a lista abaixo.')
                            ->schema([
                                // 1. CAMPO VIRTUAL (Upload em Massa)
                                Forms\Components\FileUpload::make('bulk_gallery_upload')
                                    ->label('Upload Rápido (Múltiplos Ficheiros)')
                                    ->helperText('As imagens aparecerão na lista abaixo.')
                                    ->multiple()
                                    ->image()
                                    ->directory('products/gallery')
                                    ->disk('public')
                                    ->dehydrated(false) // Não salva na BD, apenas transporta
                                    ->live()
                                    ->afterStateUpdated(function ($state, $get, $set) {
                                        // Se estiver vazio, para a execução
                                        if (empty($state)) return;

                                        // ✅ A SOLUÇÃO DO ERRO ESTÁ AQUI:
                                        // Forçamos a variável a ser um array. 
                                        // Se vier uma string (1 arquivo), vira ['arquivo']. Se vier array, mantém.
                                        $files = is_array($state) ? $state : [$state];

                                        // Pega os itens atuais do repeater
                                        $currentItems = $get('images') ?? [];

                                        // Agora fazemos o loop na variável $files (que garantimos ser array)
                                        foreach ($files as $path) {

                                            // Tratamento extra para objetos temporários do Livewire
                                            if ($path instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile) {
                                                $path = $path->getFilename();
                                            }

                                            // Se por acaso não for string, ignora para não quebrar
                                            if (!is_string($path)) continue;

                                            $uuid = (string) Str::uuid();

                                            $currentItems[$uuid] = [
                                                'url' => $path,
                                                'sort_order' => count($currentItems),
                                            ];
                                        }

                                        $set('images', $currentItems);

                                        // NOTA: Removemos o $set('bulk_gallery_upload', []) para evitar conflitos internos
                                        // do componente enquanto ele ainda está processando o upload.
                                    })
                                    ->columnSpanFull(),

                                // 2. REPEATER (Lista Final)
                                Repeater::make('images')
                                    ->label('Fotos Selecionadas')
                                    ->relationship()
                                    ->schema([
                                        Forms\Components\FileUpload::make('url')
                                            ->hiddenLabel()
                                            ->image()
                                            ->directory('products/gallery')
                                            ->disk('public')
                                            ->required()
                                            ->columnSpanFull(),

                                        Forms\Components\TextInput::make('sort_order')
                                            ->numeric()
                                            ->default(0)
                                            ->hidden(),
                                    ])
                                    ->grid(3)
                                    ->reorderable('sort_order')
                                    ->collapsible(false)
                                    ->addActionLabel('Adicionar Manualmente'),
                            ]),
                    ]),

                // === COLUNA DA DIREITA (1/3 da largura) ===
                Group::make()
                    ->columnSpan(1)
                    ->schema([

                        // 4. PREÇO E DADOS
                        Section::make('Preço e Identificação')
                            ->schema([
                                TextInput::make('price')
                                    ->label('Preço (Kz)')
                                    ->required()
                                    ->numeric()
                                    ->prefix('Kz'),

                                TextInput::make('promotional_price')
                                    ->label('Preço Promo (Opcional)')
                                    ->numeric()
                                    ->prefix('Kz')
                                    ->nullable(),

                                TextInput::make('sku')
                                    ->label('SKU')
                                    ->placeholder('PROD-001')
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(50),

                                Select::make('unit')
                                    ->label('Unidade')
                                    ->options([
                                        'Unidade' => 'Unidade',
                                        'Caixa' => 'Caixa',
                                        'Metro' => 'Metro',
                                        'Kg' => 'Quilograma',
                                        'Litro' => 'Litro',
                                        'Pacote' => 'Pacote',
                                    ])
                                    ->default('Unidade')
                                    ->required()
                                    ->searchable(),
                            ]),

                        // 5. VARIAÇÕES
                        Section::make('Variações')
                            ->schema([
                                TagsInput::make('colors')->label('Cores')->placeholder('Ex: Azul'),
                                TagsInput::make('sizes')->label('Tamanhos')->placeholder('Ex: XL'),
                            ]),

                        // 6. LOGÍSTICA
                        Section::make('Logística')
                            ->schema([
                                TextInput::make('drawing')->label('Desenho/Projeto'),
                                TextInput::make('weight')->label('Peso (kg)')->numeric(),
                                TextInput::make('volume')->label('Volume'),
                                TextInput::make('dimension')->label('Dimensões'),
                            ]),

                        // 7. CROSS-SELL
                        Section::make('Recomendações')
                            ->collapsible()
                            ->schema([
                                Select::make('crossSellProducts')
                                    ->label('Produtos Sugeridos')
                                    ->relationship('crossSellProducts', 'name')
                                    ->getOptionLabelFromRecordUsing(fn(Model $record) => "{$record->name} (" . number_format($record->price, 0, ',', '.') . " Kz)")
                                    ->multiple()
                                    ->preload(),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image_url')
                    ->label('Imagem')
                    ->width(60)
                    ->height(60)
                    ->circular(),

                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->limit(30),

                TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('current_stock')
                    ->label('Estoque')
                    ->state(fn(Product $record): float => (float) $record->movements()->sum('quantity'))
                    ->formatStateUsing(fn($state, Product $record) => number_format($state, 0, ',', '.') . ' ' . $record->unit)
                    ->alignEnd(),

                TextColumn::make('categories.name')
                    ->label('Categorias')
                    ->badge()
                    ->limitList(2),

                TextColumn::make('price')
                    ->label('Preço')
                    ->formatStateUsing(fn($state) => number_format($state, 2, ',', '.') . ' Kz')
                    ->alignEnd()
                    ->sortable(),

                TextColumn::make('promotional_price')
                    ->label('Promo')
                    ->formatStateUsing(fn($state) => $state > 0 ? number_format($state, 2, ',', '.') . ' Kz' : '—')
                    ->color('success')
                    ->alignEnd()
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label('Atualizado')
                    ->dateTime('d/m/Y')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
                // AÇÃO: BAIXAR LISTA EM PDF
                Tables\Actions\Action::make('export_pdf_list')
                    ->label('Catálogo (PDF)')
                    ->icon('heroicon-o-document-arrow-down')
                    ->action(function ($livewire) {
                        $records = $livewire->getFilteredTableQuery()->get();
                        $settings = Setting::first(); // Ajuste conforme seu Model de settings

                        $pdf = Pdf::loadView('pdfs.products-list', [
                            'records' => $records,
                            'settings' => $settings
                        ])->setPaper('a4', 'landscape');

                        // CORREÇÃO: Usar function() use ($pdf) {} em vez de fn() => echo
                        return response()->streamDownload(function () use ($pdf) {
                            echo $pdf->output();
                        }, 'catalogo-' . date('Y-m-d') . '.pdf');
                    }),
            ])
            ->filters([
                SelectFilter::make('categories')->relationship('categories', 'name')->multiple()->preload(),
                SelectFilter::make('brands')->relationship('brands', 'name')->multiple()->preload(),
                Tables\Filters\Filter::make('price_range')
                    ->form([
                        TextInput::make('min_price')->numeric()->label('Mín'),
                        TextInput::make('max_price')->numeric()->label('Máx'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when($data['min_price'], fn($q, $p) => $q->where('price', '>=', $p))
                            ->when($data['max_price'], fn($q, $p) => $q->where('price', '<=', $p));
                    }),
                Tables\Filters\TernaryFilter::make('on_sale')
                    ->label('Promoção')
                    ->queries(
                        true: fn($q) => $q->where('promotional_price', '>', 0),
                        false: fn($q) => $q->where(fn($q2) => $q2->whereNull('promotional_price')->orWhere('promotional_price', 0))
                    ),
            ])
            ->actions([
                // AÇÃO: BAIXAR DETALHES EM PDF
                Tables\Actions\Action::make('pdf_details')
                    ->icon('heroicon-o-document-text')
                    ->label('Ficha')
                    ->color('gray')
                    ->action(function (Product $record) {
                        $record->load(['categories', 'brands', 'movements']);
                        $settings = Setting::first();

                        $pdf = Pdf::loadView('pdfs.product-details', [
                            'record' => $record,
                            'settings' => $settings
                        ]);

                        // CORREÇÃO: Usar function() use ($pdf) {}
                        return response()->streamDownload(function () use ($pdf) {
                            echo $pdf->output();
                        }, "ficha-{$record->sku}.pdf");
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['categories', 'brands', 'movements', 'images']);
    }
}
