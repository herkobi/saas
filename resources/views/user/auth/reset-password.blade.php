<x-guest-layout>
    <form method="POST" action="{{ route('password.store') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">
        <div class="input-group mb-2">
            <x-auth.email id="email" type="email" name="email" :value="old('email', $request->email)" placeholder="E-posta Adresi" required autocomplete="username" />
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
        <div class="mb-3">
            <x-auth.submit>
                <span>Şifremi Yenile</span>
            </x-auth.submit>
        </div>
    </form>
</x-guest-layout>
