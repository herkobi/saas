<div class="sidebar-menu border-end pe-4">
    <label class="text-muted fw-bold small mb-3 ps-3">HESABIM</label>
    <div class="list-group list-group-flush mb-2">
        <x-listlink :href="route('profile.edit')" :active="request()->routeIs('admin.profile.edit')" title="Bilgilerim">Bilgilerim</x-listlink>
    </div>
</div>
