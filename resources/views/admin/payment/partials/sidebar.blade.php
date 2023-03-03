<div class="sidebar-menu border-end pe-4">
    <label class="text-muted fw-bold small mb-3 ps-3">ÖDEMELER</label>
    <div class="list-group list-group-flush mb-2">
        <x-listlink :href="route('admin.payments')" :active="request()->routeIs('admin.payments')" title="Yaklaşan Ödemeler">Yaklaşan Ödemeler</x-listlink>
        <x-listlink :href="route('admin.payments.open')" :active="request()->routeIs('admin.payments.open')" title="Açık Ödemeler">Açık Ödemeler</x-listlink>
        <x-listlink :href="route('admin.payments.approved')" :active="request()->routeIs('admin.payments.approved')" title="Onay Bekleyen Ödemeler">Onay Bekleyen Ödemeler</x-listlink>
        <x-listlink :href="route('admin.payments.closed')" :active="request()->routeIs('admin.payments.closed')" title="Tamamlanan Ödemeler">Tamamlanan Ödemeler</x-listlink>
    </div>
</div>
