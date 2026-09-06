@php
    $maternalProgramImage = asset('images/programs/ibu-hamil-menyusui.png');
    $targets = [
        [
            'title' => 'Peserta Didik',
            'subtitle' => 'SD, SMP, SMA Sederajat, Santri',
            'description' => 'Kami berfokus pada jenjang pendidikan anak usia dini, pendidikan dasar, dan pendidikan menengah di berbagai lingkungan, meliputi pendidikan umum, kejuruan, keagamaan, pendidikan khusus, layanan khusus, serta pesantren.',
            'image' => asset('images/programs/peserta-didik.png'),
        ],
        [
            'title' => 'Anak - Anak',
            'subtitle' => 'Anak usia di Bawah 5 Tahun',
            'description' => 'Pemantauan dan dukungan gizi intensif bagi anak-anak dalam usia emas perkembangan mereka.',
            'image' => asset('images/programs/anak-anak.png'),
        ],
        [
            'title' => 'Ibu Hamil & Menyusui',
            'subtitle' => 'Gizi untuk Ibu Hamil & Menyusui',
            'description' => 'Memastikan kesehatan gizi ibu hamil dan menyusui demi kesehatan ibu dan bayi yang optimal hingga mendukung pertumbuhan dan perkembangan bayi mereka.',
            'image' => $maternalProgramImage,
        ],
    ];
    $newsPlaceholderImages = [
        asset('images/programs/peserta-didik.png'),
        asset('images/programs/anak-anak.png'),
        $maternalProgramImage,
    ];
    $newsPlaceholder = fn ($id) => $newsPlaceholderImages[$id % count($newsPlaceholderImages)];
@endphp

