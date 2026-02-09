<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Daftar Penjualan') }}
        </h2>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 bg-gray-50">
        <div class="bg-white shadow-xl sm:rounded-lg p-6">
            @if(session('success'))
                <div class="mb-6 p-4 bg-green-50 text-green-700 rounded-lg font-medium">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mb-6 p-4 bg-red-50 text-red-700 rounded-lg font-medium">
                    {{ session('error') }}
                </div>
            @endif

            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-semibold text-gray-800">Data Penjualan</h3>
                <div class="space-x-3">
                    <a href="{{ route('petugas.pembelian.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white py-2 px-4 rounded-md transition flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                        Tambah Produk
                    </a>
                </div>
            </div>

            <form method="GET" action="{{ route('petugas.pembelian') }}" class="space-x-3 mb-6">
                <div class="flex space-x-2">
                    <select name="day" class="form-select rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Hari</option>
                        @for($i = 1; $i <= 31; $i++)
                            <option value="{{ $i }}" {{ request('day') == $i ? 'selected' : '' }}>{{ $i }}</option>
                        @endfor
                    </select>

                    <select name="month" class="form-select rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Bulan</option>
                        @foreach(range(1, 12) as $month)
                            <option value="{{ $month }}" {{ request('month') == $month ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $month, 10)) }}</option>
                        @endforeach
                    </select>

                    <select name="year" class="form-select rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Tahun</option>
                        @foreach(range(date('Y') - 5, date('Y')) as $year)
                            <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>{{ $year }}</option>
                        @endforeach
                    </select>

                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-md transition">
                        Filter
                    </button>
                </div>
            </form>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 bg-white shadow-sm rounded-md">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-6 py-4 text-sm text-gray-600">#</th>
                            <th class="px-6 py-4 text-sm text-gray-600">No. Invoice</th>
                            <th class="px-6 py-4 text-sm text-gray-600">Nama Pelanggan</th>
                            <th class="px-6 py-4 text-sm text-gray-600">Tanggal</th>
                            <th class="px-6 py-4 text-sm text-gray-600">Total Harga</th>
                            <th class="px-6 py-4 text-sm text-gray-600">Dibuat Oleh</th>
                            <th class="px-6 py-4 text-sm text-gray-600">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($purchases as $purchase)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">{{ $loop->iteration }}</td>
                            <td class="px-6 py-4">{{ $purchase['invoice_number'] }}</td>
                            <td class="px-6 py-4">{{ $purchase['name'] }}</td>
                            <td class="px-6 py-4">{{ $purchase['created_at']->format('d-m-Y') }}</td>
                            <td class="px-6 py-4">Rp {{ number_format($purchase['subtotal'], 0, ',', '.') }}</td>
                            <td class="px-6 py-4">{{ $purchase['made_by'] }}</td>
                            <td class="px-6 py-4">
                                <div class="flex space-x-2">
                                        <button
                                            type="button"
                                            class="text-indigo-600 hover:text-indigo-900 bg-indigo-50 px-3 py-1 rounded-md text-sm flex items-center"
                                            data-bs-toggle="modal"
                                            data-bs-target="#detailPenjualanModal"
                                            data-name="{{ $purchase['name'] }}"
                                            data-phone="{{ $purchase['phone'] }}"
                                            data-poin="{{ $purchase['poin'] }}" 
                                            data-date="{{ $purchase['created_at']->format('d-m-Y H:i:s') }}"
                                            data-user="{{ $purchase['made_by'] }}"
                                            data-products='@json($purchase["products"])'
                                            data-joined-date="{{ $purchase['joined_date'] }}"
                                            data-total-paid="{{ $purchase['total_paid'] }}"
                                            data-diskon-member="{{ $purchase['diskon_member'] }}"
                                        >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        Lihat
                                    </button>

                                    <a href="{{ route('petugas.pembelian.export-pdf-id', $purchase['invoice_number']) }}" class="text-amber-600 hover:text-amber-900 bg-amber-50 px-3 py-1 rounded-md text-sm flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                        </svg>
                                        Unduh
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                {{ $purchases->links() }}
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="detailPenjualanModal" tabindex="-1" aria-labelledby="detailPenjualanLabel" aria-hidden="true">
        <div class="modal-dialog" style="max-width: 40%;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Penjualan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex justify-content-between">
                    <p><strong>Poin Member:</strong> <span id="modal-poin">-</span></p>
                    <p><strong>Bergabung Sejak:</strong> <span id="modal-joined-date">-</span></p>
                </div>
                <p><strong>No. HP:</strong> <span id="modal-phone">-</span></p>

                    <table class="table mt-3">
                        <thead>
                            <tr>
                                <th>Nama Produk</th>
                                <th>Qty</th>
                                <th>Harga</th>
                                <th>Sub Total</th>
                            </tr>
                        </thead>
                        <tbody id="modal-product-body">
                            <!-- Data produk akan diisi JS -->
                        </tbody>
                    </table>

                    <div class="text-end mt-3">
                        <strong>Total:</strong> <span id="modal-total">Rp 0</span>
                    </div>

                    <div class="text-xs text-gray-500 mt-4 text-center">
                        <p>Dibuat pada: <span id="modal-date"></span></p>
                        <p>Oleh: <span id="modal-user"></span></p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        const detailModal = document.getElementById('detailPenjualanModal');

        detailModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;

            const products = JSON.parse(button.getAttribute('data-products') || '[]');
            const phone = button.getAttribute('data-phone');
            const poin = button.getAttribute('data-poin');
            const date = button.getAttribute('data-date');
            const user = button.getAttribute('data-user');
            const joinedDate = button.getAttribute('data-joined-date');
            const totalPaid = Number(button.getAttribute('data-total-paid')) || 0;
            const diskonMember = Number(button.getAttribute('data-diskon-member')) || 0;

            document.getElementById('modal-poin').textContent =
                poin ? Number(poin).toLocaleString('id-ID') : '-';
            document.getElementById('modal-joined-date').textContent = joinedDate ?? '-';
            document.getElementById('modal-phone').textContent = phone ?? '-';
            document.getElementById('modal-date').textContent = date ?? '-';
            document.getElementById('modal-user').textContent = user ?? '-';

            let subtotal = 0;
            let productRows = '';

            products.forEach(product => {
                const qty = Number(product.qty) || 0;
                const price = Number(product.price) || 0;

                const subTotal = qty * price;
                subtotal += subTotal;

                productRows += `
                    <tr>
                        <td>${product.name ?? '-'}</td>
                        <td>${qty}</td>
                        <td>Rp ${price.toLocaleString('id-ID')}</td>
                        <td>Rp ${subTotal.toLocaleString('id-ID')}</td>
                    </tr>
                `;
            });

            const totalAfterDiscount = subtotal - diskonMember;
            const kembalian = totalPaid - totalAfterDiscount;

            document.getElementById('modal-product-body').innerHTML =
                productRows || `<tr><td colspan="4" class="text-center">Tidak ada produk</td></tr>`;

            document.getElementById('modal-total').innerHTML = `
                <p><strong>SUBTOTAL:</strong> Rp ${subtotal.toLocaleString('id-ID')}</p>
                <p><strong>DISKON POIN:</strong> - Rp ${diskonMember.toLocaleString('id-ID')}</p>
                <p><strong>TOTAL:</strong> Rp ${totalAfterDiscount.toLocaleString('id-ID')}</p>
                <p><strong>PEMBAYARAN:</strong> Rp ${totalPaid.toLocaleString('id-ID')}</p>
                <p><strong>KEMBALIAN:</strong> Rp ${kembalian.toLocaleString('id-ID')}</p>
            `;
        });
    </script>

</x-app-layout>
