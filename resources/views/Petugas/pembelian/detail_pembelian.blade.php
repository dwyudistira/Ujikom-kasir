<x-app-layout>
    @php
        $totalBelanja = collect($cartData)->sum('subtotal');
    @endphp

    <form id="checkout-form" method="POST">
        @csrf

        <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-4xl mx-auto flex gap-8">
            {{-- KIRI --}}
            <div class="flex-1">
                <h2 class="text-xl font-bold mb-4">Produk yang dipilih</h2>

                @forelse ($cartData as $item)
                    <div class="border-b pb-2 mb-2">
                        <p class="font-semibold">{{ $item['nama'] }}</p>
                        <p>
                            Rp {{ number_format($item['subtotal'], 0, ',', '.') }}
                            × {{ $item['jumlah'] }}
                        </p>
                    </div>
                @empty
                    <p class="text-gray-500">Tidak ada produk</p>
                @endforelse

                <div class="flex justify-between font-bold text-lg pt-4">
                    <span>Total</span>
                    <span>Rp {{ number_format($totalBelanja, 0, ',', '.') }}</span>
                </div>
            </div>

            {{-- KANAN --}}
            <div class="flex-1 space-y-4">
                <div>
                    <label class="block font-bold">Status Pembeli</label>
                    <select id="member-status" name="status"
                        class="w-full p-2 border rounded mt-1">
                        <option value="non-member">Non-Member</option>
                        <option value="member">Member</option>
                    </select>
                </div>

                <div id="member-form" class="hidden space-y-4">
                    <div>
                        <label class="block font-bold">No Telepon Member</label>
                        <input
                            type="text"
                            name="phone_number"
                            class="w-full p-2 border rounded mt-1"
                            placeholder="08xxxxxxxxxx"
                        >
                    </div>

                    <div>
                        <label class="block font-bold">Email Member</label>
                        <input
                            type="email"
                            name="email"
                            class="w-full p-2 border rounded mt-1"
                            placeholder="email@contoh.com"
                            >
                        </div>
                    </div>
                    
                    <div>
                        <label class="block font-bold">Harga Bayar</label>
                        <div class="relative mt-1">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-600">
                            Rp
                        </span>
                        <input
                        type="text"
                        id="price"
                        class="w-full pl-10 pr-4 py-2 border rounded focus:ring focus:ring-indigo-200"
                        autocomplete="off"
                        placeholder="9.000.000"
                        >
                    </div>
                </div>

                <button
                    type="submit"
                    id="pesan"
                    class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 w-full">
                    Pesan
                </button>
            </div>
        </div>
    </form>

    {{-- SWEETALERT --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- SCRIPT --}}
    <script>
        const TOTAL_BELANJA = {{ $totalBelanja }};
        const MAX_BAYAR = 9_000_000_000;

        document.addEventListener("DOMContentLoaded", () => {
            const priceInput   = document.getElementById("price");
            const memberStatus = document.getElementById("member-status");
            const memberForm   = document.getElementById("member-form");
            const form         = document.getElementById("checkout-form");
            const submitBtn    = document.getElementById("pesan");

            let hasAlertedMax = false;

            /* SweetAlert Toast */
            const Toast = Swal.mixin({
                toast: true,
                position: "top-end",
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });

            /* Toggle member */
            memberStatus.addEventListener("change", () => {
                memberForm.classList.toggle("hidden", memberStatus.value !== "member");
            });

            /* Input harga */
            priceInput.addEventListener("input", () => {
                let raw = priceInput.value.replace(/\D/g, "");
                let number = parseInt(raw || 0);

                if (number > MAX_BAYAR) {
                    number = MAX_BAYAR;

                    if (!hasAlertedMax) {
                        Toast.fire({
                            icon: "warning",
                            title: "Pembayaran maksimal Rp 9.000.000.000"
                        });
                        hasAlertedMax = true;
                    }
                } else {
                    hasAlertedMax = false;
                }

                priceInput.dataset.raw = number;
                priceInput.value = number
                    ? new Intl.NumberFormat("id-ID").format(number)
                    : "";
            });

            /* Submit */
            form.addEventListener("submit", async (e) => {
                e.preventDefault();

                const bayar = parseInt(priceInput.dataset.raw || 0);

                if (bayar <= 0) {
                    Toast.fire({ icon: "error", title: "Harga bayar wajib diisi" });
                    return;
                }

                if (bayar < TOTAL_BELANJA) {
                    Toast.fire({ icon: "error", title: "Harga bayar kurang dari total belanja" });
                    return;
                }

                submitBtn.disabled = true;
                submitBtn.textContent = "Memproses...";

                const url = memberStatus.value === "member"
                    ? "{{ route('petugas.pembelian.memberPage') }}"
                    : "{{ route('petugas.pembelian.receipt_store') }}";

                const formData = new FormData(form);
                formData.set("price", bayar);

                try {
                    const res = await fetch(url, {
                        method: "POST",
                        headers: {
                            "X-CSRF-TOKEN": "{{ csrf_token() }}",
                            "Accept": "application/json",
                        },
                        body: formData,
                    });

                    const data = await res.json();

                    if (data.redirect) {
                        window.location.href = data.redirect;
                    } else {
                        Swal.fire({
                            icon: "success",
                            title: "Transaksi berhasil",
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => location.reload());
                    }
                } catch {
                    Swal.fire("Error", "Terjadi kesalahan sistem", "error");
                } finally {
                    submitBtn.disabled = false;
                    submitBtn.textContent = "Pesan";
                }
            });
        });
    </script>
</x-app-layout>
