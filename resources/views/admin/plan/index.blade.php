<x-admin-layout>
    <div class="row">
        <div class="col-md-3 h-100">
            @include('admin.plan.partials.sidebar')
        </div>
        <div class="col-md-9">
            <div class="page-title d-flex align-items-center justify-content-between border-bottom pb-2 mb-3">
                @include('admin.plan.partials.header', ['title' => 'Ücretli Planlar', 'link' => 'admin.plan.create', 'linktext' => "Plan Ekle" ])
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
                                <th>Plan Ücreti</th>
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
                                <td>Standart Plan Aylık</td>
                                <td>Gizli</td>
                                <td>Aylık</td>
                                <td>₺ 250,00</td>
                                <td><a href="plan-duzenle.html" title="Plan Düzenle" class="fs-5 border-0 bg-transparent text-decoration-none text-black-50" title="Düzenle"><i class="ri-edit-line"></i></a></td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input shadow-none" type="checkbox" role="switch" id="odemeDurum" checked>
                                    </div>
                                </td>
                                <td>Standart Plan Yıllık</td>
                                <td>Genel</td>
                                <td>Yıllık</td>
                                <td>₺ 2.000,00</td>
                                <td><a href="plan-duzenle.html" title="Plan Düzenle" class="fs-5 border-0 bg-transparent text-decoration-none text-black-50" title="Düzenle"><i class="ri-edit-line"></i></a></td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input shadow-none" type="checkbox" role="switch" id="odemeDurum" checked>
                                    </div>
                                </td>
                                <td>Uzman Plan Aylık</td>
                                <td>Gizli</td>
                                <td>Aylık</td>
                                <td>₺ 350,00</td>
                                <td><a href="plan-duzenle.html" title="Plan Düzenle" class="fs-5 border-0 bg-transparent text-decoration-none text-black-50" title="Düzenle"><i class="ri-edit-line"></i></a></td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input shadow-none" type="checkbox" role="switch" id="odemeDurum" checked>
                                    </div>
                                </td>
                                <td>Uzman Plan Yıllık</td>
                                <td>Genel</td>
                                <td>Yıllık</td>
                                <td>₺ 3.500,00</td>
                                <td><a href="plan-duzenle.html" title="Plan Düzenle" class="fs-5 border-0 bg-transparent text-decoration-none text-black-50" title="Düzenle"><i class="ri-edit-line"></i></a></td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input shadow-none" type="checkbox" role="switch" id="odemeDurum" checked>
                                    </div>
                                </td>
                                <td>Profesyonel Plan Aylık</td>
                                <td>Genel</td>
                                <td>Aylık</td>
                                <td>₺ 500,00</td>
                                <td><a href="plan-duzenle.html" title="Plan Düzenle" class="fs-5 border-0 bg-transparent text-decoration-none text-black-50" title="Düzenle"><i class="ri-edit-line"></i></a></td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input shadow-none" type="checkbox" role="switch" id="odemeDurum" checked>
                                    </div>
                                </td>
                                <td>Profesyonel Plan Yıllık</td>
                                <td>Gizli</td>
                                <td>Yıllık</td>
                                <td>₺ 5.000,00</td>
                                <td><a href="plan-duzenle.html" title="Plan Düzenle" class="fs-5 border-0 bg-transparent text-decoration-none text-black-50" title="Düzenle"><i class="ri-edit-line"></i></a></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
