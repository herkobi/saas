<div class="sidebar-menu border-end pe-4">
    <label class="text-muted fw-bold small mb-3 ps-3">HESAPLAR</label>
    <div class="list-group list-group-flush mb-5">
        <x-listlink :href="route('admin.customers')" :active="request()->routeIs('admin.customers')" title="Aktif Hesaplar">Aktif Hesaplar</x-listlink>
        <a href="hesaplar-demo.html" class="list-group-item list-group-item-action" title="Demo Hesaplar">Demo Hesaplar</a>
        <a href="hesaplar-kapananlar.html" class="list-group-item list-group-item-action" title="Kapalı Hesaplar">Kapalı Hesaplar</a>
    </div>
    <label class="text-muted fw-bold small mb-3 ps-3">İŞLEMLER</label>
    <div class="list-group list-group-flush mb-4">
        <a href="hesaplar-yeni.html" class="list-group-item list-group-item-action" title="Yeni Hesap Oluştur">Yeni Hesap Oluştur</a>
        <a href="hesaplar-kategoriler.html" class="list-group-item list-group-item-action" title="Kategoriler">Kategoriler</a>
        <a href="hesaplar-kategori-ekle.html" class="list-group-item list-group-item-action" title="Kategori Ekle">Kategori Ekle</a>
    </div>
</div>
