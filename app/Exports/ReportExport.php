<?php

namespace App\Exports;

use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ReportExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected $reportId;
    protected $reportData;

    public function __construct(string $reportId)
    {
        $this->reportId = $reportId;
        // Puxar os dados do cache imediatamente
        $this->reportData = Cache::get($this->reportId);
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        // Retorna os dados como uma coleção
        return collect($this->reportData['data'] ?? []);
    }

    /**
    * @return array
    */
    public function headings(): array
    {
        // Retorna as colunas como cabeçalhos
        return $this->reportData['columns'] ?? [];
    }
}
