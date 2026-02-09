@component('mail::message')
# Abonelik Yenileme Hatırlatması

Merhaba {{ $user->name }},

**{{ $planName }}** planı aboneliğinizin süresinin dolmasına **{{ $daysRemaining }} gün** kaldı.

@component('mail::panel')
**Plan:** {{ $planName }}<br>
**Bitiş Tarihi:** {{ $endsAt }}
@endcomponent

Kesintisiz hizmet almaya devam etmek için aboneliğinizi yenilemeyi unutmayın.

@component('mail::button', ['url' => $subscriptionUrl])
Aboneliği Yenile
@endcomponent

Saygılarımızla,<br>
{{ settings('site_name') ?? config('app.name') }}
@endcomponent
