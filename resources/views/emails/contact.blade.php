<x-mail::message>
# Hi my name is {{ $data['name'] }}

Message:
 {{ $data['message'] }}.

 @php
 $mailto_link = 'mailto:' . $data['email'] . '?subject=' . urlencode('Reply to Contact Us') . '&body=' . urlencode('Hi ' . $data['name'] . ',%0D%0A%0D%0A');
@endphp

<x-mail::button :url="$mailto_link">
Reply
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
