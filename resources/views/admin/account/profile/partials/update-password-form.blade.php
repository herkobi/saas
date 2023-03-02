<div class="card rounded-0 shadow-sm">
    <div class="card-body">
        <h3 class="card-title">{{ __('Şifre Güncelle') }}</h3>
        <p class="text-mute">
            {{ __('Güvenliğiniz için uzun ve karmaşık şifre girmeye özen gösteriniz.') }}
        </p>
        <form method="post" action="{{ route('admin.password.update') }}" class="mt-6 space-y-6">
            @csrf
            @method('put')
            <div class="mb-3">
                <x-input-label for="current_password" :value="__('Kullandığınız Şifreniz')" />
                <div class="input-group">
                    <span class="input-group-text rounded-0 shadow-none">
                        <i class="ri ri-key-line"></i>
                    </span>
                    <x-input id="current_password" name="current_password" type="password" autocomplete="current-password" />
                    <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-1 mb-0" />
                </div>
            </div>
            <div class="mb-3">
                <x-input-label for="password" :value="__('Yeni Şifreniz')" />
                <div class="input-group">
                    <span class="input-group-text rounded-0 shadow-none">
                        <i class="ri ri-key-2-line"></i>
                    </span>
                    <x-input id="password" name="password" type="password" autocomplete="new-password" />
                    <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-1 mb-0" />
                </div>
            </div>
            <div class="mb-3">
                <x-input-label for="password_confirmation" :value="__('Yeni Şifrenizi Tekrar Giriniz')" />
                <div class="input-group">
                    <span class="input-group-text rounded-0 shadow-none">
                        <i class="ri ri-key-2-line"></i>
                    </span>
                    <x-input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" />
                    <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-1 mb-0" />
                </div>
            </div>
            <div class="mb-3">
                <x-submit>{{ __('Güncelle') }}</x-user.profile.submit>
            </div>
            @if (session('status') === 'password-updated')
                <p class="text text-danger">{{ __('Güncellendi.') }}</p>
            @endif
        </form>
    </div>
</div>
