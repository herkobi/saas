@component('mail::message')
# Aboneliğiniz İptal Edildi

Merhaba {{ $user->name }},

**{{ $planName }}** planı aboneliğiniz yönetici tarafından iptal edildi.

@if ($immediate)
İptal işlemi derhal uygulanmıştır.
@else
Aboneliğiniz **{{ $endsAt }}** tarihine kadar aktif kalacaktır.
@endif

@component('mail::button', ['url' => $accountUrl])
Abonelik Detayları
@endcomponent

Saygılarımızla,<br>
{{ settings('site_name') ?? config('app.name') }}
@endcomponent
