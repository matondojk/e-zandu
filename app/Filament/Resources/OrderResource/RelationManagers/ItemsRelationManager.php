<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Number;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items'; // Nome da relação no Model Cart

    protected static ?string $modelLabel = 'Item do Pedido';
    protected static ?string $pluralModelLabel = 'Itens do Pedido';


    // Desabilitar criação/edição direta dos itens aqui
    public function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            // Remover Cabeçalho e Ação de Criar
            ->headerActions([])
            ->recordTitleAttribute('product.name') // Usa o nome do produto como título
            ->columns([
                Tables\Columns\ImageColumn::make('product.image_url') // Acessa a imagem através da relação
                    ->label('Produto')
                    ->disk('public')
                    ->width(50)->height('auto')->circular(),

                Tables\Columns\TextColumn::make('product.name') // Acessa o nome através da relação
                    ->label('Nome')
                    ->url(fn ($record) => url('/admin/products/' . $record->product_id . '/edit')) // Link para editar o produto
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('quantity')
                    ->label('Qtd.')
                    ->alignCenter()
                    ->sortable(),

                Tables\Columns\TextColumn::make('price')
                    ->label('Preço Unit. (no Pedido)')
                    ->formatStateUsing(fn ($state): string => 'Kz ' . number_format($state, 0, ',', '.'))
                    ->alignEnd()
                    ->sortable(),

                Tables\Columns\TextColumn::make('subtotal')
                    ->label('Subtotal')
                     // Calcula o subtotal (preço * quantidade)
                    ->state(fn ($record): float => $record->price * $record->quantity)
                    ->formatStateUsing(fn ($state): string => 'Kz ' . number_format($state, 0, ',', '.'))
                    ->alignEnd(),
            ])
            ->filters([
                //
            ])
            ->actions([
                // (Não permitir editar/apagar itens de um pedido)
            ])
            ->bulkActions([
                 // (Manter vazio)
            ])
             ->paginated(false); // Mostra todos os itens sem paginação
    }
}
