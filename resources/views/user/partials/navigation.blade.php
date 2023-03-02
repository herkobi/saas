<ul class="navbar-nav ms-auto mb-2 mb-md-0">
    <li class="nav-item">
        <x-navlink :href="route('dashboard')" :active="request()->routeIs('dashboard')" aria-current="page">
            <i class="ri-dashboard-line"></i> {{ __('Başlangıç') }}
        </x-navlink>
    </li>
    <li class="nav-item dropdown">
        <x-dropdown align="right">
            <x-slot name="trigger">
                <x-navlink class="dropdown-toggle" :href="route('profile.edit')" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ri-user-3-line"></i> {{Auth::user()->name}}
                </x-navlink>
                <x-slot name="content">
                    <x-dropdownlink :href="route('plan')" :active="request()->routeIs('plan')">
                        <i class="ri-secure-payment-line me-2"></i> {{ __('Planım') }}
                    </x-dropdownlink>
                    <x-dropdownlink :href="route('payment')" :active="request()->routeIs('payment')">
                        <i class="ri-wallet-2-line me-2"></i> {{ __('Ödemelerim') }}
                    </x-dropdownlink>
                    <x-dropdownlink :href="route('profile.edit')" :active="request()->routeIs('profile.edit')">
                        <i class="ri-user-3-line me-2"></i> {{ __('Bilgilerim') }}
                    </x-dropdownlink>
                    <li><hr class="dropdown-divider m-0"></li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-dropdownlink :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                            <i class="ri-logout-circle-r-line me-2"></i> {{ __('Oturumu Kapat') }}
                        </x-dropdownlink>
                    </form>
                </x-slot>
            </x-slot>
        </x-dropdown>
    </li>
</ul>
