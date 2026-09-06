@php
    $placeholderImages = [
        asset('images/programs/peserta-didik.png'),
        asset('images/programs/anak-anak.png'),
        asset('images/programs/ibu-hamil-menyusui.png'),
    ];
    $placeholder = fn ($id) => $placeholderImages[$id % count($placeholderImages)];
@endphp

<x-layouts.public :settings="$settings">
    <section class="bg-slate-100 py-12 sm:py-16 lg:py-20">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl">
                <p class="text-sm font-medium text-slate-500">Berita Kami</p>
                <h1 class="mt-2 text-3xl font-medium tracking-tight text-[#071E49] sm:text-4xl">Rangkaian cerita <span class="text-[#C9A45C]">tentang BGN</span></h1>
                <p class="mt-4 text-sm leading-relaxed text-slate-600 sm:text-base">Publikasi resmi Badan Gizi Nasional, meliputi artikel, siaran pers, dan dokumentasi foto.</p>
            </div>

            <div class="mt-8 flex flex-wrap gap-2">
                <a href="{{ route('news.index') }}" class="rounded-lg border px-4 py-2 text-sm font-medium transition {{ $selectedCategory === '' ? 'border-[#071E49] bg-[#071E49] text-white' : 'border-slate-200 bg-white text-[#071E49] hover:border-[#071E49]' }}">Semua</a>
                @foreach ($categories as $category)
                    <a href="{{ route('news.index', ['kategori' => $category]) }}" class="rounded-lg border px-4 py-2 text-sm font-medium transition {{ $selectedCategory === $category ? 'border-[#071E49] bg-[#071E49] text-white' : 'border-slate-200 bg-white text-[#071E49] hover:border-[#071E49]' }}">{{ $category }}</a>
                @endforeach
            </div>

            <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @forelse ($news as $article)
                    <a href="{{ route('news.show', $article->slug) }}" class="group overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200/80 transition hover:-translate-y-1 hover:shadow-lg">
                        <img src="{{ $article->image_url ?? $placeholder($article->id) }}" alt="{{ $article->title }}" class="aspect-[16/10] w-full object-cover transition duration-300 group-hover:scale-105" loading="lazy">
                        <div class="p-5">
                            <h2 class="line-clamp-2 text-base font-semibold leading-snug text-[#071E49]">{{ $article->title }}</h2>
                            <p class="mt-3 text-sm"><span class="text-[#C9A45C]">BGN</span><span class="text-slate-500"> &middot; {{ $article->category }} &middot; {{ $article->published_at->translatedFormat('d F Y') }}</span></p>
                        </div>
                    </a>
                @empty
                    <p class="text-sm text-slate-500 sm:col-span-2 lg:col-span-3">Belum ada publikasi pada kategori ini.</p>
                @endforelse
            </div>

            @if ($news->hasPages())
                <div class="mt-10">{{ $news->links() }}</div>
            @endif
        </div>
    </section>
</x-layouts.public>
