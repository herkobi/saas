@component('mail::message')
# Abonelik Süresi Doldu

Merhaba {{ $user->name }},

**{{ $planName }}** planı aboneliğinizin süresi **{{ $endsAt }}** tarihinde doldu.

Hizmetlerimizden kesintisiz yararlanmaya devam etmek için aboneliğinizi yenileyebilirsiniz.

@component('mail::button', ['url' => $subscriptionUrl])
Aboneliği Yenile
@endcomponent

Saygılarımızla,<br>
{{ settings('site_name') ?? config('app.name') }}
@endcomponent
