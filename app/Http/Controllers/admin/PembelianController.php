<?php

namespace App\Http\Controllers\admin;

use App\Exports\SalesExport;
use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\Sales;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class PembelianController extends Controller
{
    public function index()
    {
        $query = Sales::with('member');

        if ($day = request('day')) {
            $query->whereDay('created_at', $day);
        }

        if ($month = request('month')) {
            $query->whereMonth('created_at', $month);
        }

        if ($year = request('year')) {
            $query->whereYear('created_at', $year);
        }

        $sales = $query->get()->groupBy('invoice_number');

        $collection = $sales->map(function ($items, $invoiceNumber) {

            $products = $items->map(function ($item) {
                $productData = json_decode($item->product_data, true) ?? [];

                $qty = (int) ($item->quantity ?? 0);
                $subtotal = (int) ($item->subtotal ?? 0);

                $price = $qty > 0 ? (int) round($subtotal / $qty) : 0;

                return [
                    'name' => $productData['nama'] ?? '-',
                    'qty' => $qty,
                    'price' => $price,
                    'subtotal' => $subtotal,
                ];
            })->values();

            $member = $items->first()?->member;

            return [
                'invoice_number' => $invoiceNumber,
                'subtotal' => $items->sum('subtotal'),
                'name' => $items->first()?->name ?? 'Non-Member',
                'created_at' => $items->first()?->created_at,
                'made_by' => $items->first()?->made_by,
                'products' => $products,
                'phone' => $member?->phone_number ?? '-',
                'poin' => $member?->points ?? 0,
                'joined_date' => $member
                    ? $member->created_at->format('d-m-Y')
                    : '-',
                'total_paid' => $items->first()?->total_paid ?? $items->sum('subtotal'),
                'diskon_member' => $items->first()?->diskon_member ?? 0,
            ];
        })->values();

        $perPage = 10;
        $currentPage = request()->get('page', 1);

        $purchases = new LengthAwarePaginator(
            $collection->slice(($currentPage - 1) * $perPage, $perPage)->values(),
            $collection->count(),
            $perPage,
            $currentPage,
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ]
        );

        $totalSubtotal = $collection->sum('subtotal');

        return view('admin.pembelian.index', compact('purchases', 'totalSubtotal'));
    }
    
    public function export(Request $request)
    {
        $day = $request->input('day');
        $month = $request->input('month');
        $year = $request->input('year');
    
        $purchasesQuery = Sales::query();
    
        if ($day) {
            $purchasesQuery->whereDay('created_at', $day);
        }
    
        if ($month) {
            $purchasesQuery->whereMonth('created_at', $month);
        }
    
        if ($year) {
            $purchasesQuery->whereYear('created_at', $year);
        }
    
        $purchases = $purchasesQuery->get();
    
        return Excel::download(new SalesExport($purchases), 'penjualan.xlsx');

    }
    
    public function exportPdfId($id)
    {
        $sales = Sales::where('invoice_number', $id)->get();

        if ($sales->isEmpty()) {
            return redirect()->back()->with('error', 'Data transaksi tidak ditemukan.');
        }

        $latestInvoice = $sales->first();

        // ================= MEMBER =================
        $member = null;
        if ($latestInvoice->member_id) {
            $member = Member::find($latestInvoice->member_id);
        }

        // ================= CART DATA =================
        $cartData = $sales->map(function ($sale) {
            $product = json_decode($sale->product_data, true);
            return [
                'nama'     => $product['nama'] ?? '-',
                'jumlah'   => $product['jumlah'] ?? 0,
                'subtotal' => $product['subtotal'] ?? 0,
            ];
        });

        // ================= PERHITUNGAN =================
        $subtotal = $sales->sum('subtotal');
        $usedPoints = $latestInvoice->diskon_member ?? 0;
        $totalAfterDiscount = max($subtotal - $usedPoints, 0);
        $totalPaid = $latestInvoice->total_paid;
        $kembalian = max($totalPaid - $totalAfterDiscount, 0);

        // ================= DATA PDF =================
        $data = [
            'sales'               => $latestInvoice,
            'cartData'            => $cartData,
            'subtotal'            => $subtotal,
            'totalAfterDiscount'  => $totalAfterDiscount,
            'totalPaid'           => $totalPaid,
            'kembalian'           => $kembalian,
            'user'                => Auth::user(),
            'member'              => $member,
            'points'              => $usedPoints
        ];

        // ================= EXPORT PDF =================
        $pdf = Pdf::loadView('petugas.pembelian.export_pdf', $data)
            ->setPaper('a4', 'portrait')
            ->setOption('isRemoteEnabled', true)
            ->setOption('defaultFont', 'sans-serif');

        return $pdf->download('invoice_' . $latestInvoice->invoice_number . '.pdf');
    }
}