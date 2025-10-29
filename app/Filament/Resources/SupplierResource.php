<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SupplierResource\Pages;
use App\Filament\Resources\SupplierResource\RelationManagers;
use App\Models\Supplier;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

// ✅ Imports Adicionados para o formulário e filtros
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Section;
use Filament\Tables\Filters\Filter; // Para o filtro de data
use Forms\Components\DatePicker; // Para o filtro de data

class SupplierResource extends Resource
{
    protected static ?string $model = Supplier::class;

    // ✅ Pedido 5: Ícone alterado
    protected static ?string $navigationIcon = 'heroicon-o-truck'; // MUDADO (era building-office-2)
    
    protected static ?string $navigationLabel = 'Fornecedores';
    protected static ?string $modelLabel = 'Fornecedor';
    protected static ?string $pluralModelLabel = 'Fornecedores';
    protected static ?string $navigationGroup = 'Catálogo & Inventário';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // ✅ Pedido 4: Nova estrutura do formulário
                Grid::make(3) // Layout de 3 colunas (2 + 1)
                    ->schema([
                        // --- COLUNA PRINCIPAL (2/3 da largura) ---
                        Group::make()
                            ->schema([
                                Section::make('Informações Principais')
                                    ->columns(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('name')
                                            ->label('Nome da Empresa')
                                            ->required()
                                            ->unique(ignoreRecord: true)
                                            ->maxLength(255)
                                            ->columnSpanFull(),
                                        
                                        Forms\Components\TextInput::make('nif')
                                            ->label('NIF/Nº Contribuinte')
                                            ->maxLength(50),
                                            
                                        Forms\Components\TextInput::make('contact_name')
                                            ->label('Pessoa de Contacto')
                                            ->maxLength(255),
                                    ]),
                                
                                Section::make('Contactos')
                                    ->columns(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('phone')
                                            ->label('Telefone/WhatsApp')
                                            ->tel()
                                            ->maxLength(50),

                                        Forms\Components\TextInput::make('email')
                                            ->label('E-mail')
                                            ->email()
                                            ->maxLength(255),
                                    ]),
                            ])
                            ->columnSpan(2), // Ocupa 2 colunas

                        // --- COLUNA LATERAL (1/3 da largura) ---
                        Group::make()
                            ->schema([
                                Section::make('Endereço')
                                    ->schema([
                                        Forms\Components\Textarea::make('address')
                                            ->label('Endereço Completo')
                                            ->rows(10) // Mais altura
                                            ->maxLength(500)
                                            ->columnSpanFull(),
                                    ]),
                            ])
                            ->columnSpan(1), // Ocupa 1 coluna
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome da Empresa')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('contact_name')
                    ->label('Pessoa de Contacto')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false), // Visível por defeito
                    
                Tables\Columns\TextColumn::make('phone')
                    ->label('Telefone')
                    ->searchable()
                    ->copyable() // Clicável para copiar
                    ->copyMessage('Telefone copiado'),
                
                // ✅ Pedido 1: Nova coluna de E-mail
                Tables\Columns\TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable()
                    ->copyable() // Clicável para copiar
                    ->copyMessage('E-mail copiado')
                    ->toggleable(isToggledHiddenByDefault: true), // Oculto por defeito

                Tables\Columns\TextColumn::make('nif')
                    ->label('NIF')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false), // Visível por defeito

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Data de Registo')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false), // Visível por defeito
            ])
            ->filters([
                // ✅ Pedido 2: Novo filtro de data
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
            ])
            ->actions([
                // Ações como ícones para um look mais limpo
                Tables\Actions\EditAction::make()->iconButton(),
                Tables\Actions\DeleteAction::make()->iconButton(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            // ✅ Pedido 3: Ordenar pelo mais recente
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListSuppliers::route('/'),
            'create' => Pages\CreateSupplier::route('/create'),
            'edit' => Pages\EditSupplier::route('/{record}/edit'),
        ];
    }
}
