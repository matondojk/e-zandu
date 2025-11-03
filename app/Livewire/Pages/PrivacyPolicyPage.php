<?php

namespace App\Livewire\Pages;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title; // <-- Importar

#[Layout('layouts.app')]
#[Title('Política de Privacidade - E-Zandu')] 
class PrivacyPolicyPage extends Component
{
    public function render()
    {
        return view('livewire.pages.privacy-policy-page');
    }
}