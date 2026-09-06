@props(['networkLinks', 'networkSearch', 'networkPerPage'])

<section id="jaringan" class="bg-white">
    <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8 lg:py-24">
        <div class="text-center">
            <p class="text-sm font-bold uppercase tracking-[0.18em] text-[#071E49]">Jaringan Kami</p>
            <h2 class="mt-3 text-3xl font-extrabold tracking-tight text-[#071E49] sm:text-4xl">Jaringan wilayah di <span class="text-[#C9A45C]">berbagai provinsi.</span></h2>
            <p class="mx-auto mt-3 max-w-2xl text-sm leading-relaxed text-slate-500">Pilih nama jaringan untuk mengunjungi portal wilayah terkait.</p>
        </div>

        <form action="{{ url('/') }}" method="get" class="mt-10 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <label class="text-sm font-medium text-[#071E49]">Tampilkan
                <select name="network_per_page" onchange="this.form.submit()" class="ml-2 rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-[#071E49] outline-none focus:border-[#071E49] focus:ring-2 focus:ring-[#B5E0EA]">
                    @foreach ([10, 25, 50] as $option)
                        <option value="{{ $option }}" @selected($networkPerPage === $option)>{{ $option }}</option>
                    @endforeach
                </select>
                <span class="ml-1">entri</span>
            </label>
            <label class="flex items-center gap-2 text-sm font-medium text-[#071E49]">Cari:
                <input name="network_search" value="{{ $networkSearch }}" type="search" placeholder="Cari jaringan" class="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-[#071E49] outline-none transition focus:border-[#071E49] focus:ring-2 focus:ring-[#B5E0EA] sm:w-72">
            </label>
        </form>

        <div class="mt-4 overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200/70">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[640px] text-left text-sm">
                    <thead class="bg-[#E4F1F8] text-[#071E49]">
                        <tr><th class="px-5 py-3 font-bold sm:px-6">Nama jaringan wilayah</th><th class="px-5 py-3 text-right font-bold sm:px-6">Tujuan</th></tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse ($networkLinks as $networkLink)
                            <tr class="transition hover:bg-slate-50"><td class="px-5 py-2.5 font-semibold text-[#071E49] sm:px-6">{{ $networkLink->name }}</td><td class="px-5 py-2.5 text-right sm:px-6">
                                @if ($networkLink->url)
                                    <a href="{{ $networkLink->url }}" target="_blank" rel="noopener" title="Kunjungi {{ $networkLink->name }}" class="inline-flex items-center gap-1.5 rounded-lg bg-[#06183A] px-3 py-1.5 text-xs font-bold text-white shadow-sm transition hover:bg-[#04112A] focus:outline-none focus:ring-2 focus:ring-[#071E49] focus:ring-offset-2"><span>Kunjungi</span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" class="size-3.5" aria-hidden="true"><path d="M14 3h7v7"></path><path d="M10 14 21 3"></path><path d="M21 14v6a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h6"></path></svg></a>
                                @else
                                    <span class="inline-flex items-center gap-1.5 rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-400">Segera hadir</span>
                                @endif
                            </td></tr>
                        @empty
                            <tr><td colspan="2" class="px-5 py-10 text-center text-slate-500">Jaringan tidak ditemukan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($networkLinks->count())
            <div class="mt-5 flex flex-col gap-4 text-sm text-slate-600 sm:flex-row sm:items-center sm:justify-between">
                <p>Menampilkan {{ $networkLinks->firstItem() }} sampai {{ $networkLinks->lastItem() }} dari {{ $networkLinks->total() }} entri</p>
                <div>{{ $networkLinks->onEachSide(1)->links() }}</div>
            </div>
        @endif
    </div>
</section>
