<x-filament-panels::page>
    <div class="w-full space-y-6">
        <section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200/70 sm:p-6">
            <div class="mb-6">
                <h2 class="text-base font-bold text-[#071E49]">{{ $editingId ? 'Edit Jaringan' : 'Tambah Jaringan' }}</h2>
                <p class="mt-1 text-sm text-slate-500">Masukkan nama jaringan/wilayah dan alamat tujuan. Link aktif akan muncul di halaman depan.</p>
            </div>
            <form wire:submit="save" class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
                <label class="block text-sm font-medium text-slate-800">Nama jaringan<input wire:model="name" type="text" placeholder="Contoh: PGRI Provinsi Aceh" class="mt-1.5 block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-[#071E49] shadow-sm outline-none transition focus:border-[#071E49] focus:ring-2 focus:ring-[#B5E0EA]"></label>
                <label class="block text-sm font-medium text-slate-800">URL tujuan<input wire:model="url" type="url" placeholder="https://contoh.go.id" class="mt-1.5 block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-[#071E49] shadow-sm outline-none transition focus:border-[#071E49] focus:ring-2 focus:ring-[#B5E0EA]"></label>
                <label class="block text-sm font-medium text-slate-800">Urutan tampil<input wire:model="sortOrder" type="number" min="0" class="mt-1.5 block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-[#071E49] shadow-sm outline-none transition focus:border-[#071E49] focus:ring-2 focus:ring-[#B5E0EA]"></label>
                <div class="flex items-end gap-4">
                    <label class="mb-2 inline-flex cursor-pointer items-center gap-2 text-sm font-medium text-slate-700"><span class="relative inline-flex shrink-0"><input wire:model="isActive" type="checkbox" class="peer sr-only"><span class="block h-6 w-11 rounded-full bg-slate-200 transition-colors peer-checked:bg-[#071E49]"></span><span class="pointer-events-none absolute left-1 top-1 size-4 rounded-full bg-white shadow-sm transition-transform peer-checked:translate-x-5"></span></span> Tampilkan</label>
                    <button type="submit" class="mb-0 inline-flex flex-1 items-center justify-center rounded-xl bg-[#06183A] px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#04112A]">{{ $editingId ? 'Simpan Perubahan' : 'Tambah Link' }}</button>
                    @if ($editingId)
                        <button type="button" wire:click="cancelEdit" class="mb-0 inline-flex items-center justify-center rounded-xl border border-slate-300 px-5 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">Batal</button>
                    @endif
                </div>
            </form>
            @error('name') <p class="mt-3 text-xs text-red-600">{{ $message }}</p> @enderror
            @error('url') <p class="mt-3 text-xs text-red-600">{{ $message }}</p> @enderror
        </section>

        <section class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200/70">
            <div class="border-b border-slate-100 px-5 py-4 sm:px-6"><h2 class="text-base font-bold text-[#071E49]">Daftar Jaringan</h2></div>
            <div class="overflow-x-auto"><table class="w-full min-w-[720px] text-left text-sm"><thead class="bg-slate-50 text-xs font-bold uppercase tracking-wide text-slate-500"><tr><th class="px-5 py-3 sm:px-6">Nama</th><th class="px-5 py-3">URL</th><th class="px-5 py-3 text-center">Urutan</th><th class="px-5 py-3 text-center">Status</th><th class="px-5 py-3 text-right sm:px-6">Aksi</th></tr></thead><tbody class="divide-y divide-slate-100">@forelse ($this->links as $link)<tr><td class="px-5 py-4 font-semibold text-[#071E49] sm:px-6">{{ $link->name }}</td><td class="max-w-md truncate px-5 py-4 text-slate-500">@if ($link->url)<a href="{{ $link->url }}" target="_blank" rel="noopener noreferrer" class="hover:text-[#071E49] hover:underline">{{ $link->url }}</a>@else<span class="italic text-slate-400">Belum diisi</span>@endif</td><td class="px-5 py-4 text-center text-slate-600">{{ $link->sort_order }}</td><td class="px-5 py-4 text-center"><button wire:click="toggle({{ $link->id }})" type="button" class="rounded-full px-3 py-1 text-xs font-bold {{ $link->is_active ? 'bg-[#EAF5E2] text-[#356D17]' : 'bg-slate-100 text-slate-500' }}">{{ $link->is_active ? 'Tampil' : 'Nonaktif' }}</button></td><td class="px-5 py-4 text-right sm:px-6"><div class="flex items-center justify-end gap-4"><button wire:click="edit({{ $link->id }})" type="button" class="text-sm font-bold text-[#071E49] hover:underline">Edit</button><button wire:click="delete({{ $link->id }})" wire:confirm="Hapus link ini?" type="button" class="text-sm font-bold text-red-600 hover:underline">Hapus</button></div></td></tr>@empty<tr><td colspan="5" class="px-5 py-10 text-center text-slate-500">Belum ada jaringan yang ditambahkan.</td></tr>@endforelse</tbody></table></div>
            @if ($this->links->hasPages())
                <div class="border-t border-slate-100 px-5 py-4 sm:px-6">{{ $this->links->links() }}</div>
            @endif
        </section>
    </div>
</x-filament-panels::page>
