@component('mail::message')
@if ($immediate)
# Plan Düşürüldü
@else
# Plan Değişikliği Planlandı
@endif

Merhaba {{ $user->name }},

@if ($immediate)
Abonelik planınız düşürüldü.
@else
Abonelik planınızda değişiklik planlandı. Değişiklik mevcut dönem sonunda uygulanacaktır.
@endif

@component('mail::panel')
**Önceki Plan:** {{ $oldPlanName }}<br>
**Yeni Plan:** {{ $newPlanName }}<br>
**Yeni Fiyat:** {{ $newPrice }} {{ $currency }}
@if (!$immediate && $effectiveAt)
<br>**Geçerlilik Tarihi:** {{ $effectiveAt }}
@endif
@endcomponent

@component('mail::button', ['url' => $dashboardUrl])
Panele Git
@endcomponent

Saygılarımızla,<br>
{{ settings('site_name') ?? config('app.name') }}
@endcomponent
