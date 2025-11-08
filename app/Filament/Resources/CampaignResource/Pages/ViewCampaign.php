<?php

namespace App\Filament\Resources\CampaignResource\Pages;

use App\Filament\Resources\CampaignResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use App\Models\Campaign;

class ViewCampaign extends ViewRecord
{
    protected static string $resource = CampaignResource::class;

    /**
     * Define a Infolist (vista de detalhes) para o registo.
     */
    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                // Secção 1: Detalhes Principais
                Infolists\Components\Section::make('Detalhes da Campanha')
                    ->columns(3)
                    ->schema([
                        Infolists\Components\TextEntry::make('name')
                            ->label('Nome da Campanha'),
                        
                        Infolists\Components\TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->colors([
                                'gray' => 'draft',
                                'info' => 'scheduled',
                                'warning' => 'processing',
                                'success' => 'sent',
                                'danger' => 'failed',
                            ]),
                        
                        Infolists\Components\TextEntry::make('scheduled_at')
                            ->label('Agendada Para')
                            ->dateTime('d/m/Y H:i')
                            ->placeholder('Envio imediato (na criação)'),
                    ]),
                
                // Secção 2: Conteúdo e Audiência
                Infolists\Components\Section::make('Conteúdo e Audiência')
                    ->columns(2)
                    ->schema([
                        Infolists\Components\TextEntry::make('channel')
                            ->label('Canais')
                            ->badge(),
                        
                        Infolists\Components\TextEntry::make('content_type')
                            ->label('Tipo de Conteúdo')
                            ->formatStateUsing(fn ($state) => match($state) {
                                'message' => 'Mensagem Simples',
                                'coupon' => 'Cupão',
                                'product_promo' => 'Promoção de Produto',
                                default => $state
                            }),
                        
                        Infolists\Components\TextEntry::make('coupon.code')
                            ->label('Cupão Anexado')
                            ->placeholder('N/A'),

                        Infolists\Components\TextEntry::make('audience.count')
                            ->label('Público Total')
                            ->state(fn (Campaign $record) => $record->audience()->count() . ' clientes'),
                        
                        Infolists\Components\TextEntry::make('audience_type')
                            ->label('Tipo de Audiência')
                            ->formatStateUsing(fn ($state) => match($state) {
                                'all' => 'Todos os Clientes',
                                'manual' => 'Seleção Manual',
                                'ai_prompt' => 'Filtro por IA',
                                default => $state
                            }),
                        
                        Infolists\Components\TextEntry::make('audience_prompt')
                            ->label('Prompt da IA (Audiência)')
                            ->visible(fn ($state) => !empty($state)),
                        
                        Infolists\Components\TextEntry::make('audience.name')
                            ->label('Clientes na Audiência')
                            ->badge()
                            ->limitList(20)
                            ->columnSpanFull()
                            ->visible(fn (Campaign $record) => $record->audience_type !== 'all' && $record->audience->isNotEmpty()),
                    ]),
                
                // Secção 3: Templates de Mensagem
                Infolists\Components\Section::make('Templates de Mensagem')
                    ->collapsible()
                    ->schema([
                        Infolists\Components\TextEntry::make('whatsapp_template')
                            ->label('Template WhatsApp')
                            ->markdown()
                            ->columnSpanFull(),
                        
                        Infolists\Components\TextEntry::make('email_subject')
                            ->label('Assunto do Email'),
                        
                        // O erro de digitação "InTfolists" foi corrigido aqui
                        Infolists\Components\TextEntry::make('email_template')
                            ->label('Template Email')
                            ->html()
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}