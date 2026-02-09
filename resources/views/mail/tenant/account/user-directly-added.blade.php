@component('mail::message')
# Hesaba Eklendiniz

Merhaba {{ $user->name }},

**{{ $addedBy->name }}** sizi **{{ $tenantName }}** hesabına ekledi.

Artık bu hesaba erişebilirsiniz.

Saygılarımızla,<br>
{{ settings('site_name') ?? config('app.name') }}
@endcomponent
