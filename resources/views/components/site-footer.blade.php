@props(['settings'])
@php
    $contact = $settings->footer_details;
    $showContact = (bool) $contact['show_contact'];
    $socials = array_filter(array_intersect_key($contact, array_flip(['facebook', 'instagram', 'x', 'tiktok'])));
    $whatsapp = preg_replace('/\D/', '', $contact['whatsapp']);
    if (str_starts_with($whatsapp, '0')) $whatsapp = '62'.substr($whatsapp, 1);
    $whatsappUrl = $showContact && $whatsapp ? 'https://wa.me/'.$whatsapp : null;
    $mapUrl = $showContact && $contact['address'] ? 'https://www.google.com/maps/search/?api=1&query='.rawurlencode($contact['address']) : null;
    $groups = [
        'Navigasi' => [
            'Beranda' => url('/'), 'Berita' => route('news.index'),
            'Juknis' => route('juknis.index'), 'Infografis' => url('/'),
            'SPPG Operasional' => url('/'),
        ],
        'Profil BGN' => [
            'Pejabat BGN' => url('/'), 'Visi Misi' => url('/'),
            'Arti Logo' => url('/'), 'Tugas & Fungsi' => url('/'),
        ],
        'Gabung' => ['Gabung Menjadi Mitra' => url('/')],
        'Hubungi Kami' => array_filter([
            'Bantuan' => $showContact && $contact['email'] ? 'mailto:'.$contact['email'] : ($whatsappUrl ?: url('/')),
            'Alamat' => $mapUrl ?: url('/'), 'FAQ' => route('faq.index'), 'Ajukan Pengaduan' => url('/'),
        ]),
    ];
@endphp
<footer id="kontak" class="site-footer">
    <div class="site-footer__container">
        <a href="{{ url('/') }}" class="site-footer__brand" aria-label="Beranda Badan Gizi Nasional">
            <img src="{{ asset('images/branding/bgn-footer.png') }}" alt="Badan Gizi Nasional" width="230" height="96" loading="lazy">
        </a>
        <div class="site-footer__grid">
            @foreach ($groups as $heading => $links)
                <nav aria-label="{{ $heading }} footer">
                    <h2>{{ $heading }}</h2>
                    <ul class="site-footer__links">
                        @foreach ($links as $label => $href)
                            <li><a href="{{ $href }}">{{ $label }}</a></li>
                        @endforeach
                    </ul>
                </nav>
            @endforeach
            <div class="site-footer__contact">
                @if ($socials)
                    <h2>Media Sosial</h2>
                    <div class="site-footer__socials">
                        @foreach ($socials as $platform => $href)
                            <a href="{{ $href }}" target="_blank" rel="noopener noreferrer" aria-label="{{ ['facebook' => 'Facebook', 'instagram' => 'Instagram', 'x' => 'X (Twitter)', 'tiktok' => 'TikTok'][$platform] }}">
                                <x-footer-icon :name="$platform" />
                            </a>
                        @endforeach
                    </div>
                @endif
                @if ($showContact && ($contact['address'] || $contact['email'] || $contact['phone'] || $whatsappUrl))
                    <h2>Kontak</h2>
                    <address class="site-footer__details">
                        @if ($mapUrl)
                            <a href="{{ $mapUrl }}" target="_blank" rel="noopener noreferrer"><x-footer-icon name="address" /><span class="whitespace-pre-line">{{ $contact['address'] }}</span></a>
                        @endif
                        @if ($contact['email'])
                            <a href="mailto:{{ $contact['email'] }}"><x-footer-icon name="email" /><span>{{ $contact['email'] }}</span></a>
                        @endif
                        @if ($contact['phone'])
                            <a href="tel:{{ preg_replace('/[^0-9+]/', '', $contact['phone']) }}"><x-footer-icon name="phone" /><span>{{ $contact['phone'] }}</span></a>
                        @endif
                        @if ($whatsappUrl)
                            <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer"><x-footer-icon name="whatsapp" /><span>{{ $contact['whatsapp'] }}</span></a>
                        @endif
                    </address>
                @endif
            </div>
        </div>
        <div class="site-footer__copyright">&copy; {{ now()->year }} {{ $settings->display_name }} | All Rights Reserved</div>
    </div>
</footer>
@if ($whatsappUrl)
    <div class="footer-help">
        <div class="footer-help__prompt" data-footer-help-prompt>
            <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer">Butuh Bantuan?</a>
            <button type="button" aria-label="Tutup pesan bantuan" data-footer-help-close>&times;</button>
        </div>
        <a class="footer-help__button" href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer" aria-label="Hubungi kami melalui WhatsApp"><x-footer-icon name="whatsapp" /></a>
    </div>
@endif
