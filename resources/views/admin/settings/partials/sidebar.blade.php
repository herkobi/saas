<div class="sidebar-menu border-end pe-4">
    <label class="text-muted fw-bold small mb-3 ps-3">AYARLAR</label>
    <div class="list-group list-group-flush mb-5">
        <x-listlink :href="route('admin.settings.gateway')" :active="request()->routeIs('admin.settings.gateway')" title="Ödeme Sistemleri">Ödeme Sistemleri</x-listlink>
        <x-listlink :href="route('admin.settings.contracts')" :active="request()->routeIs('admin.settings.contracts')" title="Sözleşmeler">Sözleşmeler</x-listlink>
    </div>
</div>
