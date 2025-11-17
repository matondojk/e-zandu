<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PurchaseExport implements FromQuery, WithHeadings, WithMapping
{
    protected $query;

    public function __construct(Builder $query)
    {
        $this->query = $query;
    }

    // Carrega as relações necessárias
    public function query()
    {
        return $this->query->with(['supplier', 'user', 'stock']);
    }

    // Define os cabeçalhos das colunas
    public function headings(): array
    {
        return [
            'Nº Compra',
            'Status',
            'Data de Emissão',
            'Fornecedor',
            'Responsável',
            'Armazém Destino',
            'Entregue',
            'Subtotal (Itens)',
            'Desconto',
            'Frete',
            'IVA (14%)',
            'Total Geral',
        ];
    }

    public function map($purchase): array
    {
        return [
            $purchase->purchase_number,
            $purchase->status,
            $purchase->issue_date ? \Carbon\Carbon::parse($purchase->issue_date)->format('d/m/Y') : '-',
            $purchase->supplier->name ?? 'N/A',
            $purchase->user->name ?? 'N/A',
            $purchase->stock->name ?? 'N/A',
            $purchase->is_delivered ? 'Sim' : 'Não',
            
            number_format($purchase->total_amount, 2, ',', '.'),
            number_format($purchase->discount_total, 2, ',', '.'),
            number_format($purchase->freight_cost, 2, ',', '.'),
            number_format($purchase->iva_amount, 2, ',', '.'),
            number_format($purchase->total_payable, 2, ',', '.'),
        ];
    }
}