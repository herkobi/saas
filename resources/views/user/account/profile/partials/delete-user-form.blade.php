<div class="card rounded-0 shadow-none border-0 bg-transparent">
    <div class="card-body">
        <h3 class="card-title">{{ __('Hesabımı Sil') }}</h3>
        <p class="text-mute">
            {{ __('Hesabınızı silerseniz tüm içeriğiniz kaybolacaktır. Bu işlem geri alınamaz.') }}
        </p>
        <x-modal>
            {{ __('Hesabımı Sil') }}
        </x-modal>

        <div class="modal fade" id="deleteModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content rounded-0 shadow-none">
                    <form method="post" action="{{ route('profile.destroy') }}">
                        @csrf
                        @method('delete')
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="deleteModalLabel">Hesap Silme Onayı</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <h4 class="fw-semibold">{{ __('Hesabınızı silmek istediğinizden emin misiniz?') }}</h4>
                            <p>{{ __('Hesabınızı silerseniz tüm verileriniz kaybolacaktır ve bu işlem geri alınamaz. Hesabınızı silmek için lütfen şifrenizi giriniz.') }}</p>
                            <div class="mb-3">
                                <x-input-label for="password" :value="__('Kullandığınız Şifreniz')" />
                                <div class="input-group">
                                    <span class="input-group-text rounded-0 shadow-none">
                                        <i class="ri ri-key-line"></i>
                                    </span>
                                    <x-input id="password" name="password" type="password" />
                                    <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-1 mb-0" />
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
                            <button type="submit" class="btn btn-primary">Hesabımı Sil</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
