<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\Product;
use App\Models\Sales;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PembelianController extends Controller
{
    public function index()
    {
        $query = Sales::with('member');
    
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
    
        return view('petugas.pembelian.index', compact('purchases', 'totalSubtotal'));
    }
    
    
    public function create()
    {
        $products = Product::all();
        return view("petugas.pembelian.create", compact('products'));
    }

    public function detail(Request $request)
    {
        $cartData = json_decode($request->input('cart_data'), true);
        session(['cart_data' => $cartData]);
        

        return view('petugas.pembelian.detail_pembelian', compact('cartData'));
    }

    // Non-Member
    public function receiptNonMember() { 
        $cartData = session('cart_data');
    
        $sales = Sales::latest()->first();
    
        $members = Member::latest()->first();
    
        $subtotal = array_sum(array_column($cartData, 'subtotal'));
    
        $kembalian = $sales->total_paid - $subtotal;
    
        return view("petugas.pembelian.receipt_non_member", compact('cartData', 'sales', 'members', 'kembalian', 'subtotal'));
    }
    
    public function storeNonMember(Request $request)
    {
        try {
            $cartData = session('cart_data');

            $request->merge([
                'price' => (int) str_replace(['.', ','], '', $request->price)
            ]);
            

            $request->validate([
                'price' => 'required|integer|min:1|max:1000000000',
            ],[
                'price.max' => 'Pembayaran maksimal hanya boleh Rp1.000.000.000',
            ]);

            $invoiceNumber = 'INV-' . strtoupper(Str::random(8));

            foreach ($cartData as $item) {
                $product = Product::find($item['id']);
                if ($product) {
                    $product->stock -= $item['jumlah'];
                    $product->save();
                }
            
                Sales::create([
                    'invoice_number' => $invoiceNumber,
                    'name'           => 'Non Member',
                    'product_id'     => $item['id'],
                    'member_id'      => null,
                    'product_data'   => json_encode($item),
                    'quantity'       => $item['jumlah'],
                    'subtotal'       => $item['subtotal'],
                    'total_paid'     => $request->price,
                    'made_by'        => Auth::user()->name,
                ]);
            }
            
            return response()->json([
                'success' => true,
                'redirect' => route('petugas.pembelian.receipt'),
                'message' => 'Pembelian berhasil disimpan untuk Non-Member'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    //Member
    public function memberPage(Request $request)
    {
        $cartData = session('cart_data');
    
        $sales = Sales::latest()->first();
    
        $members = Member::find($sales->member_id);
    
        $hasPreviousPurchase = Sales::where('member_id', $sales->member_id)
            ->where('id', '!=', $sales->id)
            ->exists();
    
        return view('petugas.pembelian.detail_member', compact('cartData', 'sales', 'members', 'hasPreviousPurchase'));
    }
    
    public function receiptMember()
    {
        $cartData = session('cart_data');
        
        $sales = Sales::latest()->first();
        $members = Member::latest()->first();
        
        $subtotal = array_sum(array_column($cartData, 'subtotal'));
    
        $points = Member::where('id', $sales->member_id)->value('points');
    
        $kembalian = $sales->total_paid;
    
        $usePoints = request()->has('gunakan_poin') && request()->get('gunakan_poin') == '1';
    
        if ($usePoints && $points > 0) {
            $kembalian = $sales->total_paid - $points;
        }
    
        return view("petugas.pembelian.receipt_member", compact('cartData', 'sales', 'members', 'kembalian', 'subtotal', 'points', 'usePoints'));
    }
    

    public function storeMember(Request $request)
    {
        try {
            $cartData = session('cart_data');

            
            $request->validate([
                'phone_number' => 'required|string',
                'price' => 'required|integer|min:1|max:1000000000',
                'name' => 'nullable|string|max:255',
                'join_in' => 'nullable|date',
            ],[
                'price.max' => 'Pembayaran maksimal hanya boleh Rp1.000.000.000',
            ]);

            $member = DB::table('members')->where('phone_number', $request->phone_number)->first();

            if (!$member) {
                $totalSubtotal = array_sum(array_column($cartData, 'subtotal'));

                $points = $totalSubtotal / 100;
            
                $memberId = DB::table('members')->insertGetId([
                    'phone_number' => $request->phone_number,
                    'name' => $request->name ?? 'Member Baru',
                    'member_code' => 'MEM-' . strtoupper(Str::random(8)),
                    'join_in' => now(),
                    'points' => $points,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $member = DB::table('members')->find($memberId);
            } else {
                $totalSubtotal = array_sum(array_column($cartData, 'subtotal'));
                $points = $totalSubtotal / 100;
                
                DB::table('members')->where('id', $member->id)->update([
                    'points' => $member->points + $points,  
                    'updated_at' => now(),
                ]);
            
                $member = DB::table('members')->find($member->id);
            }
            
            
            $invoiceNumber = 'INV-' . strtoupper(Str::random(8));
            
            foreach ($cartData as $item) {
                Sales::create([
                    'invoice_number' => $invoiceNumber,
                    'name' => $member->name,
                    'product_id' => $item['id'],
                    'member_id' => $member->id,
                    'product_data' => json_encode($item),
                    'quantity' => $item['jumlah'],
                    'subtotal' => $item['subtotal'],
                    'total_paid' => $request->price, 
                    'made_by' => Auth::user()->name,
                ]);
            }
            
            return response()->json([
                'success' => true,
                'redirect' => route('petugas.pembelian.member'),
                'message' => 'Pembelian berhasil disimpan untuk Member'
            ]);

        } catch (\Exception $e) {   
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function simpanMember(Request $request)
    {
        $request->validate([
            'nama' => 'required|string', 
            'poin' => 'required|numeric',
            'total_bayar' => 'required|numeric',
        ]);
    
        $member = Member::find($request->member_id);
        if (!$member) {
            return back()->with('error', 'Member tidak ditemukan.');
        }
    
        $gunakanPoin = $request->has('gunakan_poin');
    
        $poin_value = 100;
        $harga_awal = $request->total_bayar;
        $harga_akhir = $harga_awal;
        $poin_digunakan = 0;
    
        if ($gunakanPoin && $member->points > 0) {
            $maks_potongan = $member->points * $poin_value;
    
            if ($maks_potongan >= $harga_awal) {
                $poin_digunakan = ceil($harga_awal / $poin_value);
                $harga_akhir = 0;
            } else {
                $poin_digunakan = $member->points;
                $harga_akhir = $harga_awal - $maks_potongan;
            }
    
            $member->points -= $poin_digunakan;
        }
    
        $jumlahTransaksi = Sales::where('member_id', $member->id)->count();
        if ($jumlahTransaksi <= 1 && $member->name === 'Member Baru') {
            $member->name = $request->nama;
        }
    
        $member->save();
    
        $sale = Sales::latest()->first(); 
    
        if ($sale && $sale->member_id == $member->id) {
            $sale->total_paid = $harga_akhir;
            $sale->save();
        } else {
            $sale = new Sales();
            $sale->member_id = $member->id;
            $sale->name = $member->name;
            $sale->invoice_number = 'INV-' . strtoupper(uniqid());
            $sale->total_paid = $harga_akhir;
            $sale->save();
        }
    
        return redirect()->route('petugas.pembelian.receipt_member')
                         ->with('success', 'Transaksi berhasil disimpan.');
    }
    
    // Export
    public function exportPdf()
    {
        $latestInvoice = Sales::latest('created_at')->first();
    
        if (!$latestInvoice) {
            return redirect()->back()->with('error', 'Tidak ada data transaksi untuk diexport.');
        }
    
        $sales = Sales::where('invoice_number', $latestInvoice->invoice_number)->get();
    
        $member = null;
        $points = 0;  // Default points
        if ($latestInvoice->member_id) {
            $member = Member::find($latestInvoice->member_id);
            if ($member) {
                $points = $member->points ?? 0;  // Get points from the member table
            }
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
