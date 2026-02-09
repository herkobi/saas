@component('mail::message')
# Aboneliğiniz Yenilendi

Merhaba {{ $user->name }},

**{{ $planName }}** planı aboneliğiniz yönetici tarafından yenilendi.

@component('mail::panel')
**Plan:** {{ $planName }}<br>
**Başlangıç:** {{ $startsAt }}<br>
**Bitiş:** {{ $endsAt }}
@endcomponent

@component('mail::button', ['url' => $accountUrl])
Abonelik Detayları
@endcomponent

Saygılarımızla,<br>
{{ settings('site_name') ?? config('app.name') }}
@endcomponent
