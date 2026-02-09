@component('mail::message')
# Aboneliğiniz Aktif

Merhaba {{ $user->name }},

**{{ $planName }}** planı aboneliğiniz başarıyla aktif edildi.

@component('mail::panel')
**Plan:** {{ $planName }}<br>
**Başlangıç:** {{ $startsAt }}<br>
**Bitiş:** {{ $endsAt }}
@endcomponent

@component('mail::button', ['url' => $dashboardUrl])
Panele Git
@endcomponent

Saygılarımızla,<br>
{{ settings('site_name') ?? config('app.name') }}
@endcomponent
