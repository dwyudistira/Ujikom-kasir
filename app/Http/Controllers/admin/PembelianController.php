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
        $query = Sales::with('member'); // Use Sale if you renamed the model
    
        // Filter by day, month, and year
        if ($day = request('day')) {
            $query->whereDay('created_at', $day);
        }
    
        if ($month = request('month')) {
            $query->whereMonth('created_at', $month);
        }
    
        if ($year = request('year')) {
            $query->whereYear('created_at', $year);
        }
    
        $allSales = $query->get();
    
        $grouped = $allSales->groupBy('invoice_number');
    
        $collection = $grouped->map(function ($items, $invoiceNumber) {
            $products = $items->map(function ($item) {
                $productData = json_decode($item->product_data, true);
    
                return [
                    'name' => $productData['nama'] ?? 'Non-Member', 
                    'qty' => $item->quantity ?? 0, 
                    'price' => $productData['subtotal'] ?? 0, 
                    'subtotal' => $item->subtotal ?? 0, 
                ];
                
            });
    
            $member = $items->first()->member; 
            $joinedDate = $member ? $member->created_at->format('d-m-Y') : '-'; 
            $phone = $member ? $member->phone_number : '-'; 
            $points = $member ? $member->points : 0; 
    
            return [
                'invoice_number' => $invoiceNumber,
                'subtotal' => $items->sum('subtotal'),
                'name' => $items->first()->name,
                'created_at' => $items->first()->created_at,
                'made_by' => $items->first()->made_by,
                'products' => $products,
                'phone' => $phone,
                'poin' => $points, 
                'joined_date' => $joinedDate, 
            ];
        })->values();
    
        $totalSubtotal = $collection->sum('subtotal');
    
        $perPage = 10;
        $currentPage = request()->get('page', 1);
        $currentItems = $collection->slice(($currentPage - 1) * $perPage, $perPage)->values();
    
        $purchases = new LengthAwarePaginator(
            $currentItems,
            $collection->count(),
            $perPage,
            $currentPage,
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ]
        );
    
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
        
        $member = null;
        if ($latestInvoice->member_id) {
            $member = Member::find($latestInvoice->member_id);
        }
    
        $cartData = $sales->map(function ($sale) {
            $product = json_decode($sale->product_data, true);
            return [
                'nama' => $product['nama'] ?? '-',
                'jumlah' => $product['jumlah'] ?? 0,
                'subtotal' => $product['subtotal'] ?? 0,
            ];
        });
    
        $subtotal = $sales->sum('subtotal');
        $totalPaid = $latestInvoice->total_paid;
        $kembalian = $totalPaid - $subtotal;
        $points = $latestInvoice->points ?? 0;
    
        $data = [
            'sales' => $latestInvoice,
            'cartData' => $cartData,
            'subtotal' => $subtotal,
            'totalPaid' => $totalPaid,
            'kembalian' => $kembalian,
            'user' => Auth::user(),
            'member' => $member,
            'points' => $points
        ];
    
        $pdf = Pdf::loadView('petugas.pembelian.export_pdf', $data)
                 ->setPaper('a4', 'portrait')
                 ->setOption('isRemoteEnabled', true)
                 ->setOption('defaultFont', 'sans-serif');
    
        return $pdf->download('invoice_' . $latestInvoice->invoice_number . '.pdf');
    }
}