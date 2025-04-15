<x-app-layout>
    <div class="max-w-5xl mx-auto mt-12 p-10 bg-white shadow-2xl rounded-3xl border border-gray-100">
        <div class="mb-8">
            <h3 class="text-2xl font-bold text-gray-800 mb-2">Tambah User</h3>
        </div>

        <form action="#" method="POST" class="space-y-8">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div>
                    <label for="name" class="block text-sm font-bold text-gray-700 mb-2">Nama</label>
                    <input type="text" name="name" required
                        class="w-full px-5 py-3 border border-gray-300 rounded-xl shadow-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none">`
                </div>

                <div>
                    <label for="email" class="block text-sm font-bold text-gray-700 mb-2">Email</label>
                    <input type="email" name="email"" required
                        class="w-full px-5 py-3 border border-gray-300 rounded-xl shadow-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                </div>

                <div>
                    <label for="password" class="block text-sm font-bold text-gray-700 mb-2">Password</label>
                    <input type="password" name="password" required
                        class="w-full px-5 py-3 border border-gray-300 rounded-xl shadow-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                </div>

                <div>
                    <label for="role" class="block text-sm font-bold text-gray-700 mb-2">Role</label>
                    <select name="role" id="role" required
                        class="w-full px-5 py-3 border border-gray-300 rounded-xl shadow-sm bg-white focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                        <option value="">-- Pilih Role --</option>
                        <option value="Administrator">Admin</option>
                        <option value="Petugas">Petugas</option>
                    </select>
                </div>
            </div>

            <div class="pt-6">
                <button type="submit"
                    class="w-full bg-indigo-600 text-white text-lg font-semibold py-3 px-6 rounded-xl hover:bg-indigo-700 transition duration-300 ease-in-out shadow-md">
                    Simpan User
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
