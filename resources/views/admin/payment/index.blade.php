<x-admin-layout>
    <div class="row">
        <div class="col-md-3 h-100">
            @include('admin.payment.partials.sidebar')
        </div>
        <div class="col-md-9">
            <div class="page-title d-flex align-items-center justify-content-between border-bottom pb-2 mb-3">
                @include('admin.payment.partials.header', ['title'=>'Yaklaşan Ödemeler'])
            </div>
            <div class="page-content">
            </div>
        </div>
    </div>
</x-admin-layout>
