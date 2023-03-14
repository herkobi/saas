<x-admin-layout>
    <div class="row">
        <div class="col-md-12">
            <div class="page-content">
                <table data-toggle="table" data-show-columns="true" data-pagination="true" data-search="true" data-show-search-clear-button="true" data-filter-control="true" data-mobile-responsive="true">
                    <thead>
                        <tr>
                            <th data-sortable="true" data-field="name" data-filter-control="input">Müşteri Adı</th>
                            <th data-sortable="true" data-field="group" data-filter-control="select">Grubu</th>
                            <th data-sortable="true" data-field="plan" data-filter-control="select">Kullanılan Paket</th>
                            <th data-sortable="true" data-field="payment" data-filter-control="input">Ödeme Döngüsü</th>
                            <th data-sortable="true" data-field="membership" data-filter-control="input">Abonelik Tarihi</th>
                            <th data-sortable="true" data-field="category" data-filter-control="select">Kategori</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Ahmet Mehmet</td>
                            <td>Yeniler</td>
                            <td>Standart Paket</td>
                            <td>Aylık</td>
                            <td>10.05.2023</td>
                            <td>Özel Müşteri</td>
                        </tr>
                        <tr>
                            <td>Fikret Saffet</td>
                            <td>Ustalar</td>
                            <td>Profesyonel Paket</td>
                            <td>Yıllık</td>
                            <td>17.02.2023</td>
                            <td>Hazır Müşteri</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-admin-layout>
