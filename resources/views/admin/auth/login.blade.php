<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('admin.login') }}">
        @csrf
        <div class="input-group mb-2">
            <x-auth.email id="email" type="email" name="identity" :value="old('email')" placeholder="E-posta Adresi" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-1 mb-0" />
        </div>
        <div class="input-group mb-2">
            <x-auth.password id="password" type="password" name="password" :value="old('password')" placeholder="Şifre" required autofocus autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-1 mb-0" />
        </div>
        <div class="form-check text-start mb-3">
            <input class="form-check-input rounded-0 shadow-none" type="checkbox" id="remember_me" name="remember">
            <label class="form-check-label" for="remember_me">Beni Hatırla</label>
        </div>
        <div class="mb-3">
            <x-auth.submit>
                <span>Oturum Aç</span>
            </x-auth.submit>
        </div>
        <div class="d-flex align-items-center justify-content-between">
            @if (Route::has('admin.password.request'))
            <a class="text-decoration-none text-dark" href="{{ route('admin.password.request') }}" title="Şifre Yenile">
                {{ __('Şifremi Unuttum') }}
            </a>
            @endif
        </div>
    </form>
</x-guest-layout>
