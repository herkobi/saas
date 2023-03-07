<x-admin-layout>
    <div class="row">
        <div class="col-md-3 h-100">
            @include('admin.settings.partials.sidebar')
        </div>
        <div class="col-md-9">
            <div class="page-title d-flex align-items-center justify-content-between border-bottom pb-2 mb-3">
                @include('admin.settings.partials.header', ['title' => 'Sözleşmeler', 'link' => 'admin.settings.contracts.create', 'linktext' => 'Sözleşme Ekle' ])
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
                            @foreach ($contracts as $item)
                            <tr>
                                <td>{{$item->title}}</td>
                                <td>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input shadow-none" type="checkbox" role="switch" id="odemeDurum" checked>
                                    </div>
                                </td>
                                <td>
                                    <a href="{{route('admin.settings.contracts.edit', $item->id)}}" class="fs-5 border-0 bg-transparent text-decoration-none text-black-50" title="Düzenle">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <i class="ri-edit-line"></i> <span class="small">Düzenle</span>
                                        </div>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
