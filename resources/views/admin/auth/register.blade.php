<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf
        <div class="input-group mb-2">
            <x-auth.name id="name" type="text" name="name" :value="old('name')" placeholder="Ad Soyad" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-1 mb-0" />
        </div>
        <div class="input-group mb-2">
            <x-auth.email id="email" type="email" name="email" :value="old('email')" placeholder="E-posta Adresi" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-1 mb-0" />
        </div>
        <div class="input-group mb-2">
            <x-auth.password id="password" type="password" name="password" placeholder="Şifre" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-1 mb-0" />
        </div>
        <div class="input-group mb-2">
            <x-auth.password id="password_confirmation" type="password" name="password_confirmation" placeholder="Şifrenizi Tekrar Giriniz" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1 mb-0" />
        </div>
        <div class="form-check text-start mb-3">
            <input class="form-check-input rounded-0 shadow-none" type="checkbox" id="tos" name="tos" required>
            <label class="form-check-label" for="tos">
                <a href="#" class="text-decoration-none" target="_blank" title="Herkobi Üyelik Sözleşmesi">Üyelik Sözleşmesini</a> okudum, onaylıyorum.
            </label>
        </div>
        <div class="mb-3">
            <x-auth.submit>
                <span>Üye Ol</span>
            </x-auth.submit>
        </div>
        <div class="d-flex align-items-center justify-content-between">
            <a class="text-decoration-none text-dark" href="{{ route('login') }}" title="Oturum Aç">
                {{ __('Oturum Aç') }}
            </a>
        </div>
    </form>
</x-guest-layout>
