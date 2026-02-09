@component('mail::message')
# Rolünüz Değiştirildi

Merhaba {{ $user->name }},

**{{ $tenantName }}** hesabındaki rolünüz değiştirildi.

@component('mail::panel')
**Önceki Rol:** {{ $oldRoleLabel }}<br>
**Yeni Rol:** {{ $newRoleLabel }}
@endcomponent

@component('mail::button', ['url' => $dashboardUrl])
Panele Git
@endcomponent

Saygılarımızla,<br>
{{ settings('site_name') ?? config('app.name') }}
@endcomponent
