@props(['url'])

<tr>
    <td class="header" style="text-align: center; padding: 40px;">
        <a href="{{ $url }}" style="display: inline-block;">
            @if (trim($slot) === 'Clinico')
                <img src="https://clinico-api.clinico.site/images/Logo.png" alt="Clinico Logo" style="width: 150px; height: auto; max-width: 100%;">
            @else
                {{ $slot }}
            @endif
        </a>
    </td>
</tr>
