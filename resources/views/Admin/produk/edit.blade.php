<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-gray-900 leading-tight">
            {{ __('🛒 Update Produk') }}
        </h2>
    </x-slot>

    <div class="max-w-5xl mx-auto mt-12 p-8 bg-white shadow-lg rounded-2xl border border-gray-100">

        <div class="mb-8">
            <h3 class="text-2xl font-bold text-gray-800 mb-2">Edit Produk</h3>
        </div>

        <form action="#" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label for="name" class="block text-sm font-semibold text-gray-700 mb-1">Nama Produk</label>
                <input type="text" name="name" value="#" required
                       class="w-full pl-3 pr-5 py-3 border border-gray-300 rounded-xl shadow-sm focus:ring-2 focus:ring-indigo-400 ">
            </div>

            <div>
                <label for="price" class="block text-sm font-semibold text-gray-700 mb-1">Harga</label>
                <div class="relative">
                    <span class="absolute left-4 top-3 text-gray-600">Rp</span>
                    <input type="text" id="price_display" value="#"
                           class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-md shadow-sm focus:ring focus:ring-indigo-200 focus:outline-none">
                    <input type="hidden" name="price" id="price" value="#">
                </div>
            </div>

            <div>
                <label for="stock" class="block text-sm font-semibold text-gray-700 mb-1">Stok</label>
                <input type="number" name="stock" value="#" required min="1" readonly
                    class="w-full px-3 py-3 border border-gray-300 bg-gray-100 text-gray-700 rounded-md shadow-sm focus:ring-2 focus:ring-indigo-400">
            </div>

            <div>
                <label for="image" class="block text-sm font-semibold text-gray-700 mb-1">Gambar Produk</label>
                <input type="file" name="image" accept="image/*"
                       class="w-full pl-3 pr-5 py-3 border border-gray-300 rounded-xl shadow-sm focus:ring-2 focus:ring-indigo-400  bg-gray-50">
            </div>

            <div>
                <button type="submit"
                        class="w-full bg-indigo-600 text-white py-3 px-6 rounded-md shadow-md hover:bg-indigo-700 transition duration-200 ease-in-out">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
