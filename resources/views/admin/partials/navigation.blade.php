<ul class="navbar-nav ms-auto mb-2 mb-md-0">
    <li class="nav-item">
        <x-navlink :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')" aria-current="page">
            <i class="ri-dashboard-line"></i> {{ __('Başlangıç') }}
        </x-navlink>
    </li>
    <li class="nav-item">
        <x-navlink :href="route('admin.customers')" :active="request()->routeIs('admin.customers')" aria-current="page">
            <i class="ri-account-pin-circle-line"></i> {{ __('Hesaplar') }}
        </x-navlink>
    </li>
    <li class="nav-item">
        <x-navlink :href="route('admin.plans')" :active="request()->routeIs('admin.plans')" aria-current="page">
            <i class="ri-cloud-line"></i> {{ __('Planlar') }}
        </x-navlink>
    </li>
    <li class="nav-item">
        <x-navlink :href="route('admin.payments')" :active="request()->routeIs('admin.payments')" aria-current="page">
            <i class="ri-secure-payment-line"></i> {{ __('Ödemeler') }}
        </x-navlink>
    </li>
    <li class="nav-item">
        <x-navlink :href="route('admin.payments')" :active="request()->routeIs('admin.payments')" aria-current="page">
            <i class="ri-list-settings-line"></i> {{ __('Ayarlar') }}
        </x-navlink>
    </li>
    <li class="nav-item dropdown">
        <x-dropdown align="right">
            <x-slot name="trigger">
                <x-navlink class="dropdown-toggle" :href="route('admin.profile.edit')" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ri-user-3-line"></i> {{Auth::user()->name}}
                </x-navlink>
                <x-slot name="content">
                    <x-dropdownlink :href="route('admin.profile.edit')" :active="request()->routeIs('admin.profile.edit')">
                        <i class="ri-user-3-line me-2"></i> {{ __('Bilgilerim') }}
                    </x-dropdownlink>
                    <li><hr class="dropdown-divider m-0"></li>
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <x-dropdownlink :href="route('admin.logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                            <i class="ri-logout-circle-r-line me-2"></i> {{ __('Oturumu Kapat') }}
                        </x-dropdownlink>
                    </form>
                </x-slot>
            </x-slot>
        </x-dropdown>
    </li>
</ul>
