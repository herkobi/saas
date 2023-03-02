<div class="sidebar-menu border-end pe-4">
    <label class="text-muted fw-bold small mb-3 ps-3">ÖDEMELER</label>
    <div class="list-group list-group-flush mb-2">
        <x-listlink :href="route('admin.payments')" :active="request()->routeIs('admin.payments')" title="Yaklaşan Ödemeler">Yaklaşan Ödemeler</x-listlink>
        <a href="odemeler-acik.html" class="list-group-item list-group-item-action" title="Açık Ödemeler">Açık Ödemeler</a>
        <a href="odemeler-onay-bekleyenler.html" class="list-group-item list-group-item-action" title="Onay Bekleyen Ödemeler">Onay Bekleyen Ödemeler</a>
        <a href="odemeler-tamamlananlar.html" class="list-group-item list-group-item-action" title="Tamamlanan Ödemeler">Tamamlanan Ödemeler</a>
    </div>
</div>
