<x-app-layout>

    <div class="max-w-6xl mx-auto mt-12 p-10 bg-white shadow-2xl rounded-3xl border border-gray-100">
        <!-- Penjelasan di atas form -->
    <div class="mb-8">
        <h3 class="text-2xl font-bold text-gray-800 mb-2">Tambah Produk</h3>
    </div>


        <form action="#" method="POST" enctype="multipart/form-data" class="space-y-8">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Nama Produk -->
                <div>
                    <label for="name" class="block text-sm font-bold text-gray-700 mb-2">Nama Produk</label>
                    <input type="text" name="name" required
                        class="w-full pl-3 pr-5 py-3 border border-gray-300 rounded-xl shadow-sm focus:ring-2 focus:ring-indigo-400 ">
                </div>

                <!-- Harga Produk -->
                <div>
                    <label for="price_display" class="block text-sm font-bold text-gray-700 mb-2">Harga</label>
                    <div class="relative">
                        <span class="absolute left-4 top-3 text-gray-500">Rp</span>
                        <input type="text" id="price_display"
                            class="w-full px-5 py-3 border border-gray-300 rounded-xl shadow-sm focus:ring-2 focus:ring-indigo-400">
                        <input type="hidden" name="price" id="price">
                    </div>
                </div>

                <!-- Stok Produk -->
                <div>
                    <label for="stock" class="block text-sm font-bold text-gray-700 mb-2">Stok</label>
                    <input type="number" name="stock" required min="1"
                        class="w-full pl-3 pr-5 py-3 border border-gray-300 rounded-xl shadow-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                </div>

                <!-- Gambar Produk -->
                <div>
                    <label for="image" class="block text-sm font-bold text-gray-700 mb-2">Gambar Produk</label>
                    <input type="file" name="image" accept="image/*"
                        class="w-full pl-3 pr-5 py-3 border border-gray-300 rounded-xl shadow-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none bg-gray-50">
                </div>
            </div>

            <!-- Tombol Submit -->
            <div class="pt-4">
                <button type="submit"
                    class="w-full bg-indigo-600 text-white text-lg font-semibold py-3 px-6 rounded-xl hover:bg-indigo-700 transition duration-300 ease-in-out shadow-md">
                    Simpan Produk
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
