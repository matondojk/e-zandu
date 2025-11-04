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

    protected function getHeaderActions(): array
    {
        // ... (O SEU CÓDIGO DE AÇÕES PERMANECE IGUAL) ...
        $shippingRate = Cache::remember('shipping_rate', 60 * 5, function () {
            $settings = Setting::find(1);
            return $settings->shipping_rate ?? 0;
        });

        return [
            // --- AÇÃO "MARCAR COMO PAGO" (SIMPLIFICADA) ---
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
                ->action(function (Cart $record, InvoiceService $invoiceService, WhatsAppService $whatsAppService) use ($shippingRate) {

                    try {
                        // 1. Validar Comprovativo
                        if (empty($record->payment_proof_path)) {
                            Notification::make()->title('Erro: Comprovativo em Falta')->body('Este pedido não tem um comprovativo anexado.')->danger()->send();
                            return;
                        }

                        // 2. Mudar status
                        // O OBSERVER (`updating`) vai correr AQUI, antes do save().
                        $record->status = 'paid';
                        $record->delivery_status = 'processing';
                        $record->save(); // O Observer 'updating' e 'updated' correm aqui

                        // 3. Gerar Fatura
                        $pdfUrl = $invoiceService->generate($record);

                        // 4. Enviar Fatura ao Cliente
                        $whatsAppService->sendDocumentMessage(
                            $pdfUrl,
                            $record->user->phone,
                            "Fatura-Pedido-{$record->id}.pdf"
                        );

                        // 5. Mensagem de Confirmação Atualizada
                        $whatsAppService->sendTextMessage(
                            "Pagamento confirmado para o Pedido #{$record->id}! ✅\nObrigado pela sua compra. O seu produto *já será preparado para envio*.",
                            $record->user->phone
                        );

                        // 6. Notificação de Sucesso
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

            // --- NOVA AÇÃO "MARCAR COMO ENTREGUE" ---
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
                            "O seu Pedido #{$record->id} foi marcado como *entregue*! 🚚✅\n\nEsperamos que goste dos seus produtos. Obrigado por comprar na TangaWA Shop!",
                            $record->user->phone
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

                        $whatsAppService->sendTextMessage($message, $record->user->phone);

                        Notification::make()->title('Pagamento Rejeitado')->body('O cliente foi notificado.')->warning()->send();
                        $this->refreshFormData(['payment_proof_path']);
                    } catch (Exception $e) {
                        Log::error("Erro ao rejeitar pedido #{$record->id}: " . $e->getMessage());
                        Notification::make()->title('Erro ao Rejeitar')->body('Ocorreu um erro. Verifique os logs.')->danger()->send();
                    }
                }),
        ];
    }

    //
    // =================================================================
    // AQUI COMEÇA A CORREÇÃO DO INFOLIST
    // =================================================================
    //
    public function infolist(Infolist $infolist): Infolist
    {
        $shippingRate = Cache::remember('shipping_rate', 60 * 5, function () {
            $settings = Setting::find(1);
            return $settings->shipping_rate ?? 0;
        });

        return $infolist
            ->schema([
                // --- Secção de Notas do Cliente (Se existir) ---
                // Esta secção já estava boa, pois não definia colunas.
                Infolists\Components\Section::make('Notas do Cliente (Tamanho/Cor)')
                    ->schema([
                        Infolists\Components\TextEntry::make('order_notes')
                            ->label('Notas')
                            ->hiddenLabel() // Esconde o label, o título da secção é o suficiente
                            ->markdown() // Permite quebras de linha
                            ->default('Nenhuma nota fornecida.'),
                    ])
                    ->visible(fn(Cart $record) => !empty($record->order_notes)), // Só mostra esta secção se houver notas

                // --- Secção Cliente (CORRIGIDA) ---
                Infolists\Components\Section::make('Detalhes do Cliente')
                    // CORREÇÃO: Usar colunas responsivas. 1 no mobile (default), 3 no desktop (md).
                    ->columns(['default' => 1, 'md' => 3])
                    ->schema([
                        Infolists\Components\TextEntry::make('user.name')->label('Cliente')->icon('heroicon-o-user'),
                        Infolists\Components\TextEntry::make('user.phone')->label('WhatsApp')->copyable(),
                        
                        // CORREÇÃO: O 'columnSpanFull' deve ser responsivo também.
                        Infolists\Components\TextEntry::make('user.delivery_address')
                            ->label('Endereço de Entrega')
                            ->default('Não fornecido')
                            ->columnSpan(['default' => 1, 'md' => 'full']), // Ocupa a largura total em 'md', mas 1 no mobile.
                    ]),

                // --- Secção Status (CORRIGIDA) ---
                Infolists\Components\Section::make('Resumo Financeiro e Status')
                    // CORREÇÃO: A mesma lógica de colunas responsivas.
                    ->columns(['default' => 1, 'md' => 3])
                    ->schema([
                        Infolists\Components\TextEntry::make('id')->label('ID Pedido'),

                        Infolists\Components\TextEntry::make('status')
                            ->label('Status Pagamento')
                            ->badge()
                            ->color(fn(string $state): string => match ($state) {
                                'active' => 'warning',
                                'paid' => 'success',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn(string $state): string => match ($state) {
                                'active' => 'Pendente',
                                'paid' => 'Pago',
                                default => ucfirst($state),
                            }),

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

                        Infolists\Components\TextEntry::make('total_price')
                            ->label('Subtotal (Produtos)')
                            ->formatStateUsing(fn($state): string => number_format($state, 2, ',', '.') . ' Kz'),
                            // ->columnSpan(1) removido por ser desnecessário

                        Infolists\Components\TextEntry::make('shipping_rate_display')
                            ->label('Taxa de Frete')
                            ->state(fn(Cart $record) => number_format($shippingRate, 2, ',', '.') . ' Kz'),
                            // ->columnSpan(1) removido por ser desnecessário

                        Infolists\Components\TextEntry::make('grand_total')
                            ->label('TOTAL GERAL')
                            ->state(fn(Cart $record) => number_format($record->total_price + $shippingRate, 2, ',', '.') . ' Kz')
                            ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                            ->color('success')
                            ->weight('bold'),
                            // ->columnSpan(1) removido por ser desnecessário

                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Data do Pedido')
                            ->dateTime('d/m/Y H:i')
                            // CORREÇÃO: O 'columnSpan(3)' deve ser responsivo.
                            ->columnSpan(['default' => 1, 'md' => 3]),
                    ]),

                // --- Secção Pagamento (Comprovativo) ---
                // Esta secção também já estava boa.
                Infolists\Components\Section::make('Pagamento')
                    ->visible(fn(Cart $record) => !empty($record->payment_proof_path))
                    ->schema([
                        Infolists\Components\TextEntry::make('payment_proof_path')
                            ->label('Comprovativo')
                            ->formatStateUsing(function ($state): ?HtmlString {
                                if (!$state) return null;
                                $url = Storage::disk('public')->url($state);
                                return new HtmlString('<a href="' . $url . '" target="_blank" style="color: #25D366; text-decoration: underline;">Ver Comprovativo</a>');
                            })
                            ->icon('heroicon-m-paper-clip'),
                    ]),
            ]);
    }
}