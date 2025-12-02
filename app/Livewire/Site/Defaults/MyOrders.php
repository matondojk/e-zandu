<?php

namespace App\Livewire\Site\Defaults;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Cart;
use Illuminate\Support\Facades\Auth;
use App\Services\InvoiceService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class MyOrders extends Component
{
    use WithPagination;

    public function render()
    {
        $orders = Cart::where('user_id', Auth::id())
            ->with(['shippingRate']) // <--- ADICIONAR ISTO (Carrega a taxa de envio)
            ->withCount('items')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.site.defaults.my-orders', [
            'orders' => $orders
        ])->layout('components.layouts.defaults.app', [
            'title' => 'Meus Pedidos | Zandu'
        ]);
    }

    public function downloadInvoice($cartId)
    {
        $cart = Cart::where('user_id', Auth::id())->findOrFail($cartId);
        
        try {
            $invoiceService = new InvoiceService();
            $relativePath = $invoiceService->generate($cart);
            
            if (!Storage::disk('public')->exists($relativePath)) {
                throw new \Exception("Arquivo não encontrado no disco após geração.");
            }

            $fullPath = Storage::disk('public')->path($relativePath);
            return response()->download($fullPath);

        } catch (\Exception $e) {
            Log::error("Erro Download Fatura: " . $e->getMessage());
            $this->dispatch('toast', ['type' => 'error', 'message' => 'Erro: ' . $e->getMessage()]);
        }
    }
}