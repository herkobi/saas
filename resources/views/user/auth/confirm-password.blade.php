<x-guest-layout>
    <div class="mb-3">
        {{ __('Lütfen şifrenizi giriniz.') }}
    </div>

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf
        <div class="input-group mb-2">
            <x-auth.password id="password" type="password" name="password" placeholder="Şifre" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-1 mb-0" />
        </div>
        <div class="mb-3">
            <x-auth.submit>
                <span>Onayla</span>
            </x-auth.submit>
        </div>
    </form>
</x-guest-layout>
