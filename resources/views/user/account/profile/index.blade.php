<x-app-layout>
    <div class="row">
        <div class="col-md-3 h-100">
            @include('user.account.partials.sidebar')
        </div>
        <div class="col-md-9">
            <div class="page-title d-flex align-items-center justify-content-between border-bottom pb-2 mb-3">
                <h3 class="section-title mb-0">Profilim</h3>
            </div>
            <div class="page-content">
                <div class="mb-5">
                    @include('user.account.profile.partials.update-profile-information-form')
                </div>
                <div class="mb-5">
                    @include('user.account.profile.partials.update-password-form')
                </div>
                <div class="mb-5">
                    @include('user.account.profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
