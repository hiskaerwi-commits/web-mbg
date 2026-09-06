<x-filament-panels::page>
    <form wire:submit="save" class="w-full space-y-6">
        <section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200/70 sm:p-6">
            <div class="mb-6">
                <h2 class="text-base font-bold text-[#071E49]">Identitas Website</h2>
                <p class="mt-1 text-sm text-slate-500">Data ini dipakai pada header, footer, dan identitas browser.</p>
            </div>

            <div class="grid gap-5 md:grid-cols-2">
                <label class="block text-sm font-medium text-slate-800">
                    Nama website
                    <input wire:model="siteName" type="text" class="mt-1.5 block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-[#071E49] shadow-sm outline-none transition focus:border-[#071E49] focus:ring-2 focus:ring-[#B5E0EA]" />
                    @error('siteName') <span class="mt-1 block text-xs text-red-600">{{ $message }}</span> @enderror
                </label>

                <label class="block text-sm font-medium text-slate-800">
                    Tagline
                    <input wire:model="siteTagline" type="text" class="mt-1.5 block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-[#071E49] shadow-sm outline-none transition focus:border-[#071E49] focus:ring-2 focus:ring-[#B5E0EA]" />
                </label>

                <label class="block text-sm font-medium text-slate-800">
                    Logo
                    <input wire:model="logo" type="file" accept="image/*" class="mt-1.5 block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-600 file:mr-3 file:rounded-md file:border-0 file:bg-[#071E49] file:px-3 file:py-1.5 file:text-sm file:font-semibold file:text-white" />
                    @if ($logoPath)
                        <img src="{{ Storage::disk('public')->url($logoPath) }}" alt="Logo saat ini" class="mt-3 h-12 w-auto rounded-md border border-slate-200 bg-slate-50 p-1" />
                    @endif
                    @error('logo') <span class="mt-1 block text-xs text-red-600">{{ $message }}</span> @enderror
                </label>

                <label class="block text-sm font-medium text-slate-800">
                    Favicon
                    <input wire:model="favicon" type="file" accept=".png,.ico,.svg,image/png,image/x-icon,image/svg+xml" class="mt-1.5 block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-600 file:mr-3 file:rounded-md file:border-0 file:bg-[#071E49] file:px-3 file:py-1.5 file:text-sm file:font-semibold file:text-white" />
                    @if ($faviconPath)
                        <img src="{{ Storage::disk('public')->url($faviconPath) }}" alt="Favicon saat ini" class="mt-3 size-10 rounded-md border border-slate-200 bg-slate-50 p-1" />
                    @endif
                    @error('favicon') <span class="mt-1 block text-xs text-red-600">{{ $message }}</span> @enderror
                </label>
            </div>

            <label class="mt-5 block text-sm font-medium text-slate-800">
                Teks footer
                <textarea wire:model="footerText" rows="3" class="mt-1.5 block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-[#071E49] shadow-sm outline-none transition focus:border-[#071E49] focus:ring-2 focus:ring-[#B5E0EA]"></textarea>
            </label>
        </section>

        <section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200/70 sm:p-6">
            <div class="mb-6">
                <h2 class="text-base font-bold text-[#071E49]">Kontak & Media Sosial Footer</h2>
                <p class="mt-1 text-sm text-slate-500">Ubah kontak sesuai wilayah website. Kosongkan isian untuk menyembunyikannya dari footer.</p>
            </div>
            <label class="mb-5 flex items-start gap-3 text-sm text-slate-800">
                <input wire:model="footer.show_contact" type="checkbox" class="mt-1 rounded border-slate-300 text-[#071E49]" />
                <span><span class="block font-semibold">Tampilkan kontak wilayah</span><span class="mt-1 block text-slate-500">Aktifkan setelah data kontak wilayah dipastikan benar. Saat nonaktif, kontak dan tombol WhatsApp disembunyikan; menu Bantuan dan Alamat mengarah ke beranda. Isian tetap tersimpan.</span></span>
            </label>
            @error('footer.show_contact') <span class="mb-4 block text-xs text-red-600">{{ $message }}</span> @enderror
            <label class="block text-sm font-medium text-slate-800">
                Alamat
                <textarea wire:model="footer.address" rows="3" class="mt-1.5 block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-[#071E49]" placeholder="Alamat kantor lengkap"></textarea>
                @error('footer.address') <span class="mt-1 block text-xs text-red-600">{{ $message }}</span> @enderror
            </label>
            <div class="mt-5 grid gap-5 md:grid-cols-2">
                @foreach (['email' => ['Email', 'email', 'nama@institusi.go.id'], 'phone' => ['Nomor telepon', 'tel', '021-1234567'], 'whatsapp' => ['Nomor WhatsApp', 'tel', '0812-3456-7890'], 'facebook' => ['Facebook', 'url', 'https://www.facebook.com/akun'], 'instagram' => ['Instagram', 'url', 'https://www.instagram.com/akun'], 'x' => ['X (Twitter)', 'url', 'https://x.com/akun'], 'tiktok' => ['TikTok', 'url', 'https://www.tiktok.com/@akun']] as $key => [$label, $type, $placeholder])
                    <label wire:key="footer-{{ $key }}" class="block text-sm font-medium text-slate-800">
                        {{ $label }}
                        <input wire:model="footer.{{ $key }}" type="{{ $type }}" placeholder="{{ $placeholder }}" class="mt-1.5 block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-[#071E49]" />
                        @error('footer.'.$key) <span class="mt-1 block text-xs text-red-600">{{ $message }}</span> @enderror
                    </label>
                @endforeach
            </div>
            <p class="mt-4 text-xs text-slate-500">Media sosial memakai tautan profil lengkap. WhatsApp menerima format 08 atau kode negara, misalnya +62.</p>
        </section>

        <section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200/70 sm:p-6">
            <div class="mb-6">
                <h2 class="text-base font-bold text-[#071E49]">Data MBG</h2>
                <p class="mt-1 text-sm text-slate-500">Pilih Pusat / Nasional untuk seluruh wilayah, atau pilih satu provinsi. Pengaturan berlaku di halaman Data MBG publik dan admin.</p>
            </div>

            <label class="block max-w-md text-sm font-medium text-slate-800">
                Cakupan Data MBG
                <select wire:model="mbgProvinceCode" class="mt-1.5 block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-[#071E49] shadow-sm outline-none transition focus:border-[#071E49] focus:ring-2 focus:ring-[#B5E0EA]">
                    <option value="">Pusat / Nasional — Semua Wilayah</option>
                    @foreach ($this->provinceOptions() as $code => $name)
                        <option value="{{ $code }}">{{ $name }} ({{ $code }})</option>
                    @endforeach
                </select>
                @error('mbgProvinceCode') <span class="mt-1 block text-xs text-red-600">{{ $message }}</span> @enderror
            </label>
        </section>

        <section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200/70 sm:p-6">
            <div class="mb-6">
                <h2 class="text-base font-bold text-[#071E49]">SEO Global</h2>
                <p class="mt-1 text-sm text-slate-500">Nilai default untuk halaman public yang belum memiliki metadata sendiri.</p>
            </div>

            <div class="grid gap-5 md:grid-cols-2">
                <label class="block text-sm font-medium text-slate-800">
                    Meta title default
                    <input wire:model="defaultMetaTitle" type="text" class="mt-1.5 block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-[#071E49] shadow-sm outline-none transition focus:border-[#071E49] focus:ring-2 focus:ring-[#B5E0EA]" />
                </label>

                <label class="block text-sm font-medium text-slate-800">
                    Google site verification
                    <input wire:model="googleSiteVerification" type="text" class="mt-1.5 block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-[#071E49] shadow-sm outline-none transition focus:border-[#071E49] focus:ring-2 focus:ring-[#B5E0EA]" />
                </label>
            </div>

            <label class="mt-5 block text-sm font-medium text-slate-800">
                Meta description default
                <textarea wire:model="defaultMetaDescription" rows="3" class="mt-1.5 block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-[#071E49] shadow-sm outline-none transition focus:border-[#071E49] focus:ring-2 focus:ring-[#B5E0EA]"></textarea>
            </label>

            <label class="mt-5 block text-sm font-medium text-slate-800">
                Gambar Open Graph default
                <input wire:model="defaultOgImage" type="file" accept="image/*" class="mt-1.5 block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-600 file:mr-3 file:rounded-md file:border-0 file:bg-[#071E49] file:px-3 file:py-1.5 file:text-sm file:font-semibold file:text-white" />
                @if ($defaultOgImagePath)
                    <img src="{{ Storage::disk('public')->url($defaultOgImagePath) }}" alt="Open Graph image saat ini" class="mt-3 h-24 w-auto rounded-md border border-slate-200 bg-slate-50 p-1" />
                @endif
                @error('defaultOgImage') <span class="mt-1 block text-xs text-red-600">{{ $message }}</span> @enderror
            </label>

        </section>

        <div class="flex justify-end">
            <button type="submit" class="inline-flex items-center rounded-xl bg-[#06183A] px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-[#04112A] focus:outline-none focus:ring-2 focus:ring-[#071E49] focus:ring-offset-2">
                Simpan Website Settings
            </button>
        </div>
    </form>
</x-filament-panels::page>
