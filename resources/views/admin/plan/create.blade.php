<x-admin-layout>
    <div class="row">
        <div class="col-md-3 h-100">
            @include('admin.plan.partials.sidebar')
        </div>
        <div class="col-md-9">
            <div class="page-title d-flex align-items-center justify-content-between border-bottom pb-2 mb-3">
                @include('admin.plan.partials.header', ['title' => 'Plan Ekle', 'link' => 'admin.plans', 'linktext' => "Planlar" ])
            </div>
            <div class="page-content">
                <div class="row">
                    <div class="col-md-10">
                        <form action="" method="post">
                            @csrf
                            <div class="mb-3 border-bottom pb-3">
                                <div class="row">
                                    <label for="planTur" class="col-md-4 fw-bold align-self-center">Plan Türü</label>
                                    <div class="col-md-8">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="planTur" id="planTur" value="1" checked>
                                            <label class="form-check-label" for="planTur">Ücretli</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="planTur" id="planTur" value="0">
                                            <label class="form-check-label" for="planTur">Ücretsiz</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3 border-bottom pb-3">
                                <div class="row">
                                    <label for="planAdi" class="col-md-4 fw-bold align-self-center">Plan Adı</label>
                                    <div class="col-md-8">
                                        <x-input id="planAdi" type="text" class="form-control-sm" name="title" placeholder="Plan Adı" required autofocus />
                                        <x-input-error :messages="$errors->get('title')" class="mt-1 mb-0" />
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3 border-bottom pb-3">
                                <div class="row">
                                    <label for="planDurum" class="col-md-4 fw-bold align-self-center">Plan Durumu</label>
                                    <div class="col-md-8">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="planDurum" id="planDurum" value="1" checked>
                                            <label class="form-check-label" for="planDurum">Aktif</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="planDurum" id="planDurum" value="0">
                                            <label class="form-check-label" for="planDurum">Pasif</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3 border-bottom pb-3">
                                <div class="row">
                                    <label for="planYapi" class="col-md-4 fw-bold align-self-center">Plan Yapısı</label>
                                    <div class="col-md-8">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="planYapi" id="planYapi" value="1" checked>
                                            <label class="form-check-label" for="planYapi">Genel</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="planYapi" id="planYapi" value="0">
                                            <label class="form-check-label" for="planYapi">Müşteriye Özel (Gizli)</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3 border-bottom pb-3">
                                <div class="row">
                                    <label for="planDonem" class="col-md-4 fw-bold align-self-start">Plan Dönemi</label>
                                    <div class="col-md-8">
                                        <div class="row">
                                            <div class="col-md-6 mb-2">
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="planDonem" id="planDonemAylik" value="0">
                                                    <label class="form-check-label" for="planDonemAylik">Aylık Ödemeli</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="planDonem" id="planDonem3Aylik" value="1">
                                                    <label class="form-check-label" for="planDonem3Aylik">3 Aylık Ödemeli</label>
                                                </div>
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="planDonem" id="planDonem6Aylik" value="2">
                                                    <label class="form-check-label" for="planDonem6Aylik">6 Aylık Ödemeli</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="planDonem" id="planDonemYillik" value="3">
                                                    <label class="form-check-label" for="planDonemYillik">Yıllık Ödemeli</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3 border-bottom pb-3">
                                <div class="row">
                                    <label for="planTutar" class="col-md-4 fw-bold align-self-center">Plan Ücreti</label>
                                    <div class="col-md-8">
                                        <x-input id="planTutar" type="text" class="form-control-sm" name="price" placeholder="Plan Ücreti" required autofocus />
                                        <x-input-error :messages="$errors->get('price')" class="mt-1 mb-0" />
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3 border-bottom pb-3">
                                <div class="row">
                                    <label for="planArtikGun" class="col-md-4 fw-bold align-self-center">Artık Gün</label>
                                    <div class="col-md-8">
                                        <x-input id="planArtikGun" type="number" class="form-control-sm" name="extra" placeholder="Ödeme Beklenecek Ek Günler" required autofocus />
                                        <x-input-error :messages="$errors->get('extra')" class="mt-1 mb-0" />
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3 border-bottom pb-3">
                                <div class="row">
                                    <label for="planDesc" class="col-md-4 fw-bold align-self-start">Plan İçeriği</label>
                                    <div class="col-md-8">
                                        <textarea class="form-control editor rounded-0 shadow-none" name="desc" id="planDesc" rows="3"></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="row">
                                    <div class="offset-md-4 col-md-5">
                                        <button type="submit" class="btn add-btn btn-primary btn-sm rounded-0 shadow-none"><i class="ri-add-line"></i> Planı Kaydet</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
