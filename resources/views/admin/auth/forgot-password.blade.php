<x-guest-layout>
    <div class="mb-4">
        {{ __('Lütfen e-posta adresinizi giriniz. Şifrenizi yenilemeniz için size e-posta gönderilecektir.') }}
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />
    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <div class="input-group mb-2">
            <x-auth.email id="email" type="email" name="email" placeholder="E-posta Adresi" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-1 mb-0" />
        </div>
        <div class="mb-3">
            <x-auth.submit>
                <span>Şifremi Yenile</span>
            </x-auth.submit>
        </div>
        <div class="d-flex align-items-center justify-content-between">
            <a class="text-decoration-none text-dark" href="{{ route('login') }}" title="Oturum Aç">
                {{ __('Oturum Aç') }}
            </a>
        </div>
    </form>
</x-guest-layout>
