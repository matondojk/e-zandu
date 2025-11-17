<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class OrderExport implements FromQuery, WithHeadings, WithMapping
{
    protected $query;

    public function __construct(Builder $query)
    {
        $this->query = $query;
    }

    // Carrega as relações necessárias
    public function query()
    {
        // Certifique-se de carregar as relações necessárias para acessar os detalhes
        return $this->query->with(['user', 'paymentMethod', 'shippingRate']);
    }

    // Define os cabeçalhos das colunas conforme solicitado
    public function headings(): array
    {
        return [
            'Nº Pedido',
            'Data',
            'Cliente',
            'Telefone',
            'Status Pagamento',
            'Método Pagamento',
            'Local de Entrega',
            'Subtotal',
            'Frete',
            'Desconto',
            'Total Geral',
        ];
    }

    // Mapeia os dados da linha para o formato da planilha
    public function map($order): array
    {
        $totalGeral = ($order->total_price + $order->shipping_amount) - $order->discount_amount;
        
        $statusMap = [
            'active' => 'Pendente',
            'paid' => 'Pago',
            'completed' => 'Concluído',
            'cancelled' => 'Cancelado',
        ];

        return [
            $order->cart_number ?? $order->id,
            $order->created_at->format('d/m/Y H:i'),
            $order->user->name ?? 'Anónimo',
            $order->user->phone ?? '-',
            $statusMap[$order->status] ?? ucfirst($order->status),
            $order->paymentMethod->name ?? 'N/A',
            $order->shippingRate->province_name ?? 'N/A',
            number_format($order->total_price, 2, ',', '.'),
            number_format($order->shipping_amount, 2, ',', '.'),
            number_format($order->discount_amount, 2, ',', '.'),
            number_format($totalGeral, 2, ',', '.'),
        ];
    }
}