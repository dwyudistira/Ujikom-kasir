<?php

namespace App\Exports;

use App\Models\Sales;
use App\Models\Member;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SalesExport implements FromCollection, WithHeadings, WithMapping
{
    protected $groupedSales;
    protected $members;

    public function __construct(Collection $filteredSales)
    {
        $this->groupedSales = $filteredSales->groupBy('invoice_number');

        $this->members = Member::all()->keyBy('id');
    }
    

    public function collection()
    {
        return $this->groupedSales->map(function ($items) {
            return $items->first();
        })->values();
    }

    public function map($sale): array
    {
        $salesByInvoice = $this->groupedSales[$sale->invoice_number];

        $member = $this->members[$sale->member_id] ?? null;

        $produkStr = '';
        $totalSubtotal = 0;
        $totalDiskon = 0;

        foreach ($salesByInvoice as $item) {
            $product = json_decode($item->product_data, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($product)) {
                $nama = $product['nama'] ?? 'Produk';
                $jumlah = $product['jumlah'] ?? 0;
                $subtotal = $product['subtotal'] ?? 0;
                $diskon_member = $product['diskon_member'] ?? 0;

                $produkStr .= "{$nama} ( {$jumlah} : Rp. " . number_format($subtotal, 0, ',', '.') . " ), ";

                $totalSubtotal += $subtotal;
                $totalDiskon += $diskon_member;
            }
        }
        $produkStr = rtrim($produkStr, ', ');

        $totalBayar = max($totalSubtotal - $totalDiskon, 0);
        $totalPaid = $sale->total_paid ?? $totalBayar;
        $kembalian = max($totalPaid - $totalBayar, 0);

        return [
            $member->name ?? 'Bukan Member',
            $member->phone_number ?? '-',
            $member->points ?? '-',
            $produkStr,
            'Rp. ' . number_format($totalSubtotal, 0, ',', '.'),
            'Rp. ' . number_format($totalPaid, 0, ',', '.'),
            'Rp. ' . number_format($totalDiskon, 0, ',', '.'),
            'Rp. ' . number_format($kembalian, 0, ',', '.'),
            optional($sale->created_at)->format('d-m-Y'),
        ];
    }

    public function headings(): array
    {
        return [
            'Nama Pelanggan',
            'No HP Pelanggan',
            'Poin Pelanggan',
            'Produk',
            'Total Harga',
            'Total Bayar',
            'Total Diskon Poin',
            'Total Kembalian',
            'Tanggal Pembelian',
        ];
    }
}