<x-layouts.public :settings="$settings">
    <section class="overflow-hidden bg-[#071E49]">
        <div class="mx-auto grid max-w-7xl gap-10 px-4 py-16 sm:px-6 md:py-20 lg:grid-cols-[1.15fr_.85fr] lg:px-8 lg:py-24">
            <div class="max-w-3xl">
                <span class="inline-flex flex-wrap items-center gap-1.5 rounded-full bg-white/10 px-3 py-1.5 text-xs font-bold tracking-wide text-[#B5E0EA] ring-1 ring-white/15">
                    <span>BADAN GIZI NASIONAL</span>
                    @if ($settings->province_name)
                        <span class="text-white/40">&middot;</span>
                        <span class="text-[#C9A45C]">{{ $settings->province_name }}</span>
                    @endif
                </span>
                <h1 class="mt-5 text-3xl font-extrabold leading-[1.15] tracking-tight text-white sm:text-4xl lg:text-5xl">Mewujudkan Generasi Sehat dan Bergizi menuju <span class="text-[#C9A45C]">Indonesia Emas</span>.</h1>
                <p class="mt-6 max-w-2xl text-base leading-relaxed text-slate-200 sm:text-lg">{{ $settings->display_name }} menghadirkan informasi program gizi, data Makan Bergizi Gratis (MBG), dokumen, dan layanan publik dalam satu portal yang mudah diakses.</p>
                <div class="mt-8 flex flex-wrap gap-3"><a href="#sasaran" class="inline-flex items-center rounded-xl bg-white px-5 py-3 text-sm font-bold text-[#071E49] shadow-sm transition hover:bg-slate-100">Lihat Program</a><a href="#tentang" class="inline-flex items-center rounded-xl border border-white/30 px-5 py-3 text-sm font-bold text-white transition hover:bg-white/10">Tentang Kami</a></div>
            </div>
            <div class="relative mx-auto w-full max-w-xl self-center">
                <div class="rounded-3xl bg-white p-5 shadow-2xl shadow-black/20 ring-1 ring-white/20 sm:p-6">
                    <div class="flex items-start gap-3"><span class="grid size-11 shrink-0 place-items-center rounded-xl bg-[#071E49] text-xl font-bold text-white">✓</span><div><p class="text-sm font-bold text-[#071E49]">Layanan Gizi Terpusat</p><p class="mt-1 text-sm leading-relaxed text-slate-500">Seluruh program, data, dan publikasi gizi tersedia dalam satu tempat.</p></div></div>
                    <div class="mt-5 grid grid-cols-2 gap-3 border-t border-slate-100 pt-5"><div class="rounded-xl bg-slate-100 p-4"><p class="text-2xl font-extrabold text-[#071E49]">24/7</p><p class="mt-1 text-xs font-medium text-slate-500">Akses informasi</p></div><div class="rounded-xl bg-[#EAF5E2] p-4"><p class="text-2xl font-extrabold text-[#071E49]">Terbuka</p><p class="mt-1 text-xs font-medium text-slate-500">Untuk masyarakat</p></div></div>
                </div>
                <div class="absolute -right-3 -top-3 hidden size-24 rounded-full border-[14px] border-[#92D05D] opacity-90 lg:block"></div>
            </div>
        </div>
    </section>

    <section id="berita" class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8 lg:py-20">
        <div class="grid gap-6 lg:grid-cols-[1fr_360px]">
            <div class="relative h-fit self-start overflow-hidden rounded-2xl bg-slate-200 shadow-sm" data-berita-carousel>
                <div class="relative aspect-[4/3] sm:aspect-[16/9]">
                    @forelse ($beritaBgnHighlight as $index => $slide)
                        <a href="{{ route('news.show', $slide->slug) }}" class="absolute inset-0 transition-opacity duration-500 {{ $index === 0 ? 'opacity-100' : 'pointer-events-none opacity-0' }}" data-berita-slide>
                            <img src="{{ $slide->image_url ?? $newsPlaceholder($slide->id) }}" alt="{{ $slide->title }}" class="size-full object-cover" loading="lazy">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/75 via-black/10 to-transparent"></div>
                            <p class="absolute inset-x-6 bottom-6 text-lg font-extrabold leading-snug text-white sm:text-2xl">{{ $slide->title }}</p>
                        </a>
                    @empty
                        <div class="flex size-full items-center justify-center text-sm font-semibold text-slate-500">Belum ada berita sorotan.</div>
                    @endforelse
                    @if ($beritaBgnHighlight->count() > 1)
                        <button type="button" class="absolute left-4 top-1/2 grid size-11 -translate-y-1/2 place-items-center rounded-full bg-black/30 text-2xl text-white backdrop-blur transition hover:bg-black/50" data-berita-prev aria-label="Slide sebelumnya">&larr;</button>
                        <button type="button" class="absolute right-4 top-1/2 grid size-11 -translate-y-1/2 place-items-center rounded-full bg-black/30 text-2xl text-white backdrop-blur transition hover:bg-black/50" data-berita-next aria-label="Slide berikutnya">&rarr;</button>
                        <div class="absolute inset-x-0 bottom-5 flex justify-center gap-2">
                            @foreach ($beritaBgnHighlight as $index => $slide)
                                <button type="button" class="h-1.5 w-9 rounded-full transition-colors sm:w-11 {{ $index === 0 ? 'bg-[#C9A45C]' : 'bg-white/40' }}" data-berita-dot="{{ $index }}" aria-label="Slide {{ $index + 1 }}"></button>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <div>
                <h3 class="text-xl font-medium tracking-tight text-[#071E49]">Berita BGN</h3>
                <div class="mt-4 flex flex-col divide-y divide-slate-200">
                    @forelse ($beritaBgnList as $item)
                        <a href="{{ route('news.show', $item->slug) }}" class="flex gap-3 py-3 first:pt-0">
                            <img src="{{ $item->image_url ?? $newsPlaceholder($item->id) }}" alt="{{ $item->title }}" class="size-16 shrink-0 rounded-xl object-cover">
                            <div class="min-w-0">
                                <p class="line-clamp-2 text-[15px] font-medium leading-snug text-[#071E49]">{{ $item->title }}</p>
                                <p class="mt-1.5 text-xs font-medium"><span class="text-[#C9A45C]">BGN</span><span class="text-[#071E49]"> - {{ $item->published_at->translatedFormat('d F Y') }}</span></p>
                            </div>
                        </a>
                    @empty
                        <p class="py-3 text-sm text-slate-500">Belum ada berita BGN.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="mt-12 flex items-end justify-between gap-4">
            <h3 class="text-xl font-extrabold tracking-tight text-[#071E49]">Berita Nasional</h3>
            <a href="{{ route('news.index') }}" class="inline-flex shrink-0 items-center gap-2 rounded-full border border-slate-200 px-4 py-2 text-sm font-semibold text-[#071E49] shadow-sm transition hover:bg-slate-50">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" class="size-4" aria-hidden="true"><path d="M4 4h16v14H8l-4 4z"></path><path d="M8 9h8"></path><path d="M8 13h5"></path></svg>
                Lihat Cerita Kami
            </a>
        </div>

        <div class="mt-6 -mr-4 flex gap-6 overflow-hidden pr-4 sm:-mr-6 sm:pr-6 lg:-mr-8 lg:pr-8">
            @forelse ($beritaNasional as $item)
                <a href="{{ $item->url ?: route('news.show', $item->slug) }}" @if ($item->url) target="_blank" rel="noopener noreferrer" @endif class="group relative block aspect-[16/10] w-[290px] shrink-0 overflow-hidden rounded-2xl sm:w-[340px]">
                    <img src="{{ $item->image_url ?? $newsPlaceholder($item->id) }}" alt="{{ $item->title }}" class="size-full object-cover transition duration-300 group-hover:scale-105" loading="lazy">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/15 to-transparent"></div>
                    <div class="absolute inset-x-0 bottom-0 p-4">
                        <p class="line-clamp-2 text-sm font-bold leading-snug text-white sm:text-base">{{ $item->title }}</p>
                        <p class="mt-2 text-xs font-medium text-white/70">{{ $item->source ?: 'Media Nasional' }} &middot; {{ $item->published_at->translatedFormat('d M Y') }}</p>
                    </div>
                </a>
            @empty
                <p class="text-sm text-slate-500">Belum ada berita nasional.</p>
            @endforelse
        </div>
    </section>

    @php
        $collaborationLogos = [
            ['file' => 'kemendagri.png', 'name' => 'Kementerian Dalam Negeri'], ['file' => 'kemenkeu.png', 'name' => 'Kementerian Keuangan'], ['file' => 'bappenas.png', 'name' => 'BAPPENAS'], ['file' => 'panrb.png', 'name' => 'PANRB'], ['file' => 'bumn.png', 'name' => 'BUMN'], ['file' => 'komdigi.png', 'name' => 'KOMDIGI'], ['file' => 'bssn.png', 'name' => 'Badan Siber dan Sandi Negara'], ['file' => 'kemendes.png', 'name' => 'Kementerian Desa'], ['file' => 'kemenkes.png', 'name' => 'Kemenkes'], ['file' => 'Logo-kemendikbud.png', 'name' => 'Kemendikbud'], ['file' => 'kementan.png', 'name' => 'Kementan'], ['file' => 'KKP.png', 'name' => 'KKP'], ['file' => 'bapanas.png', 'name' => 'Bapanas'], ['file' => 'bpom.png', 'name' => 'BPOM'], ['file' => 'kemenko-pangan.png', 'name' => 'Kemenko Pangan'],
        ];
    @endphp
    <section id="tentang" class="relative w-full overflow-hidden bg-[#071E49] py-9">
        <img src="{{ asset('images/decorations/pattern4.png') }}" alt="" class="pointer-events-none absolute -left-24 top-1/2 w-56 -translate-y-1/2 opacity-30 brightness-0 invert lg:w-64">
        <img src="{{ asset('images/decorations/pattern4.png') }}" alt="" class="pointer-events-none absolute -right-24 top-1/2 w-56 -translate-y-1/2 rotate-180 opacity-30 brightness-0 invert lg:w-64">
        <div class="relative mx-auto max-w-7xl px-4 text-center sm:px-6 lg:px-8">
            <h2 class="text-sm font-extrabold tracking-tight text-white sm:text-base">Berkolaborasi dan Bersinergi Bersama</h2>
            <div class="mt-5 flex gap-3 overflow-x-auto px-2 pb-1 sm:justify-center lg:flex-nowrap lg:overflow-visible">
                @foreach ($collaborationLogos as $logo)
                    <div class="grid size-14 shrink-0 place-items-center rounded-xl bg-white/60 p-2 shadow-sm backdrop-blur lg:size-16">
                        <img src="{{ asset('images/collaboration/' . $logo['file']) }}" alt="{{ $logo['name'] }}" class="size-full object-contain" loading="lazy">
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section id="sasaran" class="relative overflow-hidden border-y border-slate-200 bg-slate-50 py-16 lg:py-20">
        <img src="{{ asset('images/decorations/pattern4.png') }}" alt="" class="pointer-events-none absolute -right-10 top-20 hidden w-80 opacity-[0.08] lg:block">
        <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div>
                <div class="max-w-3xl">
                    <p class="text-base font-semibold text-slate-600 sm:text-lg">Sasaran BGN</p>
                    <h2 class="mt-2 text-3xl font-extrabold leading-tight tracking-tight text-[#071E49] sm:text-4xl lg:text-5xl">Misi Badan Gizi Nasional (BGN) untuk <span class="text-[#C9A45C]">Menuju Indonesia Emas</span></h2>
                    <p class="mt-5 text-sm leading-relaxed text-slate-600 sm:text-base">Program komprehensif yang dirancang untuk memastikan setiap individu mendapatkan asupan gizi optimal, mendukung tercapainya Indonesia Emas melalui generasi yang sehat dan berkualitas.</p>
                </div>
            </div>

            <div class="mt-10 grid gap-6 lg:mt-12 lg:grid-cols-4">
                <article class="relative aspect-[2/3] overflow-hidden rounded-2xl bg-[#E5D5B4] p-7 shadow-sm lg:p-8">
                    <h3 class="max-w-48 text-2xl font-extrabold leading-tight text-slate-900 lg:text-3xl">Sasaran Pemenuhan Gizi BGN</h3>
                    <p class="mt-3 max-w-52 text-sm leading-relaxed text-slate-600 lg:text-base">Kami mendukung kesehatan gizi melalui berbagai program untuk memastikan setiap individu mendapatkan kebutuhan gizi yang optimal.</p>
                    <div class="absolute -bottom-12 -right-12 size-40 rotate-45 rounded-[2rem] border-[18px] border-[#071E49] opacity-95"></div>
                    <div class="absolute -bottom-7 -right-4 size-24 rotate-45 rounded-3xl border-[14px] border-[#C9A45C]"></div>
                </article>

                @foreach ($targets as $target)
                    <article class="target-flip aspect-[2/3]">
                        <div class="target-flip__inner relative size-full rounded-2xl">
                            <div class="target-flip__face absolute inset-0 overflow-hidden rounded-2xl bg-[#E5D5B4] shadow-sm">
                                <img src="{{ $target['image'] }}" alt="Ilustrasi {{ $target['title'] }}" class="size-full object-contain" loading="lazy">
                            </div>
                            <div class="target-flip__face target-flip__back absolute inset-0 flex flex-col justify-start overflow-hidden rounded-2xl bg-[#E5D5B4] p-7 shadow-sm lg:p-8">
                                <p class="text-sm font-bold uppercase tracking-[0.16em] text-[#071E49]">Sasaran BGN</p>
                                <h3 class="mt-2 text-2xl font-extrabold leading-tight text-slate-900">{{ $target['title'] }}</h3>
                                <p class="mt-3 text-sm leading-relaxed text-slate-700">{{ $target['description'] }}</p>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

