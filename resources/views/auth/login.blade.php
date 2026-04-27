<x-guest-layout>
    <div class="mb-6 text-center">
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Selamat Datang</h1>
        <p class="mt-2 text-sm text-slate-600">Silakan masuk untuk melanjutkan ke dashboard madrasah.</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-5" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4" x-data="{ showPassword: false }">
            <x-input-label for="password" value="Kata Sandi" />

            <div class="relative mt-1">
                <x-text-input id="password" class="block w-full pe-10"
                            x-bind:type="showPassword ? 'text' : 'password'"
                            name="password"
                            required autocomplete="current-password" />

                <button type="button"
                        class="absolute inset-y-0 end-0 inline-flex items-center justify-center pe-3 text-slate-500 hover:text-slate-700"
                        x-on:click="showPassword = !showPassword"
                        x-bind:aria-label="showPassword ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'">
                    <svg x-show="!showPassword" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M10 3C5.5 3 1.73 5.94.46 10c1.27 4.06 5.04 7 9.54 7s8.27-2.94 9.54-7C18.27 5.94 14.5 3 10 3Zm0 11a4 4 0 1 1 0-8 4 4 0 0 1 0 8Z" />
                    </svg>
                    <svg x-show="showPassword" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path d="m3.28 2.22 14.5 14.5-1.06 1.06-2.37-2.37A10.34 10.34 0 0 1 10 17c-4.5 0-8.27-2.94-9.54-7a10.5 10.5 0 0 1 3.36-4.86L2.22 3.28l1.06-1.06ZM10 6c.56 0 1.1.12 1.57.34L8.34 9.57A3 3 0 0 1 10 6Zm4.78 4a4.75 4.75 0 0 0-1.02-1.7l-1.08 1.08c.2.37.32.79.32 1.24a3 3 0 0 1-.28 1.25l1.25 1.25c.34-.34.61-.72.81-1.12Zm-3.6 3.6L9.93 12.35A2 2 0 0 1 7.65 10.07L6.4 8.82a4 4 0 0 0 4.78 4.78ZM10 3c4.5 0 8.27 2.94 9.54 7a10.45 10.45 0 0 1-2.2 3.66l-1.08-1.08A8.7 8.7 0 0 0 17.95 10C16.7 6.68 13.57 4.3 10 4.3c-.86 0-1.69.14-2.46.4L6.5 3.66A10.9 10.9 0 0 1 10 3Z" />
                    </svg>
                </button>
            </div>

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-emerald-600 shadow-sm focus:ring-emerald-500" name="remember">
                <span class="ms-2 text-sm text-slate-600">Ingat saya</span>
            </label>
        </div>

        <div class="mt-6 flex items-center justify-between gap-4">
            @if (Route::has('password.request'))
                <a class="text-sm font-medium text-emerald-700 hover:text-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 rounded-md" href="{{ route('password.request') }}">
                    Lupa kata sandi?
                </a>
            @endif

            <x-primary-button class="ms-auto bg-emerald-600 hover:bg-emerald-700 focus:ring-emerald-500">
                Masuk
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
