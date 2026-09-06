<x-filament-panels::page>
    <section class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200/70">
        <div class="border-b border-slate-100 px-6 py-4">
            <h2 class="font-bold text-[#071E49]">Rekap Data MBG</h2>
            <p class="mt-1 text-sm text-slate-500">
                @if ($this->provinceName)
                    Menampilkan data untuk <span class="font-semibold text-[#071E49]">{{ $this->provinceName }}</span>. Ubah cakupan di menu <span class="font-semibold">Website Settings</span>.
                @else
                    Pusat / Nasional — menampilkan seluruh data yang tersedia. Ubah cakupan di Website Settings.
                @endif
            </p>
        </div>
        <div class="overflow-x-auto"><table class="w-full min-w-[900px] text-sm"><thead class="bg-slate-50 text-left text-xs font-bold uppercase text-slate-500"><tr><th class="px-6 py-3">Provinsi</th><th class="px-4 py-3">Kabupaten/Kota</th><th class="px-4 py-3">Jenjang</th><th class="px-4 py-3 text-right">Satpen</th><th class="px-4 py-3 text-right">Penerima</th><th class="px-6 py-3">Update</th></tr></thead><tbody class="divide-y divide-slate-100">@foreach ($this->recaps as $recap)<tr><td class="px-6 py-3 font-semibold text-[#071E49]">{{ $recap->province_name ?: 'Nasional' }}</td><td class="px-4 py-3">{{ $recap->regency_name ?: ($recap->province_code ? 'Rekap provinsi' : 'Rekap nasional') }}</td><td class="px-4 py-3">{{ $recap->level }}</td><td class="px-4 py-3 text-right">{{ number_format($recap->education_units) }}</td><td class="px-4 py-3 text-right">{{ number_format($recap->beneficiaries) }}</td><td class="px-6 py-3 text-slate-500">{{ $recap->source_pulled_at?->format('d M Y H:i') }}</td></tr>@endforeach</tbody></table></div>
        <div class="border-t border-slate-100 px-6 py-4">{{ $this->recaps->links() }}</div>
    </section>
</x-filament-panels::page>
