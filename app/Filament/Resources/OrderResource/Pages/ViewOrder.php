<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use App\Services\InvoiceService;
use App\Services\WhatsAppService;
use Exception;
use Filament\Notifications\Notification;
use App\Models\Cart;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderPaid; 
use App\Mail\OrderDelivered;
use App\Mail\OrderRejected;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Illuminate\Support\HtmlString;
use Filament\Forms;
use Barryvdh\DomPDF\Facade\Pdf; 
use Illuminate\Support\Facades\Auth; // Necessário para auth()->id()

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
             // ✅ AÇÃO PDF
             Actions\Action::make('download_pdf')
             ->label('Baixar PDF')
             ->icon('heroicon-o-document-arrow-down')
             ->color('gray')
             ->action(function (Cart $record) {
                 $record->load(['items.product', 'user', 'shippingRate', 'paymentMethod']);
                 $settings = Setting::first();
                 
                 $pdf = Pdf::loadView('pdfs.order-details', [
                     'record' => $record,
                     'settings' => $settings
                 ]);

                 return response()->streamDownload(function () use ($pdf) {
                     echo $pdf->output();
                 }, 'pedido-' . ($record->cart_number ?? $record->id) . '.pdf');
             }),

            // --- AÇÃO "CONFIRMAR PAGAMENTO" (Com Auditoria) ---
            Actions\Action::make('markAsPaid')
                ->label('Confirmar Pagamento')
                ->icon('heroicon-o-check')
                ->color('gray')
                ->visible(fn(Cart $record): bool => $record->status === 'active')
                ->requiresConfirmation()
                ->modalHeading('Confirmar Pagamento?')
                ->modalDescription('Confirmar recebimento e deduzir estoque?')
                ->action(function (Cart $record, InvoiceService $invoiceService, WhatsAppService $whatsAppService) { 
                    try {
                        $record->status = 'paid';
                        $record->delivery_status = 'processing';
                        $record->payment_confirmed_by = auth()->id();
                        $record->payment_confirmed_at = now(); // Salva a data/hora
                        $record->save(); 
                        
                        $user = $record->user;
                        $pdfPath = $invoiceService->generate($record);
                        $pdfPublicUrl = Storage::disk('public')->url($pdfPath);
                        $pdfAbsolutePath = storage_path('app/public/' . $pdfPath);

                        if ($user && $user->phone) {
                            $whatsAppService->sendDocumentMessage($pdfPublicUrl, $user->phone, "Fatura-{$record->cart_number}.pdf");
                            $whatsAppService->sendTextMessage("Pagamento confirmado para o Pedido *{$record->cart_number}*! ✅", $user->phone);
                        }

                        if ($user && $user->email) {
                            try {
                                Mail::to($user->email)->send(new OrderPaid($record, $pdfAbsolutePath));
                            } catch (Exception $e) { Log::error("Erro Email: " . $e->getMessage()); }
                        }

                        Notification::make()->title('Pagamento Confirmado!')->success()->send();
                        $this->refreshFormData(['status', 'delivery_status', 'payment_confirmed_by', 'payment_confirmed_at']);

                    } catch (Exception $e) {
                        Notification::make()->title('Erro')->body('Verifique os logs.')->danger()->send();
                    }
                }),

            // --- AÇÃO "CONFIRMAR ENTREGA" (Com Auditoria) ---
            Actions\Action::make('markAsDelivered')
                ->label('Confirmar Entrega')
                ->icon('heroicon-o-truck')
                ->color('gray')
                ->visible(fn(Cart $record): bool => $record->status === 'paid' && $record->delivery_status !== 'delivered')
                ->requiresConfirmation()
                ->action(function (Cart $record, WhatsAppService $whatsAppService) {
                    try {
                        $record->delivery_status = 'delivered';
                        $record->delivery_confirmed_by = auth()->id();
                        $record->delivery_confirmed_at = now(); // Salva a data/hora
                        $record->save();
                        
                        $user = $record->user;
                        if ($user && $user->phone) {
                            $whatsAppService->sendTextMessage("O seu Pedido *{$record->cart_number}* foi entregue! 🚚✅", $user->phone);
                        }
                        if ($user && $user->email) {
                            try {
                                Mail::to($user->email)->send(new OrderDelivered($record));
                            } catch (Exception $e) {}
                        }

                        Notification::make()->title('Entrega Confirmada!')->success()->send();
                        $this->refreshFormData(['delivery_status', 'delivery_confirmed_by', 'delivery_confirmed_at']);
                    } catch (Exception $e) {
                        Notification::make()->title('Erro')->danger()->send();
                    }
                }),

            // --- AÇÃO "REJEITAR" (Com Auditoria) ---
            Actions\Action::make('rejectPayment')
                ->label('Rejeitar')
                ->color('danger') 
                ->visible(fn(Cart $record): bool => $record->status === 'active' && !empty($record->payment_proof_path))
                ->form([
                    Forms\Components\Textarea::make('reason')->label('Motivo')->required(),
                ])
                ->action(function (Cart $record, array $data, WhatsAppService $whatsAppService) {
                    try {
                        $record->status = 'cancelled';
                        $record->rejected_by = auth()->id();
                        $record->rejected_at = now(); // Salva a data/hora
                        $record->save();
                        
                        // ... (Lógica de rejeição) ...

                        Notification::make()->title('Pagamento Rejeitado.')->success()->send();
                        $this->refreshFormData(['status', 'rejected_by', 'rejected_at']);
                    } catch (Exception $e) {
                        Notification::make()->title('Erro ao Rejeitar.')->danger()->send();
                    }
                }),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                // ✅ CABEÇALHO DO PEDIDO 
                Infolists\Components\Section::make('Detalhes do Pedido')
                    ->columns(['default' => 1, 'md' => 3])
                    ->schema([
                        Infolists\Components\TextEntry::make('cart_number')
                            ->label('Nº Pedido')
                            ->weight('bold')
                            ->size('lg')
                            ->icon('heroicon-o-hashtag')
                            ->copyable(),

                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Data')
                            ->date('d/m/Y H:i')
                            ->icon('heroicon-o-calendar'),

                        Infolists\Components\TextEntry::make('status')
                            ->label('Pagamento')
                            ->badge()
                            ->color(fn(string $state) => match ($state) {
                                'active' => 'warning', 'paid' => 'success', 'completed' => 'success', default => 'gray'
                            })
                            ->formatStateUsing(fn($state) => match($state) {
                                'active' => 'Pendente', 'paid' => 'Pago', default => ucfirst($state)
                            }),
                    ]),

                // ✅ CLIENTE E ENTREGA
                Infolists\Components\Split::make([
                    Infolists\Components\Section::make('Cliente')
                        ->schema([
                            Infolists\Components\TextEntry::make('user.name')->label('Nome')->weight('bold'),
                            Infolists\Components\TextEntry::make('user.phone')->label('WhatsApp')->icon('heroicon-o-phone'),
                            Infolists\Components\TextEntry::make('user.email')->label('Email')->icon('heroicon-o-envelope'),
                        ]),
                    Infolists\Components\Section::make('Entrega')
                        ->schema([
                            Infolists\Components\TextEntry::make('shippingRate.province_name')
                                ->label('Província')
                                ->icon('heroicon-o-map'),
                            Infolists\Components\TextEntry::make('user.delivery_address')
                                ->label('Morada / Ponto de Referência'),
                            Infolists\Components\TextEntry::make('delivery_status')
                                ->label('Estado')
                                ->badge()
                                ->color(fn($state) => $state === 'delivered' ? 'success' : 'warning'),
                        ]),
                ])->from('md'),

                // NOVO: Seção de Rastreabilidade e Auditoria (6 campos em 3 colunas)
                Infolists\Components\Section::make('Rastreabilidade (Auditoria)')
                    ->description('Quem realizou a ação no painel de administração e o momento.')
                    ->columns(['default' => 1, 'md' => 3])
                    ->schema([
                        
                        // COLUNA 1: PAGAMENTO
                        Infolists\Components\TextEntry::make('paymentConfirmedBy.name')
                            ->label('Pagamento Confirmado Por')
                            ->weight('semibold')
                            ->color('success')
                            ->placeholder('N/A'),
                        Infolists\Components\TextEntry::make('payment_confirmed_at') 
                            ->label('Data/Hora')
                            ->dateTime('d/m/Y H:i')
                            ->icon('heroicon-o-clock')
                            ->placeholder('N/A'),
                            
                        // COLUNA 2: ENTREGA
                        Infolists\Components\TextEntry::make('deliveryConfirmedBy.name')
                            ->label('Entrega Confirmada Por')
                            ->weight('semibold')
                            ->color('info')
                            ->placeholder('N/A'),
                        Infolists\Components\TextEntry::make('delivery_confirmed_at') 
                            ->label('Data/Hora')
                            ->dateTime('d/m/Y H:i')
                            ->icon('heroicon-o-clock')
                            ->placeholder('N/A'),

                        // COLUNA 3: REJEIÇÃO
                        Infolists\Components\TextEntry::make('rejectedBy.name')
                            ->label('Rejeitado Por')
                            ->weight('semibold')
                            ->color('danger')
                            ->placeholder('N/A'),
                        Infolists\Components\TextEntry::make('rejected_at') 
                            ->label('Data/Hora')
                            ->dateTime('d/m/Y H:i')
                            ->icon('heroicon-o-clock')
                            ->placeholder('N/A'),
                    ]),

                // ✅ FINANCEIRO 
                Infolists\Components\Section::make('Resumo Financeiro')
                    ->columns(['default' => 1, 'md' => 4])
                    ->schema([
                        Infolists\Components\TextEntry::make('total_price')
                            ->label('Subtotal')
                            ->formatStateUsing(fn($state) => number_format($state, 2, ',', '.') . ' Kz'),

                        Infolists\Components\TextEntry::make('shipping_amount')
                            ->label('Frete')
                            ->formatStateUsing(fn($state) => '+ ' . number_format($state, 2, ',', '.') . ' Kz'),

                        Infolists\Components\TextEntry::make('discount_amount')
                            ->label('Desconto')
                            ->formatStateUsing(fn($state) => '- ' . number_format($state, 2, ',', '.') . ' Kz')
                            ->color('danger'),

                        Infolists\Components\TextEntry::make('grand_total')
                            ->label('TOTAL')
                            ->state(fn(Cart $record) => 
                                number_format(($record->total_price + $record->shipping_amount) - $record->discount_amount, 2, ',', '.') . ' Kz'
                            )
                            ->weight('bold')
                            ->size('xl')
                            ->color('success'),
                    ]),

                // ✅ NOTAS E COMPROVATIVO
                Infolists\Components\Section::make('Outras Informações')
                    ->columns(2)
                    ->schema([
                        Infolists\Components\TextEntry::make('order_notes')
                            ->label('Notas do Cliente')
                            ->markdown()
                            ->placeholder('Sem notas'),
                        
                        Infolists\Components\TextEntry::make('payment_proof_path')
                            ->label('Comprovativo')
                            ->formatStateUsing(fn() => new HtmlString('<span style="color:primary">Ver Anexo</span>'))
                            ->url(fn($record) => $record->payment_proof_path ? Storage::disk('public')->url($record->payment_proof_path) : null)
                            ->openUrlInNewTab()
                            ->visible(fn($record) => !empty($record->payment_proof_path)),
                    ]),
            ]);
    }
}