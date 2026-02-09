@component('mail::message')
# Davetiye Kabul Edildi

Merhaba,

**{{ $acceptedUser->name }}** ({{ $acceptedUser->email }}) **{{ $tenantName }}** hesabına katılma davetinizi kabul etti.

Saygılarımızla,<br>
{{ settings('site_name') ?? config('app.name') }}
@endcomponent
