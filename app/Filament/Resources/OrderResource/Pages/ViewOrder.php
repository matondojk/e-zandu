<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

// --- Imports para a Ação ---
use App\Services\InvoiceService;
use App\Services\WhatsAppService;
use Exception;
use Filament\Notifications\Notification;
use App\Models\Cart;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Filament\Forms;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Model;
// --- Fim dos Imports ---

// Imports do Infolist
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Illuminate\Support\HtmlString;


class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    /**
     * Define as Ações do Cabeçalho (Marcar como Pago, Rejeitar, etc.)
     */
    protected function getHeaderActions(): array
    {
        // ❌ REMOVIDO: A variável $shippingRate estática
        
        return [
            // --- AÇÃO "MARCAR COMO PAGO" ---
            Actions\Action::make('markAsPaid')
                ->label('Marcar como Pago')
                ->tooltip('Validar Pagamento, Deduzir Estoque e Enviar Fatura')
                ->icon('heroicon-s-check-circle')
                ->color('success')
                ->iconButton()
                ->visible(fn(Cart $record): bool => $record->status === 'active')
                ->requiresConfirmation()
                ->modalHeading('Confirmar Pagamento?')
                ->modalDescription('O sistema irá verificar o estoque e deduzir as quantidades do Armazém Padrão.')
                ->modalSubmitActionLabel('Sim, Confirmar Pagamento')
                // ❌ REMOVIDO: use ($shippingRate)
                ->action(function (Cart $record, InvoiceService $invoiceService, WhatsAppService $whatsAppService) { 

                    try {
                        if (empty($record->payment_proof_path)) {
                            Notification::make()->title('Erro: Comprovativo em Falta')->body('Este pedido não tem um comprovativo anexado.')->danger()->send();
                            return;
                        }
                        $record->status = 'paid';
                        $record->delivery_status = 'processing';
                        $record->save(); 
                        $pdfUrl = $invoiceService->generate($record);
                        $whatsAppService->sendDocumentMessage(
                            $pdfUrl,
                            $record->user->phone, // ✅ CORRIGIDO: user -> user
                            "Fatura-Pedido-{$record->id}.pdf"
                        );
                        $whatsAppService->sendTextMessage(
                            "Pagamento confirmado para o Pedido #{$record->id}! ✅\nO seu produto *já será preparado para envio*.",
                            $record->user->phone // ✅ CORRIGIDO: user -> user
                        );
                        Notification::make()->title('Pedido Marcado como Pago!')
                            ->body("O estoque foi deduzido e a fatura foi enviada.")
                            ->success()->send();
                        $this->refreshFormData(['status', 'delivery_status']);
                    } catch (Exception $e) {
                        Log::error("Erro ao marcar pedido #{$record->id} como pago: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
                        if (!str_contains($e->getMessage(), 'Armazém Padrão') && !str_contains($e->getMessage(), 'Estoque')) {
                            Notification::make()->title('Erro ao Processar')
                                ->body('Ocorreu um erro. Verifique os logs.')
                                ->danger()->send();
                        }
                    }
                }),

            // --- AÇÃO "MARCAR COMO ENTREGUE" ---
            Actions\Action::make('markAsDelivered')
                ->label('Marcar como Entregue')
                ->tooltip('Marcar o pedido como entregue e notificar o cliente')
                ->icon('heroicon-o-truck')
                ->color('info')
                ->iconButton()
                ->visible(fn(Cart $record): bool => $record->status === 'paid' && $record->delivery_status !== 'delivered')
                ->requiresConfirmation()
                ->modalHeading('Confirmar Entrega?')
                ->modalSubmitActionLabel('Sim, Confirmar Entrega')
                ->action(function (Cart $record, WhatsAppService $whatsAppService) {
                    try {
                        $record->delivery_status = 'delivered';
                        $record->save();
                        $whatsAppService->sendTextMessage(
                            "O seu Pedido #{$record->id} foi marcado como *entregue*! 🚚✅\n\nEsperamos que goste dos seus produtos. Obrigado por comprar na E-Zandu!",
                            $record->user->phone // ✅ CORRIGIDO: user -> user
                        );
                        Notification::make()->title('Pedido Marcado como Entregue!')->success()->send();
                        $this->refreshFormData(['delivery_status']);
                    } catch (Exception $e) {
                        Log::error("Erro ao marcar pedido #{$record->id} como entregue: " . $e->getMessage());
                        Notification::make()->title('Erro ao Processar')->danger()->send();
                    }
                }),

            // --- Ação de "Rejeitar" ---
            Actions\Action::make('rejectPayment')
                ->label('Rejeitar Comprovativo')
                ->tooltip('Rejeitar comprovativo e notificar cliente')
                ->icon('heroicon-s-x-circle')
                ->color('danger')
                ->iconButton()
                ->visible(fn(Cart $record): bool => $record->status === 'active' && !empty($record->payment_proof_path))
                ->form([
                    Forms\Components\Textarea::make('reason')
                        ->label('Motivo da Rejeição')->required()
                        ->placeholder('Ex: Comprovativo ilegível, valor incorreto...'),
                ])
                ->action(function (Cart $record, array $data, WhatsAppService $whatsAppService) {
                    try {
                        $reason = $data['reason'];
                        if ($record->payment_proof_path) {
                            if (Storage::disk('public')->exists($record->payment_proof_path)) {
                                Storage::disk('public')->delete($record->payment_proof_path);
                            }
                        }
                        $record->payment_proof_path = null;
                        $record->save();
                        $message = "❌ Pagamento Rejeitado para o Pedido #{$record->id}.\n\n";
                        $message .= "Motivo: *{$reason}*\n\n";
                        $message .= "Por favor, submeta um novo comprovativo no link de pagamento que recebeu.";
                        $whatsAppService->sendTextMessage($message, $record->user->phone); // ✅ CORRIGIDO: user -> user
                        Notification::make()->title('Pagamento Rejeitado')->body('O cliente foi notificado.')->warning()->send();
                        $this->refreshFormData(['payment_proof_path']);
                    } catch (Exception $e) {
                        Log::error("Erro ao rejeitar pedido #{$record->id}: " . $e->getMessage());
                        Notification::make()->title('Erro ao Rejeitar')->body('Ocorreu um erro. Verifique os logs.')->danger()->send();
                    }
                }),
        ];
    }


    public function infolist(Infolist $infolist): Infolist
    {
        // ❌ REMOVIDO: A variável $shippingRate estática
        
        return $infolist
            ->schema([
                // --- Secção de Notas do Cliente ---
                Infolists\Components\Section::make('Notas do Cliente (Tamanho/Cor)')
                    ->schema([
                        Infolists\Components\TextEntry::make('order_notes')
                            ->label('Notas')
                            ->hiddenLabel() 
                            ->markdown() 
                            ->default('Nenhuma nota fornecida.'),
                    ])
                    ->visible(fn(Cart $record) => !empty($record->order_notes)),

                // --- Secção Cliente ---
                Infolists\Components\Section::make('Detalhes do Cliente')
                    ->columns(['default' => 1, 'md' => 3])
                    ->schema([
                        // ✅ CORRIGIDO: user -> user
                        Infolists\Components\TextEntry::make('user.name')->label('Cliente')->icon('heroicon-o-user'),
                        Infolists\Components\TextEntry::make('user.phone')->label('WhatsApp')->copyable(),
                        Infolists\Components\TextEntry::make('user.delivery_address')
                            ->label('Referência de Entrega')
                            ->default('Não fornecido')
                            ->columnSpan(['default' => 1, 'md' => 'full']),
                    ]),

                // --- Secção Pagamento e Entrega ---
                Infolists\Components\Section::make('Dados de Pagamento e Entrega')
                    ->columns(['default' => 1, 'md' => 3])
                    ->schema([
                        
                        // ✅ NOVO: Método de Pagamento
                        Infolists\Components\TextEntry::make('paymentMethod.name')
                            ->label('Método de Pagamento')
                            ->badge()
                            ->color('primary'),

                        // ✅ NOVO: Local de Entrega
                        Infolists\Components\TextEntry::make('shippingRate.province_name')
                            ->label('Local de Entrega')
                            ->icon('heroicon-m-map-pin'),
                            
                        Infolists\Components\TextEntry::make('delivery_status')
                            ->label('Status Entrega')
                            ->badge()
                            ->color(fn(string $state): string => match ($state) {
                                'pending' => 'secondary',
                                'processing' => 'warning',
                                'delivered' => 'success',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn(string $state): string => match ($state) {
                                'pending' => 'Pendente',
                                'processing' => 'Em Processamento',
                                'delivered' => 'Entregue',
                                default => ucfirst($state),
                            }),
                    ]),

                // --- Secção Resumo Financeiro ---
                Infolists\Components\Section::make('Resumo Financeiro e Status')
                    ->columns(['default' => 1, 'md' => 3])
                    ->schema([
                        Infolists\Components\TextEntry::make('id')->label('ID Pedido'),
                        Infolists\Components\TextEntry::make('status')
                            ->label('Status Pagamento')
                            ->badge()
                            ->color(fn(string $state): string => match ($state) {
                                'active' => 'warning',
                                'paid' => 'success',
                                'completed' => 'success',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn(string $state): string => match ($state) {
                                'active' => 'Pendente',
                                'paid' => 'Pago',
                                'completed' => 'Concluído',
                                default => ucfirst($state),
                            }),
                        
                        // Ocupa o último slot da linha
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Data do Pedido')
                            ->dateTime('d/m/Y H:i')
                            ->columnSpan(1), 

                        // --- Linha 2 dos valores ---
                        
                        Infolists\Components\TextEntry::make('total_price')
                            ->label('Subtotal (Produtos)')
                            ->formatStateUsing(fn($state): string => number_format($state, 2, ',', '.') . ' Kz'),
                        
                        // ✅ CORRIGIDO: Usa o shipping_amount do registro
                        Infolists\Components\TextEntry::make('shipping_amount') 
                            ->label('Taxa de Frete')
                            ->formatStateUsing(fn($state): string => number_format($state, 2, ',', '.') . ' Kz'),

                        // ✅ ADICIONADO: Campo de Desconto
                        Infolists\Components\TextEntry::make('discount_amount')
                            ->label('Desconto Aplicado')
                            ->formatStateUsing(fn($state): string => number_format($state, 2, ',', '.') . ' Kz')
                            ->color('danger')
                            ->icon('heroicon-m-receipt-percent')
                            ->visible(fn (Cart $record) => $record->discount_amount > 0),
                        
                        // ✅ ADICIONADO: Campo de Cupão
                        Infolists\Components\TextEntry::make('coupon.code') 
                            ->label('Cupão Utilizado')
                            ->badge()
                            ->color('gray')
                            ->visible(fn (Cart $record) => $record->coupon_id)
                            ->columnSpan(['default' => 1, 'md' => 2]), // Ocupa 2 colunas para alinhar
                        
                        // ✅ CÁLCULO FINAL: Usa o shipping_amount e alinha-se
                        Infolists\Components\TextEntry::make('grand_total')
                            ->label('TOTAL GERAL')
                            ->state(fn(Cart $record) => 
                                // (Subtotal + Frete) - Desconto
                                number_format(
                                    ($record->total_price + $record->shipping_amount) - $record->discount_amount, 
                                    2, ',', '.'
                                ) . ' Kz'
                            )
                            ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                            ->color('success')
                            ->weight('bold')
                            ->columnSpan(['default' => 1, 'md' => 3]), // Ocupa toda a linha
                        
                    ]),

                // --- Secção Pagamento (Comprovativo) ---
                Infolists\Components\Section::make('Comprovativo de Pagamento')
                    ->visible(fn(Cart $record) => !empty($record->payment_proof_path))
                    ->schema([
                        Infolists\Components\TextEntry::make('payment_proof_path')
                            ->label('Comprovativo')
                            ->formatStateUsing(function ($state): ?HtmlString {
                                if (!$state) return null;
                                $url = Storage::disk('public')->url($state);
                                return new HtmlString('<a href="' . $url . '" target="_blank" style="color: #F97316; text-decoration: underline;">Ver Comprovativo (E-Zandu)</a>');
                            })
                            ->icon('heroicon-m-paper-clip'),
                    ]),
            ]);
    }
}