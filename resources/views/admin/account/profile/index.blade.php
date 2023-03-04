<x-admin-layout>
    <div class="row">
        <div class="col-md-3 h-100">
            @include('admin.account.partials.sidebar')
        </div>
        <div class="col-md-9">
            <div class="page-title d-flex align-items-center justify-content-between border-bottom pb-2 mb-3">
                @include('admin.account.partials.header', ['title'=>'Bilgilerim'])
            </div>
            <div class="page-content">
                <div class="mb-5">
                    @include('admin.account.profile.partials.update-profile-information-form')
                </div>
                <div class="mb-5">
                    @include('admin.account.profile.partials.update-password-form')
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
