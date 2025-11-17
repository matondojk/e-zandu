<?php

namespace App\Exports;

use Illuminate\Contracts\Queue\ShouldQueue; // Opcional, mas útil para exportações grandes
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Database\Eloquent\Builder;

class ExpenseExport implements FromQuery, WithHeadings, WithMapping // Pode usar ShouldQueue se quiser
{
    protected $query;

    public function __construct(Builder $query)
    {
        $this->query = $query;
    }

    // Define a query a ser usada (a query filtrada passada pelo Filament)
    public function query()
    {
        return $this->query;
    }

    // Define os cabeçalhos das colunas
    public function headings(): array
    {
        return [
            'ID',
            'Descrição',
            'Valor (Kz)',
            'Data da Despesa',
            'Tipo',
            'Método de Pagamento',
            'Notas',
            'Data de Registo'
        ];
    }

    // Mapeia os dados da linha para o formato da planilha
    public function map($expense): array
    {
        return [
            $expense->id,
            $expense->description,
            number_format($expense->amount, 2, ',', '.'),
            \Carbon\Carbon::parse($expense->expense_date)->format('d/m/Y'),
            $expense->type,
            $expense->payment_method,
            $expense->notes,
            $expense->created_at->format('d/m/Y H:i'),
        ];
    }
}