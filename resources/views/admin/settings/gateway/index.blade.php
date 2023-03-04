<x-admin-layout>
    <div class="row">
        <div class="col-md-3 h-100">
            @include('admin.settings.partials.sidebar')
        </div>
        <div class="col-md-9">
            <div class="page-title d-flex align-items-center justify-content-between border-bottom pb-2 mb-3">
                @include('admin.settings.partials.header', ['title' => 'Ödeme Sistemleri' ])
            </div>
            <div class="page-content">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 80%">Ödeme Sistemi</th>
                                <th style="width: 10%">Durum</th>
                                <th style="width: 10%">İşlem</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>PayTR Kredi Kartı İle Ödeme</td>
                                <td>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input shadow-none" type="checkbox" role="switch" id="odemeDurum" checked>
                                    </div>
                                </td>
                                <td><a href="ayarlar-odeme-sistemi-duzenle.html" class="small text-decoration-none text-black-50" title="Ödeme Sistemi Bilgileri">Düzenle</a></td>
                            </tr>
                            <tr>
                                <td>EFT/Havale İle Ödeme</td>
                                <td>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input shadow-none" type="checkbox" role="switch" id="odemeDurum" checked>
                                    </div>
                                </td>
                                <td><a href="ayarlar-odeme-sistemi-banka.html" class="small text-decoration-none text-black-50" title="Ödeme Sistemi Bilgileri">Düzenle</a></td>
                            </tr>
                            <tr>
                                <td>Elden Ödeme</td>
                                <td>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input shadow-none" type="checkbox" role="switch" id="odemeDurum" checked>
                                    </div>
                                </td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
