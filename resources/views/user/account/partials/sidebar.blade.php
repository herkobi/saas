<div class="card rounded-0 shadow-sm">
    <div class="card-body">
        <div class="sidebar-menu">
            <label class="text-muted fw-bold small mb-3 ps-3">Hesabım</label>
            <div class="list-group list-group-flush mb-2">
                <x-listlink :href="route('plan')" :active="request()->routeIs('plan')" title="Planım">Planım</x-listlink>
                <x-listlink :href="route('payment')" :active="request()->routeIs('payment')" title="Ödemelerim">Ödemelerim</x-listlink>
                <x-listlink :href="route('profile.edit')" :active="request()->routeIs('profile.edit')" title="Bilgilerim">Bilgilerim</x-listlink>
            </div>
        </div>
    </div>
</div>
