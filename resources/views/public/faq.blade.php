<x-layouts.public :settings="$settings">
    <section class="bg-[#f8f9fb] py-12 sm:py-16 lg:py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl">
                <p class="text-sm font-medium text-slate-600 sm:text-base">FAQ BGN</p>
                <h1 class="mt-3 text-3xl font-semibold leading-tight tracking-tight text-[#071E49] sm:text-4xl">Temukan jawaban untuk <span class="text-[#C9A45C]">mengenal BGN</span> lebih dekat.</h1>
                <p class="mt-4 text-sm leading-relaxed text-slate-600 sm:text-base">Informasi seputar pelaksanaan Makan Bergizi Gratis, penerima manfaat, keamanan pangan, serta pendaftaran dan kerja sama mitra. Pilih pertanyaan untuk membaca jawabannya.</p>
            </div>

            <form action="{{ route('faq.index') }}" method="get" role="search" class="mt-8 flex flex-col gap-3 rounded-3xl border border-slate-200 bg-white p-3 sm:flex-row sm:rounded-full">
                <label class="min-w-0 flex-1">
                    <span class="sr-only">Cari pertanyaan atau kata kunci FAQ</span>
                    <input name="search" type="search" value="{{ $search }}" maxlength="200" placeholder="Cari pertanyaan atau kata kunci, misalnya SPPG atau mitra" class="w-full rounded-full border border-slate-200 px-5 py-3 text-sm outline-none focus:border-[#071E49] focus:ring-2 focus:ring-[#B5E0EA]">
                </label>
                <button type="submit" class="rounded-full border border-[#071E49] bg-[#D4B266] px-7 py-3 text-sm font-medium text-[#071E49] transition hover:bg-[#DBBB7F]">Cari</button>
            </form>
            <div class="mt-4 flex flex-wrap items-center justify-between gap-3 text-xs text-slate-500">
                <p>{{ $questionCount }} pertanyaan dalam {{ $categories->count() }} kategori{{ $search ? ' ditemukan' : '' }}.</p>
                @if ($search)
                    <a href="{{ route('faq.index') }}" class="font-medium text-[#071E49] underline underline-offset-2">Tampilkan semua FAQ</a>
                @endif
            </div>

            <div class="mt-12 space-y-12 lg:mt-16 lg:space-y-20">
                @forelse ($categories as $category)
                    <section class="grid gap-5 lg:grid-cols-5 lg:gap-12" aria-labelledby="{{ $category['id'] }}">
                        <h2 id="{{ $category['id'] }}" class="text-xl font-semibold leading-snug text-[#071E49] sm:text-2xl lg:col-span-2">{{ $category['title'] }}</h2>
                        <div class="min-w-0 divide-y divide-slate-200 border-y border-slate-200 lg:col-span-3">
                            @foreach ($category['items'] as $item)
                                <details id="{{ $item['id'] }}" class="group" @if ($search) open @endif data-faq-item>
                                    <summary class="flex cursor-pointer list-none items-start justify-between gap-5 py-5 text-left text-base font-medium leading-relaxed text-[#071E49] outline-none focus-visible:rounded-lg focus-visible:ring-2 focus-visible:ring-[#C9A45C] [&::-webkit-details-marker]:hidden">
                                        <span>{{ $item['question'] }}</span>
                                        <svg class="mt-1 size-5 shrink-0 transition-transform group-open:rotate-180 motion-reduce:transition-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
                                    </summary>
                                    <div class="space-y-4 pb-6 pr-2 text-sm leading-7 break-words text-slate-600" data-faq-answer>
                                        @foreach ($item['blocks'] as $block)
                                            @if ($block['type'] === 'list')
                                                @if ($block['ordered'])
                                                    <ol class="list-decimal space-y-2 pl-5">
                                                        @foreach ($block['items'] as $text)<li>{{ $text }}</li>@endforeach
                                                    </ol>
                                                @else
                                                    <ul class="list-disc space-y-2 pl-5">
                                                        @foreach ($block['items'] as $text)<li>{{ $text }}</li>@endforeach
                                                    </ul>
                                                @endif
                                            @else
                                                <p>{{ $block['text'] }}</p>
                                            @endif
                                        @endforeach
                                    </div>
                                </details>
                            @endforeach
                        </div>
                    </section>
                @empty
                    <div class="rounded-3xl border border-slate-200 bg-white px-6 py-14 text-center">
                        <h2 class="text-lg font-semibold">Pertanyaan tidak ditemukan</h2>
                        <p class="mt-2 text-sm text-slate-500">Coba kata kunci lain atau tampilkan kembali seluruh FAQ.</p>
                        <a href="{{ route('faq.index') }}" class="mt-5 inline-flex rounded-full bg-[#071E49] px-5 py-3 text-sm font-medium text-white">Lihat semua FAQ</a>
                    </div>
                @endforelse
            </div>
            <p class="mt-12 border-t border-slate-200 pt-6 text-xs leading-relaxed text-slate-500">Rujukan resmi: <a href="{{ $referenceUrl }}" target="_blank" rel="noopener noreferrer" class="underline underline-offset-2">BGN Pusat</a>. Pembaruan halaman {{ \Illuminate\Support\Carbon::parse($fetchedAt)->timezone('Asia/Jakarta')->translatedFormat('d F Y') }}.</p>
        </div>
    </section>
</x-layouts.public>
