<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentMethodResource\Pages;
use App\Models\PaymentMethod;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Get; // ✅ Importar para formulários reativos

class PaymentMethodResource extends Resource
{
    protected static ?string $model = PaymentMethod::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $modelLabel = 'Método de Pagamento';
    protected static ?string $pluralModelLabel = 'Métodos de Pagamento';
    protected static ?string $navigationGroup = 'Configurações';
    protected static ?int $navigationSort = 2;
    protected static ?string $slug = 'metodos-de-pagamento'; // URL em português

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detalhes do Método')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nome do Método')
                            ->placeholder('Ex: Transferência BAI')
                            ->required(),

                        Forms\Components\Select::make('type')
                            ->label('Tipo de Método')
                            ->options([
                                'bank_transfer' => 'Transferência Bancária',
                                'express' => 'Pagamento Express (Tpa)',
                                'cash_on_delivery' => 'Pagamento na Entrega (Dinheiro)',
                            ])
                            ->required()
                            ->reactive(), // ✅ Torna o formulário reativo

                        Forms\Components\Toggle::make('is_active')
                            ->label('Ativo')
                            ->default(true),
                    ])->columns(2),

                // --- Campos Reativos ---

                Forms\Components\Section::make('Instruções para o Cliente')
                    ->description('Esta informação será mostrada ao cliente na página de pagamento.')
                    ->schema([
                        Forms\Components\Textarea::make('instructions')
                            ->label('Instruções')
                            ->placeholder('Ex: Após a transferência, envie o comprovativo para...')
                            ->rows(4),
                    ])
                    ->visible(fn(Get $get) => $get('type') !== 'cash_on_delivery'), // Só mostra se NÃO for Pag. na Entrega

                Forms\Components\Section::make('Dados Bancários')
                    ->schema([
                        Forms\Components\TextInput::make('bank_account_holder')->label('Titular da Conta'),
                        Forms\Components\TextInput::make('bank_account_number')->label('Número da Conta'),
                        Forms\Components\TextInput::make('bank_iban')->label('IBAN'),
                    ])
                    ->columns(3)
                    ->visible(fn(Get $get) => $get('type') === 'bank_transfer'), // ✅ Só mostra para Transferência

                Forms\Components\Section::make('Dados Pagamento Express')
                    ->schema([
                        Forms\Components\TextInput::make('phone_number')
                            ->label('Nº de Telefone (Express)')
                            ->tel(),
                    ])
                    ->visible(fn(Get $get) => $get('type') === 'express'), // ✅ Só mostra para Express
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable(),

                Tables\Columns\BadgeColumn::make('type')
                    ->label('Tipo')
                    ->formatStateUsing(fn($state) => match ($state) {
                        'bank_transfer' => 'Bancário',
                        'express' => 'Express',
                        'cash_on_delivery' => 'Na Entrega',
                        default => $state,
                    })
                    ->colors([
                        'primary' => 'bank_transfer',
                        'success' => 'express',
                        'gray' => 'cash_on_delivery',
                    ]),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Ativo')
                    ->boolean(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPaymentMethods::route('/'),
            'create' => Pages\CreatePaymentMethod::route('/create'),
            'edit' => Pages\EditPaymentMethod::route('/{record}/edit'),
        ];
    }
}
