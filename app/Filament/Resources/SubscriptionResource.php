<?php

// app/Filament/Resources/SubscriptionResource.php
namespace App\Filament\Resources;

use App\Filament\Resources\SubscriptionResource\Pages;
use App\Models\Subscription;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SubscriptionResource extends Resource
{
    protected static ?string $model = Subscription::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    // --- Configuração da Navegação ---
    // 1. Define o nome do grupo
    protected static ?string $navigationGroup = 'Sistema & Config';

    // 2. Define o nome do item no menu
    protected static ?string $navigationLabel = 'Minha Assinatura';

    // 3. Define a ordem (um número alto o joga para o final)
    protected static ?int $navigationSort = 99;
    // --- Fim da Configuração ---


    public static function form(Form $form): Form
    {
        return $form->schema([
            // Vazio, pois é view-only
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('plan_name')->label('Plano'),
                Tables\Columns\TextColumn::make('status')->label('Status')->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'paused' => 'warning',
                        'canceled' => 'danger',
                    }),
                Tables\Columns\TextColumn::make('start_date')->label('Início')->dateTime(),
                Tables\Columns\TextColumn::make('end_date')->label('Expira em')->dateTime(),
                
                // Exibindo os novos campos
                Tables\Columns\TextColumn::make('renewed_at')->label('Última Renovação')->dateTime(),
                Tables\Columns\TextColumn::make('paused_at')->label('Pausado em')->dateTime(),
                Tables\Columns\TextColumn::make('canceled_at')->label('Cancelado em')->dateTime(),
            ])
            ->filters([
                //
            ])
            ->actions([
                // Sem ações
            ])
            ->bulkActions([
                // Sem ações em massa
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSubscriptions::route('/'),
        ];
    }

    // Garante que ninguém possa criar um novo registro de assinatura pelo Filament
    public static function canCreate(): bool
    {
        return false;
    }
}