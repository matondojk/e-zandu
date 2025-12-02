<?php

namespace App\Livewire\Site\Defaults;

use Livewire\Component;
use App\Models\Cart;
use Illuminate\Support\Facades\Auth;
use App\Services\InvoiceService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage; // Importante adicionar isto

class OrderDetails extends Component
{
    public $orderId;

    public function mount($orderId)
    {
        $this->orderId = $orderId;
    }

    public function render()
    {
        $order = Cart::where('user_id', Auth::id())
            ->with(['items.product', 'shippingRate', 'paymentMethod', 'coupon'])
            ->findOrFail($this->orderId);

        return view('livewire.site.defaults.order-details', [
            'order' => $order
        ])->layout('components.layouts.defaults.app', [
            'title' => 'Detalhes do Pedido | Zandu'
        ]);
    }

    public function downloadInvoice()
    {
        $cart = Cart::where('user_id', Auth::id())->findOrFail($this->orderId);

        try {
            $invoiceService = new InvoiceService();
            
            // Gera a fatura e recebe o caminho relativo (ex: invoices/fatura-xyz.pdf)
            $relativePath = $invoiceService->generate($cart);

            // Verifica se existe antes de tentar baixar
            if (!Storage::disk('public')->exists($relativePath)) {
                throw new \Exception("O ficheiro foi gerado mas não foi encontrado no disco.");
            }

            // Obtém o caminho absoluto correto usando o driver do Storage
            $fullPath = Storage::disk('public')->path($relativePath);

            return response()->download($fullPath);
            
        } catch (\Exception $e) {
            Log::error("Erro Fatura: " . $e->getMessage());

            $this->dispatch('toast', [
                'type' => 'error', 
                'message' => 'Erro: ' . $e->getMessage()
            ]);
        }
    }
}