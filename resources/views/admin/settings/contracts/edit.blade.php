<x-admin-layout>
    <div class="row">
        <div class="col-md-3 h-100">
            @include('admin.settings.partials.sidebar')
        </div>
        <div class="col-md-9">
            <div class="page-title d-flex align-items-center justify-content-between border-bottom pb-2 mb-3">
                @include('admin.settings.partials.header', ['title' => 'Yeni Sözleşme', 'link' => 'admin.settings.contracts', 'linktext' => 'Sözleşmeler' ])
            </div>
            <div class="page-content">
                <form action="{{ route('admin.settings.contracts.update', $contract->id) }}" method="post">
                    @csrf
                    <div class="mb-2">
                        <x-input-label for="sozlesmeAdi">Sözleşme Adı</x-input-label>
                        <x-input id="sozlesmeAdi" type="text" name="title" value="{{ $contract->title }}" placeholder="Sözleşme Adı" required autofocus />
                        <x-input-error :messages="$errors->get('title')" class="mt-1 mb-0" />
                    </div>
                    <div class="mb-3">
                        <x-input-label for="sozlesmeDetay">Sözleşme İçeriği</x-input-label>
                        <x-textarea id="sozlesmeDetay" name="content" class="editor" required>{{ $contract->content }}</x-textarea>
                        <x-input-error :messages="$errors->get('content')" class="mt-1 mb-0" />
                    </div>
                    <div class="d-grid gap-2">
                        <x-submit id="kaydet" class="d-flex align-items-center justify-content-center"><i class="ri-add-line"></i> <span>Kaydet</span></x-submit>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-admin-layout>
