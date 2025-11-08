<x-filament-infolists::entry-wrapper>
    <ul class="space-y-4">
        @if(is_array($getState()))
            @foreach($getState() as $feature)
                <li class="flex items-start space-x-3 p-3 rounded-lg ring-1 ring-gray-200 dark:ring-gray-700">
                    
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-primary-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </div>

                    <div class="flex-1">
                        <p class="font-medium text-gray-900 dark:text-white">
                            {{ $feature['description'] ?? 'N/A' }}
                        </p>
                        
                        @php
                            $availability = $feature['plan_availability'] ?? 'both';
                            
                            $badgeClass = match($availability) {
                                'pro' => 'text-warning-700 bg-warning-50 ring-warning-600/20 dark:text-warning-400 dark:bg-warning-400/10 dark:ring-warning-400/20',
                                'essencial' => 'text-info-700 bg-info-50 ring-info-600/20 dark:text-info-400 dark:bg-info-400/10 dark:ring-info-400/20',
                                'trial' => 'text-gray-700 bg-gray-50 ring-gray-600/20 dark:text-gray-400 dark:bg-gray-400/10 dark:ring-gray-400/20',
                                default => 'text-success-700 bg-success-50 ring-success-600/20 dark:text-success-400 dark:bg-success-400/10 dark:ring-success-400/20',
                            };
                            $badgeText = match($availability) {
                                'pro' => 'PLANO PRO',
                                'essencial' => 'PLANO ESSENCIAL',
                                'trial' => 'PLANO TRIAL',
                                default => 'TODOS OS PLANOS',
                            };
                        @endphp
                        
                        <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium ring-1 ring-inset {{ $badgeClass }}">
                            {{ $badgeText }}
                        </span>
                    </div>
                </li>
            @endforeach
        @endif
    </ul>
</x-filament-infolists::entry-wrapper>