<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\HtmlString;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Group; 
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\FileUpload; 
use Illuminate\Database\Eloquent\Model;
use App\Models\Category;
use App\Models\ProductImage; 
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter; 
use Filament\Tables\Filters\Indicator; 
use Illuminate\Support\Facades\Log; 
use Illuminate\Support\Facades\DB; 
use Illuminate\Support\Facades\Session; 
use Filament\Forms\Components\Repeater; 
use Filament\Forms\Components\TextInput; 

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?string $navigationLabel = 'Produtos';
    protected static ?string $modelLabel = 'Produto';
    protected static ?string $pluralModelLabel = 'Produtos';
    protected static ?string $navigationGroup = 'Catálogo & Inventário';
    
    protected static ?string $recordTitleAttribute = 'name';
    
    public static function form(Form $form): Form
    {
        return $form
            ->columns(3)
            ->schema([
                
                // === COLUNA DA ESQUERDA (Mais Larga) ===
                Group::make()
                    ->columnSpan(2)
                    ->schema([
                        
                        // SECÇÃO 1: INFORMAÇÕES ESSENCIAIS
                        Section::make('Informações Essenciais')
                            ->columns(2)
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nome do Produto')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpanFull(),

                                Forms\Components\Textarea::make('short_description')
                                    ->label('Descrição Curta (WhatsApp)')
                                    ->rows(2)
                                    ->helperText('Máximo de 150 caracteres.')
                                    ->maxLength(150)
                                    ->required()
                                    ->columnSpanFull(),

                                Forms\Components\Textarea::make('description')
                                    ->label('Descrição Detalhada')
                                    ->required()
                                    ->rows(4)
                                    ->columnSpanFull(),

                                Forms\Components\Select::make('categories')
                                    ->label('Categorias')
                                    ->relationship('categories', 'name')
                                    ->multiple()
                                    ->preload()
                                    ->searchable()
                                    ->required()
                                    ->columnSpanFull(),
                            ]),

                        // SECÇÃO 2: IMAGEM PRINCIPAL (Mantida)
                        Section::make('Imagem Principal (Destaque)')
                            ->schema([
                                Forms\Components\Group::make()
                                    ->columns(2)
                                    ->schema([
                                        Forms\Components\FileUpload::make('image_upload_create')
                                            ->label('Fazer Upload da Imagem Principal')
                                            ->image()->directory('products/main')->disk('public') 
                                            ->required(fn($livewire) => $livewire instanceof Pages\CreateProduct)
                                            ->afterStateUpdated(function ($state, callable $set) {
                                                if ($state instanceof UploadedFile) {
                                                    $path = $state->store('products/main', 'public');
                                                    $set('image_url', Storage::disk('public')->url($path)); 
                                                }
                                            })
                                            ->visible(fn($livewire) => $livewire instanceof Pages\CreateProduct),
                                            
                                        Forms\Components\Hidden::make('image_url'), 
                                        
                                        Forms\Components\Placeholder::make('current_image')
                                            ->label('Imagem Atual')
                                            ->content(function ($record): ?HtmlString {
                                                $url = $record?->image_url;
                                                return $url && filter_var($url, FILTER_VALIDATE_URL)
                                                    ? new HtmlString('<img src="' . $url . '" style="max-width: 150px; height: auto; border-radius: 5px;">')
                                                    : new HtmlString('Nenhuma imagem carregada.');
                                            })
                                            ->visible(fn($livewire) => $livewire instanceof Pages\EditProduct),
                                            
                                        Forms\Components\FileUpload::make('image_upload_edit')
                                            ->label('Substituir Imagem Principal')
                                            ->image()->directory('products/main')->disk('public')
                                            ->helperText('Faça upload apenas se desejar substituir a imagem atual.')
                                            ->afterStateUpdated(function ($state, callable $set, $record) {
                                                if ($state instanceof UploadedFile) {
                                                    if ($record?->image_url) {
                                                        $diskPath = Str::after(parse_url($record->image_url, PHP_URL_PATH), '/storage/');
                                                        if (Storage::disk('public')->exists($diskPath)) {
                                                            Storage::disk('public')->delete($diskPath);
                                                        }
                                                    }
                                                    $path = $state->store('products/main', 'public');
                                                    $set('image_url', Storage::disk('public')->url($path));
                                                }
                                            })
                                            ->visible(fn($livewire) => $livewire instanceof Pages\EditProduct),
                                    ])->columnSpanFull(),
                            ]),
                            
                        // ✅ SECÇÃO 3: GALERIA DE IMAGENS (USANDO REPEATER + BULK UPLOAD)
                        Section::make('Galeria de Imagens Secundárias')
                            ->description('Use o campo "Upload em Massa" para selecionar várias imagens de uma só vez. Use a grelha abaixo para reordenar/apagar.')
                            ->schema([
                                // 1. CAMPO AUXILIAR DE UPLOAD EM MASSA (Não persistido)
                                Forms\Components\FileUpload::make('bulk_gallery_upload')
                                    ->label('Upload em Massa (Selecione Vários Ficheiros)')
                                    ->multiple()->image()->directory('products/gallery')->disk('public')
                                    ->helperText('Os ficheiros serão adicionados automaticamente à galeria abaixo.')
                                    ->dehydrated(false) // NUNCA SALVAR NO MODEL PRINCIPAL
                                    ->afterStateUpdated(function (?array $state, Forms\Get $get, Forms\Set $set) {
                                        if (empty($state)) return;

                                        $currentRepeaterItems = $get('images') ?? [];
                                        
                                        // Processa cada caminho do upload em massa
                                        foreach ($state as $path) {
                                            if (is_string($path) && !Str::contains($path, ['/tmp/'])) {
                                                // Cria um novo item para o Repeater
                                                $currentRepeaterItems[] = [
                                                    // O FileUpload retorna o caminho relativo do disco (ex: products/gallery/hash.jpg)
                                                    'url' => $path, 
                                                    'sort_order' => 0,
                                                ];
                                            }
                                        }

                                        // Atualiza o estado do Repeater
                                        $set('images', $currentRepeaterItems);
                                        // Limpa o campo de upload em massa
                                        $set('bulk_gallery_upload', null); 
                                    })
                                    ->columnSpanFull(),
                                    
                                // 2. REPEATER PARA GESTÃO DA RELAÇÃO
                                Repeater::make('images') // ✅ Mapeia diretamente para a relação 'images'
                                    ->label('Itens da Galeria')
                                    ->relationship() 
                                    ->schema([
                                        Forms\Components\FileUpload::make('url') 
                                            ->label('Visualização da Imagem')
                                            // ✅ Deixe o FileUpload fazer seu trabalho natural: salvar e ler o caminho relativo.
                                            // O Observer se encarregará de transformar em URL completo na BD.
                                            ->image()->directory('products/gallery')->disk('public')
                                            ->required()
                                            ->disableLabel()
                                            ->columnSpan(1),
                                            
                                        TextInput::make('sort_order')
                                            ->label('Ordem')
                                            ->numeric()
                                            ->default(0)
                                            ->hidden(), // Ocultar, pois a reordenação é visual

                                    ])
                                    // ❌ REMOVEMOS TODOS OS MUTATORS AQUI (mutateRelationshipDataBeforeFill, dehydrateStateUsing, etc.)
                                    // Confiamos no Eloquent Observer (Passo 1) para a conversão de URL.
                                    ->defaultItems(0)
                                    ->minItems(0)
                                    ->reorderable('sort_order') // Usa o campo sort_order para a reordenação
                                    ->addActionLabel('Adicionar 1 Imagem (Manual)'),
                            ]),
                    ]),
                
                // === COLUNA DA DIREITA (Mais Estreita) ===
                Group::make()
                    ->columnSpan(1) 
                    ->schema([
                        
                        // SECÇÃO 4: PREÇO E IDENTIFICAÇÃO
                        Section::make('Preço e Identificação')
                            ->schema([
                                Forms\Components\TextInput::make('price')
                                    ->label('Preço de Venda (Normal)')
                                    ->required()->numeric()->prefix('Kz')->inputMode('decimal'),

                                Forms\Components\TextInput::make('promotional_price')
                                    ->label('Preço Promocional (Opcional)')
                                    ->numeric()->prefix('Kz')->inputMode('decimal')
                                    ->nullable()
                                    ->helperText('Deixe em branco ou 0 se não estiver em promoção.'),

                                Forms\Components\TextInput::make('sku')
                                    ->label('SKU (Código de Produto)')
                                    ->placeholder('Ex: PROD-001')
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(50),

                                Forms\Components\Select::make('unit')
                                    ->label('Unidade de Venda')
                                    ->options([
                                        'Unidade' => 'Unidade', 'Caixa' => 'Caixa', 'Metro' => 'Metro',
                                        'Kg' => 'Quilograma (Kg)', 'Litro' => 'Litro (L)', 'Pacote' => 'Pacote',
                                    ])
                                    ->default('Unidade')->required()->searchable(),
                            ]),

                        // SECÇÃO 5: VARIAÇÕES (Mantida)
                        Section::make('Variações')
                            ->schema([
                                TagsInput::make('colors')->label('Cores Disponíveis')->placeholder('Ex: Vermelho...'),
                                TagsInput::make('sizes')->label('Tamanhos Disponíveis')->placeholder('Ex: S, M, L...'),
                            ]),
                            
                        // SECÇÃO 6: LOGÍSTICA (Mantida)
                        Section::make('Logística e Atributos Físicos')
                            ->schema([
                                Forms\Components\TextInput::make('drawing')->label('Desenho/Código de Projeto')->maxLength(100),
                                Forms\Components\TextInput::make('weight')->label('Peso (kg)')->numeric()->inputMode('decimal'),
                                Forms\Components\TextInput::make('volume')->label('Volume (m³ ou similar)')->maxLength(50),
                                Forms\Components\TextInput::make('dimension')->label('Dimensões (LxAxP)')->placeholder('Ex: 10x20x30 cm')->maxLength(100),
                            ]),
                            
                        // SECÇÃO 7: RECOMENDAÇÕES (Mantida)
                        Section::make('Recomendações (Cross-Sell)')
                            ->collapsible()
                            ->schema([
                                Select::make('crossSellProducts')
                                    ->label('Produtos Sugeridos')
                                    ->relationship('crossSellProducts', 'name')
                                    ->getOptionLabelFromRecordUsing(fn(Model $record) => "{$record->name} (" . number_format($record->price, 0, ',', '.') . " Kz)")
                                    ->multiple()->preload()->searchable(),
                            ]),
                    ]),
            ]);
    }

    // --- HOOKS DO RESOURCE AGORA VAZIOS/REMOVIDOS ---
    public static function mutateFormDataBeforeCreate(array $data): array { return $data; }
    public static function mutateFormDataBeforeSave(array $data, ?Model $record = null): array { return $data; }
    public static function afterCreate(Model $record): void {}
    public static function afterSave(Model $record): void {}
    
    // --- FIM DOS HOOKS ---
    
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_url')
                    ->label('Imagem')
                    ->width(80)->height('auto')->circular(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()->sortable(),
                    
                TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable()->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                
                TextColumn::make('current_stock')
                    ->label('Estoque Atual')
                    // ->state(fn (Product $record): int => $record->movements()->sum('quantity')) // Ajuste para decimal
                    ->state(fn (Product $record): float => (float) $record->movements()->sum('quantity')) // Usa float
                    ->formatStateUsing(fn ($state, Product $record) => number_format($state, 0, ',', '.') . ' ' . $record->unit)
                    ->alignEnd()
                    ->sortable(false),

                Tables\Columns\TextColumn::make('categories.name')
                    ->label('Categorias')
                    ->badge()->wrap(),

                Tables\Columns\TextColumn::make('price')
                    ->label('Preço Normal')
                    ->formatStateUsing(fn($state): string => number_format($state, 2, ',', '.') . ' Kz')
                    ->alignEnd()->sortable(),

                Tables\Columns\TextColumn::make('promotional_price')
                    ->label('Promoção')
                    ->formatStateUsing(function ($state) {
                        if ($state > 0) {
                            return number_format($state, 2, ',', '.') . ' Kz';
                        }
                        return '—';
                    })
                    ->color('success')->weight('bold')
                    ->alignEnd()->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Atualizado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('categories')
                    ->relationship('categories', 'name')
                    ->multiple()->preload()
                    ->label('Filtrar por Categoria'),

                Tables\Filters\Filter::make('price_range')
                    ->form([
                        Forms\Components\TextInput::make('min_price')->numeric()->label('Preço Mínimo (Kz)'),
                        Forms\Components\TextInput::make('max_price')->numeric()->label('Preço Máximo (Kz)'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['min_price'], fn(Builder $query, $price): Builder => $query->where('price', '>=', $price))
                            ->when($data['max_price'], fn(Builder $query, $price): Builder => $query->where('price', '<=', $price));
                    })
                    ->label('Filtrar por Preço'),
                
                // ✅ --- INÍCIO DA CORREÇÃO ---
                // O erro `Argument #2 ($label) must be of type string, null given`
                // acontece se `pluck('unit', 'unit')` encontrar um valor NULL.
                SelectFilter::make('unit')
                    ->label('Filtrar por Unidade')
                    ->options(function () {
                        // Usamos uma função para buscar os dados e formatá-los.
                        return Product::query()
                            ->select('unit')
                            ->distinct()
                            ->get()
                            ->mapWithKeys(function ($product) {
                                $unitValue = $product->unit;
                                // Se unit for NULL, usamos o rótulo 'Sem Unidade'.
                                $unitLabel = $product->unit ?? 'Sem Unidade'; 
                                
                                // O valor (key) pode ser null, mas o rótulo (value) DEVE ser um string.
                                return [$unitValue => $unitLabel];
                            });
                    })
                    ->multiple(),
                // ✅ --- FIM DA CORREÇÃO ---
                
                Tables\Filters\TernaryFilter::make('on_sale')
                    ->label('Em Promoção')
                    ->boolean()->trueLabel('Sim')->falseLabel('Não')->native(false)
                    ->queries(
                        true: fn (Builder $query) => $query->where('promotional_price', '>', 0),
                        false: fn (Builder $query) => $query->where(fn ($q) => $q->whereNull('promotional_price')->orWhere('promotional_price', 0)),
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->iconButton(),
                Tables\Actions\EditAction::make()->iconButton(),
                Tables\Actions\DeleteAction::make()->iconButton(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
    
    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
            'view' => Pages\ViewProduct::route('/{record}'), 
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['categories', 'movements', 'images']); 
    }
}

