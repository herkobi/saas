@component('mail::message')
# Ödeme Başarısız

Merhaba {{ $user->name }},

**{{ $planName }}** planı için yapılan ödeme işlemi başarısız oldu.

@component('mail::panel')
**Plan:** {{ $planName }}<br>
**Tutar:** {{ $amount }} {{ $currency }}
@endcomponent

Lütfen ödeme bilgilerinizi kontrol ederek tekrar deneyin.

@component('mail::button', ['url' => $retryUrl, 'color' => 'error'])
Tekrar Dene
@endcomponent

Saygılarımızla,<br>
{{ settings('site_name') ?? config('app.name') }}
@endcomponent
