<?php

namespace App\Filament\Resources\CampaignResource\Pages;

use App\Filament\Resources\CampaignResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCampaign extends EditRecord
{
    protected static string $resource = CampaignResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
    
    // ✅ NOVO: Lógica de sincronização para edição
    protected function afterSave(): void
    {
        $campaign = $this->getRecord();
        $data = $this->data; 

        // ✅ CORRIGIDO: Lógica manual para sincronizar produtos com dados pivot
        if ($campaign->content_type === 'product_promo' && !empty($data['productsInPromotion'])) {
            $syncData = collect($data['productsInPromotion'])->mapWithKeys(function ($item) {
                // Mapeia product_id para os dados pivot promotional_price
                return [
                    $item['product_id'] => ['promotional_price' => $item['promotional_price']]
                ];
            })->toArray();
            
            // Sincroniza (atualiza ou anexa) a relação
            $campaign->productsInPromotion()->sync($syncData);
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
}