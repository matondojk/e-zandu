<div class="bg-white rounded-[2rem] shadow-lg border border-gray-100 overflow-hidden sticky top-24">
    <div class="p-8 text-center bg-gradient-to-b from-gray-50 to-white border-b border-gray-100">
        <div class="relative w-24 h-24 mx-auto mb-4">
            <div class="w-full h-full rounded-full p-1 bg-white border border-gray-200 shadow-sm overflow-hidden group">
                @if(Auth::user()->avatar)
                     <img src="{{ filter_var(Auth::user()->avatar, FILTER_VALIDATE_URL) ? Auth::user()->avatar : Storage::disk('public')->url(Auth::user()->avatar) }}" class="w-full h-full rounded-full object-cover transition transform group-hover:scale-110 duration-500">
                @else
                    <div class="w-full h-full rounded-full bg-gray-100 flex items-center justify-center text-gray-400 font-bold text-3xl group-hover:bg-gray-200 transition">
                        {{ substr(Auth::user()->name, 0, 1) }}
                    </div>
                @endif
            </div>
            <div class="absolute bottom-1 right-1 w-5 h-5 bg-green-500 border-4 border-white rounded-full" title="Online"></div>
        </div>
        
        <h3 class="font-bold text-gray-900 text-lg mb-1">{{ Auth::user()->name }}</h3>
        <p class="text-gray-500 text-xs font-medium bg-gray-100 px-3 py-1 rounded-full inline-block truncate max-w-[200px]">
            {{ Auth::user()->email }}
        </p>
    </div>

    <nav class="p-4 space-y-2">
        <a href="{{ route('my.orders') }}" wire:navigate 
           class="flex items-center gap-4 px-5 py-4 rounded-2xl transition-all duration-200 font-bold text-sm {{ request()->routeIs('my.orders') || request()->routeIs('order.details') ? 'bg-zandu-red text-white shadow-md transform scale-[1.02]' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
            <i class="ph-bold ph-package text-xl"></i>
            Meus Pedidos
        </a>
        
        <a href="{{ route('my-account') }}" wire:navigate 
           class="flex items-center gap-4 px-5 py-4 rounded-2xl transition-all duration-200 font-bold text-sm {{ request()->routeIs('my-account') ? 'bg-zandu-red text-white shadow-md transform scale-[1.02]' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
            <i class="ph-bold ph-user-gear text-xl"></i>
            Meus Dados
        </a>

        <div class="pt-4 mt-2 border-t border-gray-50">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full flex items-center gap-4 px-5 py-4 rounded-2xl text-red-600 hover:bg-red-50 transition-all duration-200 font-bold text-sm group">
                    <i class="ph-bold ph-sign-out text-xl group-hover:translate-x-1 transition-transform"></i>
                    Sair da Conta
                </button>
            </form>
        </div>
    </nav>
</div>