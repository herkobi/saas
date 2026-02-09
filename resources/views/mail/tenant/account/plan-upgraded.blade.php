@component('mail::message')
# Plan Yükseltildi

Merhaba {{ $user->name }},

Abonelik planınız başarıyla yükseltildi.

@component('mail::panel')
**Önceki Plan:** {{ $oldPlanName }}<br>
**Yeni Plan:** {{ $newPlanName }}<br>
**Yeni Fiyat:** {{ $newPrice }} {{ $currency }}
@endcomponent

@component('mail::button', ['url' => $dashboardUrl])
Panele Git
@endcomponent

Saygılarımızla,<br>
{{ settings('site_name') ?? config('app.name') }}
@endcomponent
