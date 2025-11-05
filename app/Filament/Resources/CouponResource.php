<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CouponResource\Pages;
use App\Models\Coupon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
// ✅ Imports para o formulário reativo
use Filament\Forms\Get; 

class CouponResource extends Resource
{
    protected static ?string $model = Coupon::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';
    protected static ?string $modelLabel = 'Cupão';
    protected static ?string $pluralModelLabel = 'Cupões de Desconto';
    protected static ?string $navigationGroup = 'Loja & Vendas';
    protected static ?int $navigationSort = 4; // Colocar antes dos Clientes

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detalhes do Cupão')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('code')
                            ->label('Código do Cupão')
                            ->placeholder('Ex: NATAL10')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->columnSpanFull(),
                        
                        Forms\Components\Select::make('type')
                            ->label('Tipo de Desconto')
                            ->options([
                                'fixed' => 'Valor Fixo (Kz)',
                                'percentage' => 'Percentagem (%)',
                            ])
                            ->required()
                            ->reactive(), // ✅ Torna o formulário reativo
                        
                        Forms\Components\TextInput::make('value')
                            ->label('Valor')
                            ->numeric()
                            ->required()
                            // ✅ Mostra o prefixo ou sufixo com base no tipo
                            ->prefix(fn (Get $get) => $get('type') === 'fixed' ? 'Kz' : null)
                            ->suffix(fn (Get $get) => $get('type') === 'percentage' ? '%' : null),
                    ]),
                
                Forms\Components\Section::make('Regras e Limites')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('usage_limit')
                            ->label('Limite Total de Utilizações')
                            ->numeric()
                            ->nullable()
                            ->helperText('Deixe em branco para utilizações ilimitadas.'),
                        
                        Forms\Components\DateTimePicker::make('expires_at')
                            ->label('Data de Expiração')
                            ->nullable(),
                        
                        Forms\Components\Toggle::make('is_active')
                            ->label('Cupão Ativo')
                            ->default(true)
                            ->columnSpanFull(),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Código')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\BadgeColumn::make('type')
                    ->label('Tipo')
                    ->formatStateUsing(fn ($state) => $state === 'fixed' ? 'Fixo (Kz)' : 'Percentagem (%)')
                    ->colors([
                        'primary' => 'fixed',
                        'success' => 'percentage',
                    ]),

                Tables\Columns\TextColumn::make('value')
                    ->label('Valor')
                    ->formatStateUsing(function ($record) {
                        if ($record->type === 'fixed') {
                            return number_format($record->value, 0, ',', '.') . ' Kz';
                        }
                        return (int) $record->value . ' %';
                    })
                    ->alignEnd(),

                Tables\Columns\TextColumn::make('usage_count')
                    ->label('Utilizações')
                    ->formatStateUsing(fn ($record) => $record->usage_count . ($record->usage_limit ? " / {$record->usage_limit}" : ''))
                    ->alignCenter(),
                
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Ativo')
                    ->boolean(),
                
                Tables\Columns\TextColumn::make('expires_at')
                    ->label('Expira em')
                    ->dateTime('d/m/Y')
                    ->placeholder('Nunca')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCoupons::route('/'),
            'create' => Pages\CreateCoupon::route('/create'),
            'edit' => Pages\EditCoupon::route('/{record}/edit'),
        ];
    }    
}
