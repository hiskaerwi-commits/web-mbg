@php
    $categoryLabel = $article->category ?: 'Berita';
@endphp

<x-layouts.public :settings="$settings">
    <article class="mx-auto max-w-4xl px-4 py-12 sm:px-6 lg:px-8 lg:py-16">
        <nav class="flex flex-wrap items-center gap-2 text-sm text-slate-500">
            <a href="{{ url('/#berita') }}" class="hover:text-[#071E49] hover:underline">Berita</a>
            <span>/</span>
            <span>{{ $categoryLabel }}</span>
            <span>/</span>
            <span class="text-slate-400">{{ $article->title }}</span>
        </nav>

        <h1 class="mt-4 text-2xl font-extrabold leading-tight tracking-tight text-[#071E49] sm:text-3xl">{{ $article->title }}</h1>

        <div class="mt-4 flex flex-wrap items-center justify-between gap-2 border-b border-slate-200 pb-5 text-sm text-slate-500">
            <p>Nomor: {{ $article->document_number ?: '-' }}</p>
            <p>{{ $categoryLabel }} &middot; {{ $article->published_at->translatedFormat('d F Y') }}</p>
        </div>

        @if ($article->image_url)
            <img src="{{ $article->image_url }}" alt="{{ $article->title }}" class="mt-8 w-full rounded-lg object-cover shadow-sm">
        @endif

        @if ($article->source)
            <p class="mt-3 text-sm text-slate-500">
                Sumber:
                @if ($article->url)
                    <a href="{{ $article->url }}" target="_blank" rel="noopener noreferrer" class="text-[#071E49] underline hover:text-[#0a2a63]">{{ $article->source }}</a>
                @else
                    <span class="text-slate-700">{{ $article->source }}</span>
                @endif
            </p>
        @endif

        <div class="rich-content mt-8 text-base leading-relaxed text-slate-700">
            {!! $article->content !!}
        </div>
    </article>
</x-layouts.public>
