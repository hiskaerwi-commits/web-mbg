<x-layouts.public :settings="$settings">
    <section class="bg-[#f8f9fb] py-12 sm:py-16 lg:py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl">
                <p class="text-sm font-medium text-slate-600 sm:text-base">Dokumen Petunjuk Teknis (Juknis) BGN</p>
                <h1 class="mt-3 text-3xl font-semibold leading-tight tracking-tight text-[#071E49] sm:text-4xl">Seluruh juknis yang pernah <span class="text-[#C9A45C]">diterbitkan,</span> tersimpan untuk akses penuh.</h1>
                <p class="mt-4 text-sm leading-relaxed text-slate-600 sm:text-base">Kumpulan dokumen resmi Petunjuk Teknis (Juknis) yang dapat digunakan sebagai acuan dalam pelaksanaan program, kegiatan, maupun kebijakan terkait gizi nasional.</p>
            </div>

            <form action="{{ route('juknis.index') }}" method="get" role="search" class="mt-10 flex flex-col gap-3 rounded-3xl border border-slate-200 bg-white p-4 sm:flex-row sm:rounded-full">
                <label class="min-w-0 flex-1">
                    <span class="sr-only">Cari dokumen, tahun penetapan, atau nomor Juknis</span>
                    <input name="search" type="search" value="{{ $search }}" maxlength="200" placeholder="Cari dokumen Juknis, tahun penetapan atau nomor Juknis" class="w-full rounded-full border border-slate-200 px-5 py-3 text-sm outline-none focus:border-[#071E49] focus:ring-2 focus:ring-[#B5E0EA]">
                </label>
                <label class="sm:w-64">
                    <span class="sr-only">Jenis dokumen</span>
                    <select name="jenis" class="w-full rounded-full border border-slate-200 bg-white px-4 py-3 text-sm outline-none focus:border-[#071E49] focus:ring-2 focus:ring-[#B5E0EA]">
                        <option value="">Semua</option>
                        @foreach ($types as $type)
                            <option value="{{ $type }}" @selected($selectedType === $type)>{{ $type }}</option>
                        @endforeach
                    </select>
                </label>
                <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-full border border-[#071E49] bg-[#D4B266] px-5 py-3 text-sm font-medium text-[#071E49] transition hover:bg-[#DBBB7F]">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 5 5"/></svg>
                    Cari
                </button>
            </form>

            <div class="mt-5 flex flex-wrap items-center justify-between gap-3 text-xs text-slate-500">
                <p>{{ $documents->total() }} dokumen{{ $search || $selectedType ? ' ditemukan' : ' tersedia' }}.</p>
                <p>Sumber: <a href="{{ $sourceUrl }}" target="_blank" rel="noopener noreferrer" class="underline underline-offset-2">BGN Pusat</a> &middot; Diperbarui {{ \Illuminate\Support\Carbon::parse($fetchedAt)->timezone('Asia/Jakarta')->translatedFormat('d F Y') }}</p>
            </div>

            <div class="mt-6 space-y-4">
                @forelse ($documents as $document)
                    <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6" data-juknis-document>
                        <h2 class="text-lg font-semibold leading-snug text-[#071E49] sm:text-xl">{{ $document['title'] }}</h2>
                        <div class="mt-3 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                            <div class="flex min-w-0 flex-wrap items-start gap-2 text-xs leading-5 text-slate-600">
                                <span class="rounded-lg border border-slate-200 bg-slate-50 px-2 py-1">{{ $document['year'] }}</span>
                                <span class="rounded-lg border border-slate-200 bg-slate-50 px-2 py-1">{{ $document['type'] }}</span>
                                <span class="max-w-full rounded-lg border border-slate-200 bg-slate-50 px-2 py-1 break-words">{{ $document['number'] }}</span>
                                <span class="inline-flex items-center gap-1 rounded-lg border border-emerald-100 bg-emerald-50 px-2 py-1 text-emerald-700">
                                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/></svg>
                                    {{ $document['status'] }}
                                </span>
                            </div>
                            <a href="{{ $document['download_url'] }}" target="_blank" rel="noopener noreferrer" class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-[#071E49] px-4 py-3 text-sm font-medium text-white transition hover:bg-[#123469] lg:bg-transparent lg:px-0 lg:py-1 lg:text-blue-600 lg:hover:bg-transparent lg:hover:underline" aria-label="Unduh {{ $document['title'] }}">
                                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3v12m-4-4 4 4 4-4M4 16v5h16v-5"/></svg>
                                Unduh Dokumen
                            </a>
                        </div>
                        @if ($document['description'] && trim($document['description'], "- \n\r\t") !== '')
                            <details class="mt-5 text-sm leading-relaxed text-slate-600">
                                <summary class="cursor-pointer font-medium text-[#071E49]">Keterangan dokumen</summary>
                                <p class="mt-3 whitespace-pre-line">{{ $document['description'] }}</p>
                            </details>
                        @endif
                    </article>
                @empty
                    <div class="rounded-3xl border border-slate-200 bg-white px-6 py-14 text-center">
                        <h2 class="text-lg font-semibold">Dokumen tidak ditemukan</h2>
                        <p class="mt-2 text-sm text-slate-500">Coba kata kunci lain atau pilih jenis dokumen yang berbeda.</p>
                        <a href="{{ route('juknis.index') }}" class="mt-5 inline-flex rounded-full bg-[#071E49] px-5 py-3 text-sm font-medium text-white">Lihat semua dokumen</a>
                    </div>
                @endforelse
            </div>
            @if ($documents->hasPages())
                <div class="mt-8">{{ $documents->links() }}</div>
            @endif
        </div>
    </section>
</x-layouts.public>
