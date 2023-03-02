<x-admin-layout>
    <div class="row">
        <div class="col-md-3 h-100">
            @include('admin.plan.partials.sidebar')
        </div>
        <div class="col-md-9">
            <div class="page-title d-flex align-items-center justify-content-between border-bottom pb-2 mb-3">
                @include('admin.plan.partials.header', ['title' => 'Ücretsiz Planlar', 'link' => 'admin.plan.create', 'linktext' => "Plan Ekle" ])
            </div>
            <div class="page-content">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Durum</th>
                                <th>Plan Adı</th>
                                <th>Plan Yapısı</th>
                                <th>Plan Dönemi</th>
                                <th>İşlemler</th>
                            </tr>
                        </thead>
                        <tbody id="content-list">
                            <tr>
                                <td>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input shadow-none" type="checkbox" role="switch" id="odemeDurum" checked>
                                    </div>
                                </td>
                                <td>Demo Plan</td>
                                <td>Genel</td>
                                <td>14 Gün</td>
                                <td><a href="plan-ucretsiz-duzenle.html" title="Plan Düzenle" class="fs-5 border-0 bg-transparent text-decoration-none text-black-50" title="Düzenle"><i class="ri-edit-line"></i></a></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
