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
        // Gunakan hanya data yang sudah difilter
        $this->groupedSales = $filteredSales->groupBy('invoice_number');
    
        // Preload semua member untuk menghindari N+1
        $this->members = Member::all()->keyBy('id');
    }
    

    public function collection()
    {
        // Ambil satu sales per invoice untuk representasi baris export
        return $this->groupedSales->map(function ($items) {
            return $items->first();
        })->values();
    }

    public function map($sale): array
    {
        // Ambil semua sales untuk invoice yang sama
        $salesByInvoice = $this->groupedSales[$sale->invoice_number];

        // Ambil data member dari koleksi preload
        $member = $this->members[$sale->member_id] ?? null;

        // Format produk
        $produkStr = '';
        foreach ($salesByInvoice as $item) {
            $product = json_decode($item->product_data, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($product)) {
                $nama = $product['nama'] ?? 'Produk';
                $jumlah = $product['jumlah'] ?? 0;
                $subtotal = $product['subtotal'] ?? 0;
                $produkStr .= "{$nama} ( {$jumlah} : Rp. " . number_format($subtotal, 0, ',', '.') . " ), ";
            }
        }
        $produkStr = rtrim($produkStr, ', ');

        // Hitung total
        $subtotal = $salesByInvoice->sum('subtotal');
        $totalPaid = $sale->total_paid ?? 0;
        $diskonPoin = $sale->total_discount ?? 0;
        $kembalian = $totalPaid - $subtotal;

        return [
            $member->name ?? 'Bukan Member',
            $member->phone_number ?? '-',
            $member->points ?? '-',
            $produkStr,
            'Rp. ' . number_format($subtotal, 0, ',', '.'),
            'Rp. ' . number_format($totalPaid, 0, ',', '.'),
            'Rp. ' . number_format($diskonPoin, 0, ',', '.'),
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
