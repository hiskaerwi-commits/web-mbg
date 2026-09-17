@props(['settings'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $settings->default_meta_title ?: $settings->display_name }}</title>
    <meta name="description" content="{{ $settings->default_meta_description }}">
    <link rel="canonical" href="{{ rtrim(config('app.url'), '/') }}{{ request()->getPathInfo() }}">
    @if ($settings->google_site_verification)
        <meta name="google-site-verification" content="{{ $settings->google_site_verification }}">
    @endif
    @if ($settings->favicon_path)
        <link rel="icon" href="{{ Storage::disk('public')->url($settings->favicon_path) }}">
    @endif
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col bg-slate-100 font-sans text-[#071E49] antialiased">
    <header class="sticky top-0 z-40 border-b border-[#e4e9f0] bg-white/95 backdrop-blur">
        <div class="mx-auto flex w-full max-w-7xl items-center justify-between gap-5 px-4 py-3.5 sm:px-6 lg:px-8">
            <a href="{{ url('/') }}" class="flex min-w-0 items-center gap-3" aria-label="{{ $settings->display_name }}">
                @if ($settings->logo_path)
                    <img src="{{ Storage::disk('public')->url($settings->logo_path) }}" alt="{{ $settings->display_name }}" class="size-11 shrink-0 rounded-full object-contain">
                @else
                    <span class="grid size-11 shrink-0 place-items-center rounded-full bg-[#071E49] text-base font-extrabold text-white">{{ str($settings->site_name)->substr(0, 1) }}</span>
                @endif
                <span class="min-w-0">
                    <span class="block truncate text-base font-extrabold leading-[1.15] tracking-[-0.3px] text-[#071E49]">{{ $settings->display_name }}</span>
                    @if ($settings->site_tagline)
                        <span class="mt-0.5 block truncate text-xs font-semibold text-slate-500">{{ $settings->site_tagline }}</span>
                    @endif
                </span>
            </a>

            <nav class="hidden items-center gap-1 lg:flex" aria-label="Navigasi utama">
                <a href="{{ url('/#tentang') }}" class="rounded-lg px-3 py-2 text-sm font-semibold text-slate-600 transition hover:bg-slate-100 hover:text-[#071E49]">Tentang</a>
                <a href="{{ url('/#sasaran') }}" class="rounded-lg px-3 py-2 text-sm font-semibold text-slate-600 transition hover:bg-slate-100 hover:text-[#071E49]">Program</a>
                <a href="{{ url('/#jaringan') }}" class="rounded-lg px-3 py-2 text-sm font-semibold text-slate-600 transition hover:bg-slate-100 hover:text-[#071E49]">Jaringan</a>
                <a href="{{ route('news.index') }}" class="rounded-lg px-3 py-2 text-sm font-semibold text-slate-600 transition hover:bg-slate-100 hover:text-[#071E49]">Berita</a>
                <a href="{{ route('data-mbg') }}" class="rounded-lg px-3 py-2 text-sm font-semibold text-slate-600 transition hover:bg-slate-100 hover:text-[#071E49]">Data MBG</a>
                <a href="{{ route('juknis.index') }}" class="rounded-lg px-3 py-2 text-sm font-semibold text-slate-600 transition hover:bg-slate-100 hover:text-[#071E49]">Juknis</a>
            </nav>

            <a href="{{ url('/') }}" class="inline-flex shrink-0 items-center rounded-full bg-[#C9A45C] px-4 py-2.5 text-sm font-normal text-[#071E49] shadow-sm transition hover:scale-105 hover:bg-[#DBBB7F]">Radar MBG</a>
        </div>
    </header>

    <main class="w-full flex-1">
        {{ $slot }}
    </main>

    <x-site-footer :settings="$settings" />
</body>
</html>
