<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\Product;
use App\Models\Sales;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

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

                // HITUNG HARGA SATUAN (AMAN)
                $price = $qty > 0 ? (int) round($subtotal / $qty) : 0;

                return [
                    'name' => $productData['nama'] ?? '-',
                    'qty' => $qty,
                    'price' => $price,         // harga satuan
                    'subtotal' => $subtotal,   // subtotal asli
                ];
            })->values();

            $member = $items->first()?->member;

            return [
                'invoice_number' => $invoiceNumber,
                'subtotal' => $items->sum('subtotal'), // total bayar
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

        return view('petugas.pembelian.index', compact('purchases', 'totalSubtotal'));
    }

    public function create()
    {
        $products = Product::all();
        return view("petugas.pembelian.create", compact('products'));
    }

    public function detail(Request $request)
    {
        $cartData = json_decode($request->input('cart_data'), true) ?? [];
        session(['cart_data' => $cartData]);
        return view('petugas.pembelian.detail_pembelian', compact('cartData'));
    }

    // ================= NON MEMBER =================
    public function receiptNonMember()
    {
        $cartData = session('cart_data', []);
        $sales = Sales::latest()->first();

        $subtotal = array_sum(array_column($cartData, 'subtotal'));
        $totalPaid = $sales?->total_paid ?? $subtotal;
        $kembalian = max($totalPaid - $subtotal, 0);

        return view("petugas.pembelian.receipt_non_member", compact('cartData', 'sales', 'kembalian', 'subtotal'));
    }

    public function storeNonMember(Request $request)
    {
        try {
            $cartData = session('cart_data', []);
            $request->merge(['price' => (int) str_replace(['.', ','], '', $request->price)]);
            $request->validate(['price' => 'required|integer|min:1|max:9000000000']);

            $invoiceNumber = 'INV-' . strtoupper(Str::random(8));

            foreach ($cartData as $item) {
                $product = Product::find($item['id']);
                if ($product) {
                    $product->stock -= $item['jumlah'];
                    $product->save();
                }

                Sales::create([
                    'invoice_number' => $invoiceNumber,
                    'name' => 'Non Member',
                    'product_id' => $item['id'],
                    'member_id' => null,
                    'product_data' => json_encode($item),
                    'quantity' => $item['jumlah'],
                    'subtotal' => $item['subtotal'],
                    'total_paid' => $request->price,
                    'made_by' => Auth::user()->name,
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

    // ================= MEMBER =================
    public function memberPage()
    {
        $cartData = session('cart_data', []);
        $sales = Sales::latest()->first();
        $members = $sales?->member;
        $points = $members?->points ?? 0;

        $hasPreviousPurchase = $members
            ? Sales::where('member_id', $members->id)->where('id', '!=', $sales?->id)->exists()
            : false;

        return view('petugas.pembelian.detail_member', compact('cartData', 'sales', 'members', 'points', 'hasPreviousPurchase'));
    }

    public function receiptMember()
    {
        $cartData = session('cart_data', []);
        $memberId = session('member_id');
        $subtotalAkhir = session('subtotal_akhir', 0);
        $points = session('used_points');

        $sales = $memberId ? Sales::where('member_id', $memberId)->latest()->first() : null;
        $members = $memberId ? Member::find($memberId) : null;

        $subtotal = array_sum(array_column($cartData, 'subtotal'));
        $totalPaid = $sales?->total_paid ?? $subtotal;
        $diskonMember = $sales?->diskon_member ?? 0;
        $kembalian = max($totalPaid - $subtotalAkhir, 0);

        return view('petugas.pembelian.receipt_member', compact(
            'cartData', 'sales', 'members', 'subtotal', 'totalPaid', 'kembalian', 'subtotalAkhir', 'diskonMember', 'points'
        ));
    }

    public function storeMember(Request $request)
    {
        DB::beginTransaction();

        try {
            $cartData = session('cart_data', []);

            // Normalisasi price (kalau dari input format ribuan)
            $request->merge([
                'price' => (int) str_replace(['.', ','], '', $request->price)
            ]);

            $request->validate([
                'phone_number' => 'required|string',
                'email'        => 'nullable|email|max:255',
                'price'        => 'required|integer|min:1|max:9000000000',
                'name'         => 'nullable|string|max:255',
            ]);

            // ================= MEMBER =================
            $member = Member::firstOrCreate(
                ['phone_number' => $request->phone_number],
                [
                    'email'       => $request->email,
                    'name'        => $request->name ?? 'Member Baru',
                    'member_code' => 'MEM-' . strtoupper(Str::random(8)),
                    'join_in'     => now(),
                    'points'      => 0,
                ]
            );

            // ================= INVOICE =================
            $invoiceNumber = 'INV-' . strtoupper(Str::random(8));
            $totalSubtotal = array_sum(array_column($cartData, 'subtotal'));
            $pointsEarned  = floor($totalSubtotal / 1000);

            // ================= LOOP CART =================
            foreach ($cartData as $item) {

                $product = Product::lockForUpdate()->find($item['id']);

                if (!$product) {
                    throw new \Exception("Produk tidak ditemukan");
                }

                if ($product->stock < $item['jumlah']) {
                    throw new \Exception("Stock {$product->name} tidak mencukupi");
                }

                // Kurangi stock
                $product->stock -= $item['jumlah'];
                $product->save();

                // Simpan sales
                Sales::create([
                    'invoice_number' => $invoiceNumber,
                    'name'           => $member->name,
                    'product_id'     => $item['id'],
                    'member_id'      => $member->id,
                    'product_data'   => json_encode($item),
                    'quantity'       => $item['jumlah'],
                    'subtotal'       => $item['subtotal'],
                    'total_paid'     => $request->price,
                    'made_by'        => Auth::user()->name,
                ]);
            }

            // ================= POINT MEMBER =================
            $member->points += $pointsEarned;
            $member->save();

            DB::commit();

            return response()->json([
                'success'  => true,
                'redirect' => route('petugas.pembelian.member'),
                'message'  => 'Pembelian berhasil disimpan untuk Member'
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function simpanMember(Request $request)
    {
        $cartData = session('cart_data', []);
        $sales = Sales::all();

        $request->validate([
            'nama' => 'required|string',
            'total_bayar' => 'required|numeric',
            'member_id' => 'required|integer',
            'gunakan_poin' => 'nullable|boolean',
        ]);

        DB::beginTransaction();

        try {
            $member = Member::findOrFail($request->member_id);

            $subTotal = array_sum(array_column($cartData, 'subtotal'));
            $gunakanPoin = $request->boolean('gunakan_poin');
            $poinDigunakan = 0;
            $subtotalAkhir = $subTotal;

            if ($gunakanPoin && $member->points > 0) {
                $poinDigunakan = min($member->points, $subTotal);
                $subtotalAkhir = $subTotal - $poinDigunakan;
                $member->points -= $poinDigunakan;
                session(['used_points' => $poinDigunakan]);

                foreach ($cartData as &$item) {
                    $item['diskon_member'] = $poinDigunakan;
                    $item['subtotal_akhir'] = max($item['subtotal'] - $poinDigunakan, 0);
                }
                unset($item);
            } else {
                session()->forget('used_points');
                foreach ($cartData as &$item) {
                    $item['diskon_member'] = 0;
                    $item['subtotal_akhir'] = $item['subtotal'];
                }
                unset($item);
            }

            $member->name = $request->nama;
            $member->save();

            $lastSale = Sales::where('member_id', $member->id)
                            ->latest('created_at')
                            ->first();

            if ($lastSale) {
                $invoiceNumber = $lastSale->invoice_number;
                $oldSales = Sales::where('invoice_number', $invoiceNumber)->get();
                foreach ($oldSales as $old) {
                    $product = Product::find($old->product_id);
                    if ($product) {
                        $product->stock += $old->quantity;
                        $product->save();
                    }
                    $old->delete();
                }
            } else {
                $invoiceNumber = 'INV-' . strtoupper(Str::random(8));
            }

            $totalPaid = $lastSale->total_paid;

            foreach ($cartData as $item) {
                $product = Product::lockForUpdate()->find($item['id']);
                if (!$product) throw new \Exception("Produk {$item['nama']} tidak ditemukan");

                if ($product->stock < $item['jumlah']) throw new \Exception("Stock {$product->name} tidak mencukupi");

                // kurangi stock
                $product->stock -= $item['jumlah'];
                $product->save();

                $sale = Sales::create([
                    'invoice_number' => $invoiceNumber,
                    'name'           => $member->name,
                    'product_id'     => $item['id'],
                    'member_id'      => $member->id,
                    'product_data'   => json_encode($item),
                    'quantity'       => $item['jumlah'],
                    'subtotal'       => $item['subtotal'],
                    'subtotal_akhir' => $item['subtotal_akhir'],
                    'diskon_member'  => $item['diskon_member'],
                    'total_paid'     => $totalPaid,
                    'made_by'        => Auth::user()->name,
                ]);
            }

            session([
                'member_id' => $member->id,
                'subtotal_akhir' => $subtotalAkhir,
                'cart_data' => $cartData,
            ]);

            DB::commit();

            return redirect()->route('petugas.pembelian.receipt_member')
                            ->with('success', 'Transaksi berhasil disimpan/diupdate.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }


    // ================= EXPORT PDF =================
    public function exportPdf()
    {
        $latestInvoice = Sales::latest('created_at')->first();

        if (!$latestInvoice) {
            return redirect()->back()->with('error', 'Tidak ada data transaksi.');
        }

        $sales = Sales::where('invoice_number', $latestInvoice->invoice_number)->get();

        $member = null;
        $usedPoints = session('used_points', 0);

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
        $totalAfterDiscount = max($subtotal - $usedPoints, 0);
        $totalPaid = $latestInvoice->total_paid;
        $kembalian = max($totalPaid - $totalAfterDiscount, 0);

        $data = [
            'sales' => $latestInvoice,
            'cartData' => $cartData,
            'subtotal' => $subtotal,
            'totalAfterDiscount' => $totalAfterDiscount,
            'totalPaid' => $totalPaid,
            'kembalian' => $kembalian,
            'user' => Auth::user(),
            'member' => $member,
            'points' => $usedPoints
        ];

        return Pdf::loadView('petugas.pembelian.export_pdf', $data)
            ->setPaper('a4', 'portrait')
            ->download('invoice_' . $latestInvoice->invoice_number . '.pdf');
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
        $usedPoints = session('used_points',0);
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