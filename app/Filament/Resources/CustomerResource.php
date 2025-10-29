<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
use App\Filament\Resources\CustomerResource\RelationManagers;
use App\Models\Customer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

// ✅ Imports Adicionados
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Section;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Forms\Components\DatePicker;
use Illuminate\Support\Facades\Hash;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Clientes';
    protected static ?int $navigationSort = 1;
    protected static ?string $modelLabel = 'Cliente';
    protected static ?string $pluralModelLabel = 'Clientes';
    protected static ?string $navigationGroup = 'Vendas & Clientes';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // ✅ Nova Estrutura de Formulário
                Grid::make(3)
                    ->schema([
                        // --- COLUNA PRINCIPAL ---
                        Group::make()
                            ->schema([
                                Section::make('Informações Pessoais')
                                    ->columns(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('name')
                                            ->label('Nome Completo')
                                            ->maxLength(255)
                                            ->required()
                                            ->columnSpanFull(),
                                        
                                        Forms\Components\TextInput::make('phone')
                                            ->label('WhatsApp/Telefone')
                                            ->prefix('+')
                                            ->unique(ignoreRecord: true)
                                            ->required(),
                                            
                                        Forms\Components\TextInput::make('email')
                                            ->label('E-mail')
                                            ->email()
                                            ->unique(ignoreRecord: true)
                                            ->nullable(),
                                    ]),
                                Section::make('Endereço')
                                    ->schema([
                                        Forms\Components\Textarea::make('delivery_address')
                                            ->label('Endereço de Entrega')
                                            ->rows(4)
                                            ->columnSpanFull()
                                            ->nullable(),
                                    ]),
                            ])
                            ->columnSpan(2),

                        // --- COLUNA LATERAL ---
                        Group::make()
                            ->schema([
                                Section::make('Segurança')
                                    ->schema([
                                        // ✅ Lógica da Password Corrigida
                                        Forms\Components\TextInput::make('password')
                                            ->label(fn (string $operation): string => $operation === 'create' ? 'Password' : 'Nova Password')
                                            ->password()
                                            ->helperText(fn (string $operation): string => $operation === 'edit' ? 'Deixe em branco para não alterar.' : '')
                                            ->dehydrateStateUsing(fn (string $state): string => Hash::make($state))
                                            ->dehydrated(fn (?string $state): bool => filled($state)) // Só faz o hash se for preenchido
                                            ->required(fn (string $operation): bool => $operation === 'create') // Obrigatório apenas ao criar
                                            ->columnSpanFull(),
                                    ])
                            ])
                            ->columnSpan(1),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('phone')
                    ->label('WhatsApp')
                    ->copyable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->default('N/A')
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('carts_count')
                    ->counts('carts')
                    ->label('Nº Pedidos')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('delivery_address')
                    ->label('Endereço') // Label mais curto
                    ->limit(40)
                    ->default('Não fornecido')
                    ->toggleable(isToggledHiddenByDefault: true) // Oculto por defeito
                    ->wrap(),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Registo')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false), // Visível por defeito
            ])
            ->filters([
                // ✅ Pedido 1: Mais Filtros
                Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Registado desde'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Registado até'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
                
                TernaryFilter::make('carts')
                    ->label('Pedidos')
                    ->boolean()
                    ->trueLabel('Apenas com pedidos')
                    ->falseLabel('Apenas sem pedidos')
                    ->queries(
                        true: fn (Builder $query) => $query->has('carts'),
                        false: fn (Builder $query) => $query->doesntHave('carts'),
                    )
                    ->native(false),
            ])
            ->actions([
                // ✅ Pedido 2: Ações como iconButton
                Tables\Actions\ViewAction::make()->iconButton(),
                Tables\Actions\EditAction::make()->iconButton(),
                Tables\Actions\DeleteAction::make()->iconButton(), // Adicionado Delete
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            // CartsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            // 'view' => Pages\ViewCustomer::route('/{record}'), // Página de View não está a ser usada
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }
    
    // Otimização
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withCount('carts');
    }
}