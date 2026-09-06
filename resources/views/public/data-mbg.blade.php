<x-layouts.public :settings="$settings">
    <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8 lg:py-16">
        <nav class="flex flex-wrap items-center gap-2 text-sm text-slate-500">
            <a href="{{ url('/') }}" class="hover:text-[#071E49] hover:underline">Beranda</a>
            <span>/</span>
            <span class="text-slate-400">Data MBG</span>
        </nav>

        <p class="mt-4 text-sm font-bold uppercase tracking-[0.18em] text-[#071E49]">Data MBG</p>
        <div class="mt-3 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight text-[#071E49] sm:text-4xl">Rekap Makan Bergizi Gratis {{ $mbgProvinceName }}</h1>
                <p class="mt-2 text-sm text-slate-600">
                    @if ($mbgYear) Tahun data {{ $mbgYear }}. @endif
                    {{ $mbgProvinceCode ? 'Ringkasan berdasarkan rekap provinsi.' : 'Ringkasan seluruh wilayah berdasarkan rekap provinsi yang tersedia.' }}
                    @if ($mbgLastUpdated)
                        Data terakhir diambil {{ \Illuminate\Support\Carbon::parse($mbgLastUpdated)->timezone('Asia/Jakarta')->translatedFormat('d F Y, H:i') }} WIB.
                    @endif
                </p>
            </div>
            <a href="{{ url('/api/v1/mbg/education-recaps').'?'.http_build_query(array_filter(['province_code' => $mbgProvinceCode, 'level' => $mbgLevel, 'year' => $mbgYear])) }}" class="text-sm font-bold text-[#071E49] hover:underline">Lihat API data</a>
        </div>

        @if (! $settings->mbg_province_code && $mbgProvinceCode)
            <a href="{{ route('data-mbg', ['mbg_level' => $mbgLevel]) }}" class="mt-6 inline-flex text-sm font-bold text-[#071E49] hover:underline">&larr; Kembali ke rekap nasional</a>
        @endif

        <div class="mt-6 flex gap-2 overflow-x-auto pb-2">
            <a href="{{ route('data-mbg', ['mbg_province' => $mbgProvinceCode]) }}" class="shrink-0 rounded-full px-4 py-2 text-sm font-bold {{ ! $mbgLevel ? 'bg-[#071E49] text-white' : 'bg-white text-slate-600 ring-1 ring-slate-200' }}">Semua</a>
            @foreach ($mbgLevels as $level)
                <a href="{{ route('data-mbg', ['mbg_province' => $mbgProvinceCode, 'mbg_level' => $level]) }}" class="shrink-0 rounded-full px-4 py-2 text-sm font-bold {{ $mbgLevel === $level ? 'bg-[#071E49] text-white' : 'bg-white text-slate-600 ring-1 ring-slate-200' }}">{{ $level }}</a>
            @endforeach
        </div>

        @if ($mbgProvinceRecap)
            <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-2xl bg-white p-5 shadow-sm"><p class="text-sm font-semibold text-slate-500">Penerima manfaat</p><p class="mt-2 text-3xl font-extrabold text-[#071E49]">{{ number_format($mbgProvinceRecap->beneficiaries ?? 0) }}</p></div>
                <div class="rounded-2xl bg-white p-5 shadow-sm"><p class="text-sm font-semibold text-slate-500">Satuan pendidikan</p><p class="mt-2 text-3xl font-extrabold text-[#071E49]">{{ number_format($mbgProvinceRecap->education_units ?? 0) }}</p></div>
                <div class="rounded-2xl bg-white p-5 shadow-sm"><p class="text-sm font-semibold text-slate-500">Satpen negeri</p><p class="mt-2 text-3xl font-extrabold text-[#071E49]">{{ number_format($mbgProvinceRecap->public_units ?? 0) }}</p></div>
                <div class="rounded-2xl bg-white p-5 shadow-sm"><p class="text-sm font-semibold text-slate-500">Satpen swasta</p><p class="mt-2 text-3xl font-extrabold text-[#071E49]">{{ number_format($mbgProvinceRecap->private_units ?? 0) }}</p></div>
            </div>
        @endif

        <div class="mt-6 overflow-hidden rounded-2xl bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[620px] text-left text-sm">
                    <thead class="bg-[#071E49] text-white">
                        <tr>
                            <th class="px-5 py-3">{{ $mbgProvinceCode ? 'Kabupaten/Kota' : 'Provinsi' }}</th>
                            <th class="px-5 py-3 text-right">Satpen</th>
                            <th class="px-5 py-3 text-right">Penerima manfaat</th>
                            <th class="px-5 py-3 text-right">Kondisi khusus</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($mbgRegionRecaps as $recap)
                            <tr>
                                <td class="px-5 py-3 font-semibold text-[#071E49]">
                                    @if ($mbgProvinceCode)
                                        {{ $recap->regency_name ?: $recap->regency_code }}
                                    @else
                                        <a href="{{ route('data-mbg', ['mbg_province' => $recap->province_code, 'mbg_level' => $mbgLevel]) }}" class="hover:underline">
                                            {{ $recap->province_name }} <span aria-hidden="true">&rarr;</span>
                                            <span class="sr-only">Lihat kabupaten/kota</span>
                                        </a>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-right">{{ number_format($recap->education_units) }}</td>
                                <td class="px-5 py-3 text-right">{{ number_format($recap->beneficiaries) }}</td>
                                <td class="px-5 py-3 text-right">{{ number_format($recap->special_conditions) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-5 py-8 text-center text-slate-500">Data {{ $mbgProvinceCode ? 'kabupaten/kota' : 'provinsi' }} belum diimpor{{ $mbgLevel ? ' untuk jenjang ini' : '' }}{{ $mbgYear ? ' pada tahun '.$mbgYear : '' }}.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.public>