<section id="dokumen" class="bg-[#EAF0F7]"><div class="mx-auto flex max-w-7xl flex-col gap-6 px-4 py-12 sm:px-6 md:flex-row md:items-center md:justify-between lg:px-8"><div><p class="text-sm font-bold uppercase tracking-[0.18em] text-[#071E49]">Dokumen Publik</p><h2 class="mt-3 text-2xl font-extrabold tracking-tight text-[#071E49]">Butuh pedoman atau <span class="text-[#C9A45C]">dokumen resmi?</span></h2><p class="mt-2 text-sm text-slate-600">Temukan petunjuk teknis dan pedoman resmi Badan Gizi Nasional.</p></div><a href="{{ route('juknis.index') }}" class="inline-flex shrink-0 items-center justify-center rounded-xl bg-[#06183A] px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-[#04112A]">Buka Dokumen</a></div></section>

    <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8 lg:py-24"><div class="rounded-3xl bg-[#071E49] px-6 py-10 text-center shadow-xl shadow-[#071E49]/15 sm:px-12"><p class="text-sm font-bold uppercase tracking-[0.18em] text-[#C9A45C]">Kanal Pengaduan</p><h2 class="mx-auto mt-3 max-w-2xl text-3xl font-extrabold tracking-tight text-white">Sampaikan pertanyaan, saran, atau <span class="text-[#C9A45C]">pengaduan Anda.</span></h2><p class="mx-auto mt-4 max-w-xl text-sm leading-relaxed text-slate-200">Kanal pengaduan dapat diarahkan ke WhatsApp, email, link eksternal, atau form internal dari Website Settings.</p><a href="#kontak" class="mt-7 inline-flex rounded-xl bg-white px-5 py-3 text-sm font-bold text-[#071E49] transition hover:bg-slate-100">Hubungi Kami</a></div></section>

    <x-public.network-links :network-links="$networkLinks" :network-search="$networkSearch" :network-per-page="$networkPerPage" />
</x-layouts.public>
