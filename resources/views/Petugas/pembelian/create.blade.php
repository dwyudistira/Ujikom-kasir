<x-app-layout>
    {{-- Alert Messages --}}
    @if(session('success'))
        <div class="alert alert-success text-center">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger text-center">
            {{ session('error') }}
        </div>
    @endif

    {{-- Products Grid --}}
    <div class="container mt-4">
        <div class="row justify-content-center">
            @foreach ($products as $product)
                <div class="col-md-4 mb-4">
                    <div class="card shadow-sm border-0 p-3 text-center">
                        @if ($product->image)
                            <img src="{{ asset('storage/' . $product->image) }}" 
                                 class="card-img-top mx-auto d-block" 
                                 style="width: 250px; height: 150px; object-fit: cover; border-radius: 10px;" 
                                 alt="{{ $product->name }}">
                        @else
                            <div class="d-flex align-items-center justify-content-center bg-light"
                                 style="width: 150px; height: 150px; border-radius: 10px;">
                                <span>No Image</span>
                            </div>
                        @endif

                        <div class="card-body">
                            <h5 class="card-title">{{ $product->name }}</h5>
                            <p class="text-muted">Stok: <span id="stok-{{ $product->id }}">{{ $product->stock }}</span></p>
                            <p class="fw-bold text-primary">Rp. {{ number_format($product->price, 0, ',', '.') }}</p>

                            <div class="d-flex justify-content-center align-items-center mb-2">
                                <button
                                    id="btn-minus-{{ $product->id }}"
                                    class="btn-control"
                                    onclick="kurangi({{ $product->id }}, {{ $product->price }}, '{{ $product->name }}')"
                                    type="button"
                                    disabled
                                >-</button>

                                <span id="jumlah-{{ $product->id }}" class="fw-bold mx-2">0</span>

                                <button
                                    id="btn-plus-{{ $product->id }}"
                                    class="btn-control"
                                    onclick="tambah({{ $product->id }}, {{ $product->price }}, '{{ $product->name }}')"
                                    type="button"
                                >+</button>
                            </div>

                            <p>Sub Total: <strong>Rp. <span id="subtotal-{{ $product->id }}">0</span></strong></p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Checkout Sticky Section --}}
    <div id="checkout-sticky">
        <div class="text-center mt-4">
            <h5>Total Pembelian</h5>
            <h4 class="fw-bold text-success">Rp. <span id="grandTotal">0</span></h4>
        </div>

        <div class="text-center mt-4">
            <form id="cartForm" method="POST" action="{{ route('petugas.pembelian.detail') }}">
                @csrf
                <input type="hidden" name="cart_data" id="cartData">
                <button type="submit" onclick="kirimDataKeHalamanBerikutnya()" class="btn btn-primary">
                    Selanjutnya
                </button>
            </form>
        </div>
    </div>

    <script>
        let cart = {};

        function hitungTotal() {
            let total = 0;
            Object.values(cart).forEach(item => total += item.subtotal);
            document.getElementById("grandTotal").textContent = total.toLocaleString("id-ID");
        }

        function updateButtonState(id) {
            const jumlah = parseInt(document.getElementById("jumlah-" + id).textContent);
            const stok = parseInt(document.getElementById("stok-" + id).textContent);

            document.getElementById("btn-minus-" + id).disabled = jumlah === 0;
            document.getElementById("btn-plus-" + id).disabled = jumlah >= stok;
        }

        function tambah(id, harga, nama) {
            let stok = parseInt(document.getElementById("stok-" + id).textContent);
            let jumlahSpan = document.getElementById("jumlah-" + id);
            let subtotalSpan = document.getElementById("subtotal-" + id);

            let jumlah = parseInt(jumlahSpan.textContent);
            if (jumlah >= stok) return;

            jumlah++;
            jumlahSpan.textContent = jumlah;

            let subtotal = jumlah * harga;
            subtotalSpan.textContent = subtotal.toLocaleString("id-ID");

            cart[id] = { id, nama, jumlah, subtotal };
            localStorage.setItem("cart", JSON.stringify(cart));

            updateButtonState(id);
            hitungTotal();
        }

        function kurangi(id, harga, nama) {
            let jumlahSpan = document.getElementById("jumlah-" + id);
            let subtotalSpan = document.getElementById("subtotal-" + id);

            let jumlah = parseInt(jumlahSpan.textContent);
            if (jumlah <= 0) return;

            jumlah--;
            jumlahSpan.textContent = jumlah;

            let subtotal = jumlah * harga;
            subtotalSpan.textContent = subtotal.toLocaleString("id-ID");

            if (jumlah === 0) {
                delete cart[id];
            } else {
                cart[id] = { id, nama, jumlah, subtotal };
            }

            localStorage.setItem("cart", JSON.stringify(cart));

            updateButtonState(id);
            hitungTotal();
        }

        function kirimDataKeHalamanBerikutnya() {
            document.getElementById("cartData").value = JSON.stringify(cart);
        }
    </script>

    {{-- Bootstrap --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</x-app-layout>

<style>
    #checkout-sticky {
        position: sticky;
        bottom: 0;
        background: white;
        z-index: 10;
        padding-bottom: 15px;
    }
    .btn-control {
        width: 35px;
        height: 35px;
        padding: 0;
        font-size: 1.2rem;
    }
</style>