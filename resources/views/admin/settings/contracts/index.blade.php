<x-admin-layout>
    <div class="row">
        <div class="col-md-3 h-100">
            @include('admin.settings.partials.sidebar')
        </div>
        <div class="col-md-9">
            <div class="page-title d-flex align-items-center justify-content-between border-bottom pb-2 mb-3">
                @include('admin.settings.partials.header', ['title' => 'Sözleşmeler' ])
            </div>
            <div class="page-content">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 80%">Sözleşme</th>
                                <th style="width: 10%">Durum</th>
                                <th style="width: 10%">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Gizlilik Politikası</td>
                                <td>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input shadow-none" type="checkbox" role="switch" id="odemeDurum" checked>
                                    </div>
                                </td>
                                <td><a href="ayarlar-sozlesme-duzenle.html" class="fs-5 border-0 bg-transparent text-decoration-none text-black-50" title="Düzenle"><i class="ri-edit-line"></i></a></td>
                            </tr>
                            <tr>
                                <td>Kullanım Sözleşmesi</td>
                                <td>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input shadow-none" type="checkbox" role="switch" id="odemeDurum" checked>
                                    </div>
                                </td>
                                <td><a href="ayarlar-sozlesme-duzenle.html" class="fs-5 border-0 bg-transparent text-decoration-none text-black-50" title="Düzenle"><i class="ri-edit-line"></i></a></td>
                            </tr>
                            <tr>
                                <td>Üyelik Sözleşmesi</td>
                                <td>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input shadow-none" type="checkbox" role="switch" id="odemeDurum" checked>
                                    </div>
                                </td>
                                <td><a href="ayarlar-sozlesme-duzenle.html" class="fs-5 border-0 bg-transparent text-decoration-none text-black-50" title="Düzenle"><i class="ri-edit-line"></i> <small>Düzenle</small></a></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
