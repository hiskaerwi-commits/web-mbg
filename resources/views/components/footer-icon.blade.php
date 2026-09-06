@props(['name'])
<svg {{ $attributes->class(['footer-icon']) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    @switch($name)
        @case('address')
            <path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="3"/>
            @break
        @case('email')
            <rect x="3" y="5" width="18" height="14" rx="1"/><path d="m3 6 9 7 9-7"/>
            @break
        @case('phone')
            <path d="m7 3 4 4-3 3c1.5 3 3 4.5 6 6l3-3 4 4-2 4C10 22 2 14 3 5Z"/>
            @break
        @case('whatsapp')
            <path d="M20.5 11.5a9 9 0 0 1-13.3 7.9L2 21l1.6-5.2a9 9 0 1 1 16.9-4.3Z"/><path d="m8 6 2 3-1.2 1.5a10 10 0 0 0 4.7 4.7L15 14l3 2c-1 4-5 2-8-1S5 8 8 6Z"/>
            @break
        @case('facebook')
            <path fill="currentColor" stroke="none" d="M22 12a10 10 0 1 0-11.6 9.9v-7H8v-3h2.4V9.6c0-2.5 1.5-3.9 3.7-3.9 1.1 0 2.2.2 2.2.2v2.4h-1.2c-1.2 0-1.6.7-1.6 1.5v2.1h2.7l-.4 3h-2.3v7A10 10 0 0 0 22 12Z"/>
            @break
        @case('instagram')
            <rect x="3" y="3" width="18" height="18" rx="5" stroke-width="2"/><circle cx="12" cy="12" r="4" stroke-width="2"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor" stroke="none"/>
            @break
        @case('x')
            <path d="M4 3h4l12 18h-4ZM20 3l-7 8M4 21l7-8"/>
            @break
        @case('tiktok')
            <path d="M14 3h3c.3 3 2 4.5 4 5v3c-1.7-.1-3-1-4-2v8a5 5 0 1 1-6-4.9v3.1a2 2 0 1 0 3 1.8Z"/>
            @break
    @endswitch
</svg>
