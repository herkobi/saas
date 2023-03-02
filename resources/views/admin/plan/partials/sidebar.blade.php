<div class="sidebar-menu border-end pe-4">
    <label class="text-muted fw-bold small mb-3 ps-3">PLANLAR</label>
    <div class="list-group list-group-flush mb-2">
        <x-listlink :href="route('admin.plans')" :active="request()->routeIs('admin.plans')" title="Ücretli Planlar">Ücretli Planlar</x-listlink>
        <x-listlink :href="route('admin.plans.free')" :active="request()->routeIs('admin.plans.free')" title="Ücretsiz Planlar">Ücretsiz Planlar</x-listlink>
    </div>
</div>
