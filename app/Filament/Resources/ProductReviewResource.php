<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductReviewResource\Pages;
use App\Models\ProductReview;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Section;
use Illuminate\Support\HtmlString;

class ProductReviewResource extends Resource
{
    protected static ?string $model = ProductReview::class;

    // --- Configuração da Navegação ---
    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';
    protected static ?string $navigationLabel = 'Avaliações & Moderação';
    protected static ?string $navigationGroup = 'Loja & Vendas';
    protected static ?string $modelLabel = 'Avaliação';
    protected static ?string $pluralModelLabel = 'Avaliações';
    protected static ?int $navigationSort = 4;
    protected static ?string $slug = 'avaliacoes'; // URL em português


    /**
     * ✅ Contador de Reviews Pendentes (Badge)
     */
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'pending')->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return static::getModel()::where('status', 'pending')->count() > 0 ? 'warning' : 'success';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Detalhes da Review')
                    ->columns(2)
                    ->schema([
                        // Campos de Relação (Apenas leitura na Edição)
                        Forms\Components\Select::make('product_id')
                            ->relationship('product', 'name')
                            ->label('Produto')
                            ->searchable()
                            ->disabledOn('edit')
                            ->required(),

                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->label('Utilizador')
                            ->searchable()
                            ->disabledOn('edit')
                            ->required(),

                        // Rating
                        Forms\Components\Select::make('rating')
                            ->label('Rating (1-5)')
                            ->options([1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5])
                            ->required()
                            ->disabledOn('edit'),

                        // Status (Para Moderação)
                        Forms\Components\Select::make('status')
                            ->label('Status da Moderação')
                            ->options([
                                'pending' => 'Pendente',
                                'approved' => 'Aprovado',
                                'rejected' => 'Rejeitado',
                            ])
                            ->default('pending')
                            ->required()
                            ->in(['pending', 'approved', 'rejected'])
                            ->columnSpanFull(),

                        // Comentário
                        Forms\Components\Textarea::make('comment')
                            ->label('Comentário')
                            ->rows(3)
                            ->nullable()
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Produto')
                    ->searchable()
                    ->sortable(),
                // Removido o url() para evitar dependência do ProductResource

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Utilizador')
                    ->searchable()
                    ->sortable(),

                // ✅ COLUNA DE RATING CORRIGIDA (Usando formatStateUsing para a repetição)
                Tables\Columns\IconColumn::make('rating')
                    ->label('Rating')
                    ->size(Tables\Columns\IconColumn\IconColumnSize::Medium)
                    ->color('warning')
                    ->icons(function (string $state): array {
                        // Devolve um array de N ícones para que o Filament os exiba em sequência
                        $count = (int) $state;
                        // O array_fill é a forma mais limpa de criar N repetições de um valor
                        return array_fill(0, $count, 'heroicon-s-star');
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'approved' => 'success',
                        'rejected' => 'danger',
                        'pending' => 'warning',
                        default => 'gray',
                    })
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('comment')
                    ->label('Comentário')
                    ->limit(50)
                    ->tooltip(fn($record) => $record->comment)
                    ->wrap(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Data')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pendentes',
                        'approved' => 'Aprovadas',
                        'rejected' => 'Rejeitadas',
                    ])
                    ->label('Filtrar por Status')
                    ->default('pending')
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('approve')
                        ->label('Aprovar Review')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(fn(ProductReview $record): bool => $record->status !== 'approved')
                        ->requiresConfirmation()
                        ->action(fn(ProductReview $record) => $record->update(['status' => 'approved'])),

                    Tables\Actions\Action::make('reject')
                        ->label('Rejeitar Review')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->visible(fn(ProductReview $record): bool => $record->status !== 'rejected')
                        ->requiresConfirmation()
                        ->action(fn(ProductReview $record) => $record->update(['status' => 'rejected'])),

                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('approve_selected')
                        ->label('Aprovar Selecionadas')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn(\Illuminate\Support\Collection $records) => $records->each->update(['status' => 'approved']))
                        ->deselectRecordsAfterCompletion(),

                    Tables\Actions\BulkAction::make('reject_selected')
                        ->label('Rejeitar Selecionadas')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(fn(\Illuminate\Support\Collection $records) => $records->each->update(['status' => 'rejected']))
                        ->deselectRecordsAfterCompletion(),

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
            'index' => Pages\ListProductReviews::route('/'),
            'create' => Pages\CreateProductReview::route('/create'),
            'edit' => Pages\EditProductReview::route('/{record}/edit'),
        ];
    }
}
