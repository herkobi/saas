@component('mail::message')
# Abonelik Planınız Değiştirildi

Merhaba {{ $user->name }},

Abonelik planınız yönetici tarafından değiştirildi.

@component('mail::panel')
**Önceki Plan:** {{ $oldPlanName }}<br>
**Yeni Plan:** {{ $newPlanName }}
@endcomponent

@if ($immediate)
Değişiklik derhal uygulanmıştır.
@else
Değişiklik **{{ $endsAt }}** tarihinde uygulanacaktır.
@endif

@component('mail::button', ['url' => $accountUrl])
Abonelik Detayları
@endcomponent

Saygılarımızla,<br>
{{ settings('site_name') ?? config('app.name') }}
@endcomponent
