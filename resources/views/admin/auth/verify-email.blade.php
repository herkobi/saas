<x-guest-layout>
    <div class="mb-4">
        {{ __('Lütfen girmiş olduğunuz e-posta adresinizi onaylayınız. Sistemimizi kullanmanız için e-posta onayı gereklidir.') }}
    </div>
    @if (session('status') == 'verification-link-sent')
        <div class="mb-4">
            {{ __('Üye olurken girmiş olduğunuz e-posta adresinize yeni bir onay linki gönderildi.') }}
        </div>
    @endif
    <div class="row mt-4 g-3">
        <div class="col-md-7">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="btn btn-primary btn-sm text-decoration-none rounded-0 shadow-none float-md-start">
                    {{ __('Tekrar E-posta Gönder') }}
                </button>
            </form>
        </div>
        <div class="col-md-5">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-dark btn-sm text-decoration-none text-white rounded-0 shadow-none float-md-end">
                    {{ __('Oturumu Kapat') }}
                </button>
            </form>
        </div>
    </div>
</x-guest-layout>
