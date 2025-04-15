<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
            {{ __('Tambah User') }}
        </h2>
    </x-slot>

    <div class="max-w-5xl mx-auto mt-12 p-8 bg-white shadow-lg rounded-2xl">
        <div class="mb-8">
            <h3 class="text-2xl font-bold text-gray-800 mb-2">Edit User</h3>
        </div>

        <form action="#" method="POST" class="space-y-6">

            <div>
                <label for="name" class="block text-sm font-semibold text-gray-700 mb-1">Nama</label>
                <input type="text" name="name" value="#" required
                    class="w-full px-4 py-3 border border-gray-300 bg-gray-50 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 transition">
            </div>

            <div>
                <label for="email" class="block text-sm font-semibold text-gray-700 mb-1">Email</label>
                <input type="email" name="email" value="#" required
                    class="w-full px-4 py-3 border border-gray-300 bg-gray-50 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 transition">
            </div>

            <div>
                <label for="password" class="block text-sm font-semibold text-gray-700 mb-1">Password</label>
                <input type="password" name="password" value="{{ old('password') }}" required
                    class="w-full px-4 py-3 border border-gray-300 bg-gray-50 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 transition">
            </div>

            <div>
                <label for="role" class="block text-sm font-semibold text-gray-700 mb-1">Role</label>
                <select name="role" id="role" required
                    class="w-full px-4 py-3 border border-gray-300 bg-gray-50 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 transition">
                    <option value="Administrator">Admin</option>
                    <option value="Petugas">Petugas</option>
                </select>
            </div>

            <div>
                <button type="submit"
                    class="w-full bg-indigo-600 text-white py-3 px-6 rounded-xl font-semibold hover:bg-indigo-700 transition shadow-md">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
