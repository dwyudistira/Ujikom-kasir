<x-app-layout>
    <div class="container mt-4">
        <div class="row justify-content-center">
                <div class="col-md-4 mb-4">
                    <div class="card shadow-sm border-0 p-3 text-center">

                            <img src="#"
                                 class="card-img-top mx-auto d-block" 
                                 style="width: 250px; height: 150px; object-fit: cover; border-radius: 10px;" 
                                 alt="#">

                            <div class="d-flex align-items-center justify-content-center bg-light"
                                 style="width: 150px; height: 150px; border-radius: 10px;">
                                <span>No Image</span>
                            </div>

                        <div class="card-body">
                            <h5 class="card-title">#</h5>
                            <p class="text-muted">Stok: <span id="stok">#</span></p>
                            <p class="fw-bold text-primary">Rp. </p>

                            <div class="d-flex align-items-center justify-content-center">
                                <button class="btn btn-sm me-2"  type="button">-</button>
                                <span id="jumlah" class="fw-bold">0</span>
                                <button class="btn btn-sm ms-2"  type="button">+</button>
                            </div>

                            <p class="mt-2">Sub Total: <strong>Rp. <span id="subtotal">0</span></strong></p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="text-center mt-4">
        <form id="cartForm" method="POST" action="#">
            @csrf
            <input type="hidden" name="cart_data" id="cartData">
            <button type="submit" onclick="kirimDataKeHalamanBerikutnya()" class="btn btn-primary">Selanjutnya</button>
        </form>
    </div>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</x-app-layout>
