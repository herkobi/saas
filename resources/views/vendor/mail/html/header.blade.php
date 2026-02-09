@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
<img src="{{ logo() }}" class="logo" alt="{{ settings('site_name') ?? config('app.name') }}">
</a>
</td>
</tr>
