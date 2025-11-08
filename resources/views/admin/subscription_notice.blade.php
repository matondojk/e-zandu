
    <div class="container">
        <h1>A sua assinatura requer atenção</h1>

        @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        @if($subscription)
            <p><strong>Plano Atual:</strong> {{ $subscription->plan_name }}</p>
            <p><strong>Status:</strong> {{ $subscription->status }}</p>
            @if($subscription->ends_at)
                <p><strong>Data de Expiração:</strong> {{ $subscription->ends_at->format('d/m/Y') }}</p>
            @endif
        @else
             <p>Você não possui uma assinatura ativa.</p>
        @endif

        <p>Por favor, entre em contacto com o nosso suporte (support@matondojoao.com) para regularizar a sua situação.</p>
        
        {{-- Link para o histórico de pagamentos que você mencionou --}}
        <a href="{{ route('admin.subscription.history') }}">Ver histórico de pagamentos</a>
    </div>
