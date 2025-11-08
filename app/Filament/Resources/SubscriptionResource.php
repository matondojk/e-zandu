<?php

namespace App\Filament\Resources;

// --- Imports Adicionados ---
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use App\Models\SubscriptionDetail;
// -----------------------------

use App\Filament\Resources\SubscriptionResource\Pages;

class SubscriptionResource extends Resource
{
    protected static ?string $model = SubscriptionDetail::class;
    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $navigationLabel = 'Assinatura e Pagamentos';
    protected static ?string $navigationGroup = 'Financeiro';

    // --- CORREÇÃO ADICIONADA ---
    protected static ?string $slug = 'assinatura';
    // -----------------------------

    public static function form(Form $form): Form 
    { 
        return $form; 
    }

    public static function table(Table $table): Table 
    { 
        return $table; 
    }

    public static function getPages(): array
    {
        return [
            // Aponta o URL principal do recurso para a nossa página personalizada
            'index' => Pages\ManageSubscription::route('/'),
        ];
    }
}