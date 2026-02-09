<x-app-layout>
    <div class="max-w-4xl mx-auto bg-white p-8 rounded-xl shadow-lg border border-gray-100">
        <div class="flex justify-between mb-8 items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">INVOICE</h1>
                <div class="flex items-center mt-2">
                    <span class="bg-blue-100 text-blue-800 text-sm font-semibold px-3 py-1 rounded-full">
                        #{{ $sales?->invoice_number ?? '-' }}
                    </span>
                    <span class="text-gray-500 ml-3">{{ now()->format('d F Y') }}</span>
                </div>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('petugas.pembelian.export-pdf') }}" class="flex items-center bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-all duration-200 shadow-md hover:shadow-lg">
                    Unduh PDF
                </a>
            </div>
        </div>

        <div class="mb-8 overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b-2 border-gray-200">
                        <th class="text-left pb-3 text-lg font-semibold text-gray-700">Produk</th>
                        <th class="text-right pb-3 text-lg font-semibold text-gray-700">Harga</th>
                        <th class="text-right pb-3 text-lg font-semibold text-gray-700">Quantity</th>
                        <th class="text-right pb-3 text-lg font-semibold text-gray-700">Sub Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cartData ?? [] as $item)
                    <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                        <td class="py-4 text-gray-800 font-medium">{{ $item['nama'] ?? '-' }}</td>
                        <td class="text-right text-gray-600">Rp. {{ number_format($item['subtotal'] ?? 0, 0, ',', '.') }}</td>
                        <td class="text-right text-gray-600">{{ $item['jumlah'] ?? 0 }}</td>
                        <td class="text-right text-blue-600 font-semibold">Rp. {{ number_format($item['subtotal'] ?? 0, 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center text-gray-500 py-4">Tidak ada data produk</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="grid grid-cols-2 gap-6 mb-6">
            <div class="bg-blue-50 p-4 rounded-lg border border-blue-100">
                <p class="text-sm font-medium text-blue-800 mb-1">POIN DIGUNAKAN</p>
                <p id="poin-used" class="text-2xl font-bold text-blue-600">{{ number_format($points ?? 0, 0, ',', '.')  }}</p>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 text-right">
                <p class="text-sm font-medium text-gray-700 mb-1">KASIR</p>
                <p class="text-xl font-semibold text-gray-800">{{ Auth::user()->name ?? '-' }}</p>
            </div>
        </div>

        <div class="bg-gray-50 p-6 rounded-xl border border-gray-200 space-y-3">
            <div class="flex justify-between items-start">
                <span class="text-lg font-medium text-gray-700">TOTAL</span>

                <div class="text-right">
                    {{-- TOTAL AWAL (dicoret kalau pakai poin) --}}
                    <span class="block text-lg font-semibold {{ ($points ?? 0) > 0 ? 'line-through text-gray-400' : 'text-gray-800' }}">
                        Rp. {{ number_format($subtotal ?? $subtotalAkhir ?? 0, 0, ',', '.') }}
                    </span>

                    {{-- TOTAL SETELAH POTONG POIN --}}
                    @if(($points ?? 0) > 0)
                        <span class="block text-lg font-bold text-blue-600">
                            Rp. {{ number_format($subtotalAkhir ?? 0, 0, ',', '.') }}
                        </span>
                    @endif
                </div>
            </div>

            <div class="flex justify-between items-center">
                <span class="text-lg font-medium text-gray-700">TOTAL BAYAR</span>
                <span id="total-bayar" class="text-lg font-semibold text-gray-800">
                    {{ ($totalPaid ?? 0) == 0 ? 'FREE' : 'Rp. ' . number_format($totalPaid ?? 0, 0, ',', '.') }}
                </span>
            </div>

            <div class="flex justify-between items-center pt-4 border-t border-gray-200">
                <span class="text-2xl font-bold text-gray-800">KEMBALIAN</span>
                <span id="kembalian" class="text-2xl font-bold text-blue-600">
                    Rp. {{ number_format($kembalian ?? 0, 0, ".", ".") }}
                </span>
            </div>
        </div>
    </div>
</x-app-layout>