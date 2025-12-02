<?php

namespace App\Livewire\Site\Defaults;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Cart;
use Illuminate\Support\Facades\Auth;

class PaymentUpload extends Component
{
    use WithFileUploads;

    public $cart;
    public $cartId;
    public $proof; // Variável para o ficheiro (comprovativo)

    public function mount($cartId)
    {
        $this->cartId = $cartId;
        
        // Busca o pedido e carrega o método de pagamento para mostrar as instruções
        $this->cart = Cart::with('paymentMethod')
            ->where('id', $cartId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        // Se já tiver enviado comprovativo ou pago, redireciona para sucesso
        // Ajuste conforme a sua lógica de status
        if ($this->cart->payment_proof_path || $this->cart->status === 'paid') {
            return redirect()->route('order.success', ['cartId' => $cartId]);
        }
    }

    public function saveProof()
    {
        $this->validate([
            'proof' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120', // 5MB Max
        ], [
            'proof.required' => 'Por favor, anexe o comprovativo.',
            'proof.mimes' => 'O ficheiro deve ser uma imagem (JPG, PNG) ou PDF.',
            'proof.max' => 'O ficheiro não pode ter mais de 5MB.'
        ]);

        // Salvar o ficheiro na pasta 'payment_proofs' dentro do disco 'public'
        $path = $this->proof->store('payment_proofs', 'public');

        // Atualizar o pedido
        $this->cart->update([
            'payment_proof_path' => $path,
            'status' => 'pending' // Mudamos o status para aguardar verificação
        ]);

        $this->dispatch('toast', ['type' => 'success', 'message' => 'Comprovativo enviado com sucesso!']);

        return redirect()->route('order.success', ['cartId' => $this->cartId]);
    }

    public function render()
    {
        return view('livewire.site.defaults.payment-upload')->layout('components.layouts.defaults.app', [
            'title' => 'Pagamento | Zandu'
        ]);
    }
}