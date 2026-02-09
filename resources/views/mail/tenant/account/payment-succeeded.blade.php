@component('mail::message')
# Ödeme Başarılı

Merhaba {{ $user->name }},

Ödemeniz başarıyla tamamlandı. İşlem detayları aşağıdadır:

@component('mail::panel')
**Plan:** {{ $planName }}<br>
**Tutar:** {{ $amount }} {{ $currency }}<br>
**Ödeme Tarihi:** {{ $paidAt }}
@endcomponent

@component('mail::button', ['url' => $dashboardUrl])
Panele Git
@endcomponent

Saygılarımızla,<br>
{{ settings('site_name') ?? config('app.name') }}
@endcomponent
