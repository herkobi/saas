<div class="card rounded-0 shadow-sm">
    <div class="card-body">
        <h3 class="card-title">{{ __('Kişisel Bilgiler') }}</h3>
        <p class="text-mute">
            {{ __("Kişisel bilgilerinizi ve e-posta adresinizi bu bölümden güncelleyebilirsiniz.") }}
        </p>
        <form id="send-verification" method="post" action="{{ route('verification.send') }}">
            @csrf
        </form>

        <form method="post" action="{{ route('admin.profile.update') }}">
            @csrf
            @method('patch')
            <div class="input-group mb-3">
                <span class="input-group-text rounded-0 shadow-none">
                    <i class="ri ri-user-3-line"></i>
                </span>
                <x-input id="name" type="text" name="name" :value="old('name', $user->name)" placeholder="Ad Soyad" required autofocus autocomplete="name" />
                <x-input-error :messages="$errors->get('name')" class="mt-1 mb-0" />
            </div>
            <div class="input-group mb-3">
                <span class="input-group-text rounded-0 shadow-none">
                    <i class="ri ri-mail-check-line"></i>
                </span>
                <x-input id="email" type="email" name="email" :value="old('email', $user->email)" placeholder="Ad Soyad" required autofocus autocomplete="username" />
                <x-input-error :messages="$errors->get('email')" class="mt-1 mb-0" />
            </div>
            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mb-3">
                    <div class="alert alert-danger rounded-0 shadow-none">
                        {{ __('E-posta adresiniz onaylı değil.') }}

                        <button form="send-verification" class="btn btn-warning rounded-0 shadow-none btn-sm">
                            {{ __('Onay e-postasını tekrar göndermek için tıklayınız.') }}
                        </button>
                    </div>

                    @if (session('status') === 'verification-link-sent')
                        <div class="alert alert-info rounded-0 shadow-none">
                            {{ __('E-posta adresinizi onaylamanız için yeni bir e-posta gönderildi.') }}
                        </div>
                    @endif
                </div>
            @endif
            <div class="mb-3">
                <x-submit>{{ __('Güncelle') }}</x-submit>
            </div>
            @if (session('status') === 'profile-updated')
                <p class="text text-danger">{{ __('Güncellendi.') }}</p>
            @endif
        </form>
    </div>
</div>
