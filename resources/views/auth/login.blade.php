<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center bg-gray-100 px-4">
        <div class="w-full max-w-md bg-white shadow-lg rounded-2xl p-8">

            <!-- Title -->
            <div class="text-center mb-6">
                <h1 class="text-3xl font-bold text-gray-800">Sign In</h1>
                <p class="text-sm text-gray-500 mt-1">
                    Enter your email and password
                </p>
            </div>

            <!-- Session Status -->
            <x-auth-session-status class="mb-4 text-center" :status="session('status')" />

            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                <!-- Email -->
                <div>
                    <x-input-label for="email" value="Email" class="mb-1 text-gray-600" />
                    <x-text-input
                        id="email"
                        class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                        type="email"
                        name="email"
                        :value="old('email')"
                        required
                        autofocus
                        autocomplete="username"
                        placeholder="mail@example.com"
                    />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <!-- Password -->
                <div>
                    <x-input-label for="password" value="Password" class="mb-1 text-gray-600" />
                    <x-text-input
                        id="password"
                        class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                        type="password"
                        name="password"
                        required
                        autocomplete="current-password"
                        placeholder="Enter your password"
                    />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <!-- Remember & Forgot -->
                <div class="flex items-center justify-between text-sm">
                    <label for="remember_me" class="flex items-center gap-2 text-gray-600">
                        <input
                            id="remember_me"
                            type="checkbox"
                            class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                            name="remember"
                        >
                        Remember me
                    </label>

                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-indigo-600 hover:underline">
                            Forgot password?
                        </a>
                    @endif
                </div>

                <!-- Button -->
                <button
                    type="submit"
                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 rounded-xl transition"
                >
                    Sign In
                </button>
            </form>

            <!-- Register -->
            <p class="text-center text-sm text-gray-500 mt-6">
                Not registered yet?
                <a href="{{ route('register') }}" class="text-indigo-600 font-medium hover:underline">
                    Create an Account
                </a>
            </p>

        </div>
    </div>
</x-guest-layout>
