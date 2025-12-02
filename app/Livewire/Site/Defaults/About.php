<?php

namespace App\Livewire\Site\Defaults;

use Livewire\Component;
use App\Models\ShippingRate;
use App\Models\Setting;

class About extends Component
{
    public function render()
    {
        return view('livewire.site.defaults.about', [
            'settings' => Setting::first(),
            'shippingRates' => ShippingRate::where('is_active', true)->orderBy('province_name')->get(),
        ])->layout('components.layouts.defaults.app', [
            'title' => 'Quem Somos | E-Zandu'
        ]);
    }
}