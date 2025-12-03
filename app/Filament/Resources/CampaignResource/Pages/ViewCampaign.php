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
                            ->formatStateUsing(fn($state) => match ($state) { // ✅ Tradução dos Status
                                'draft' => 'Rascunho',
                                'scheduled' => 'Agendada',
                                'processing' => 'Processando',
                                'sent' => 'Enviada',
                                'failed' => 'Falhou',
                                default => $state,
                            })
                            ->colors([ // ✅ Cores dos Status
                                'gray' => 'draft',
                                'info' => 'scheduled',
                                'warning' => 'processing',
                                'success' => 'sent',
                                'danger' => 'failed',
                            ]),

                        Infolists\Components\TextEntry::make('scheduled_at')
                            ->label('Agendada Para')
                            ->dateTime('d/m/Y H:i')
                            ->placeholder('Envio imediato'),
                    ]),

                // Secção 2: Conteúdo e Audiência (Modernizado com Fieldset)
                Infolists\Components\Section::make('Conteúdo e Audiência')
                    ->columns(2)
                    ->schema([
                        Infolists\Components\Fieldset::make('Informações de Envio')
                            ->columns(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('channel')
                                    ->label('Canais')
                                    ->badge()
                                    ->formatStateUsing(fn(string $state): string => match ($state) {
                                        'whatsapp' => 'WhatsApp',
                                        'email'    => 'E-mail',
                                        'both'     => 'WhatsApp e E-mail',
                                        default    => $state,
                                    })
                                    ->colors([
                                        'success' => 'whatsapp',
                                        'info'    => 'email',
                                        'primary' => 'both',
                                    ]),

                                Infolists\Components\TextEntry::make('audience.count')
                                    ->label('Público Total')
                                    ->state(fn(Campaign $record) => $record->audience()->count() . ' clientes'),
                            ]),

                        Infolists\Components\Fieldset::make('Detalhamento')
                            ->columns(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('content_type')
                                    ->label('Tipo de Conteúdo')
                                    ->formatStateUsing(fn($state) => match ($state) {
                                        'message' => 'Mensagem Simples',
                                        'coupon' => 'Cupão',
                                        'product_promo' => 'Promoção de Produto',
                                        default => $state
                                    }),

                                Infolists\Components\TextEntry::make('coupon.code')
                                    ->label('Cupão Anexado')
                                    ->placeholder('N/A'),
                            ]),

                        Infolists\Components\Fieldset::make('Audiência Selecionada')
                            ->columns(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('audience_type')
                                    ->label('Tipo de Seleção')
                                    ->formatStateUsing(fn($state) => match ($state) {
                                        'all' => 'Todos',
                                        'manual' => 'Manual',
                                        'ai_prompt' => 'Filtro IA',
                                        default => $state
                                    }),

                                Infolists\Components\TextEntry::make('audience_prompt')
                                    ->label('Prompt da IA')
                                    ->visible(fn($state) => !empty($state)),
                            ])->columnSpanFull(),
                    ]),

                // Secção 3: Templates de Mensagem
                Infolists\Components\Section::make('Templates de Mensagem')
                    ->collapsible()
                    ->schema([
                        Infolists\Components\TextEntry::make('whatsapp_template')
                            ->label('Template WhatsApp')
                            ->markdown()
                            ->visible(fn(Campaign $record) => in_array($record->channel, ['whatsapp', 'both'])) // ✅ Visibilidade Condicional
                            ->columnSpanFull(),

                        Infolists\Components\TextEntry::make('email_subject')
                            ->label('Assunto do Email')
                            ->visible(fn(Campaign $record) => in_array($record->channel, ['email', 'both'])), // ✅ Visibilidade Condicional

                        Infolists\Components\TextEntry::make('email_template')
                            ->label('Template Email')
                            ->html()
                            ->visible(fn(Campaign $record) => in_array($record->channel, ['email', 'both'])) // ✅ Visibilidade Condicional
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
