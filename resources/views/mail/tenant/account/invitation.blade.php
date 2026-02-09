@component('mail::message')
# Ekip Davetiyesi

Merhaba,

**{{ $inviterName }}** sizi **{{ $tenantName }}** hesabına **{{ $roleLabel }}** olarak davet etti.

Daveti kabul etmek için aşağıdaki butona tıklayın.

@component('mail::button', ['url' => $acceptUrl])
Daveti Kabul Et
@endcomponent

Bu daveti beklemiyorsanız, bu e-postayı görmezden gelebilirsiniz.

Saygılarımızla,<br>
{{ settings('site_name') ?? config('app.name') }}
@endcomponent
