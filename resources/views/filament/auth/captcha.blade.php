<div class="captcha-row">
    <div class="captcha-image-wrap">
        {!! captcha_img('flat') !!}
    </div>

    <button
        type="button"
        wire:click="$refresh"
        class="captcha-refresh-btn"
    >
        <svg class="captcha-refresh-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M23 4v6h-6" />
            <path d="M1 20v-6h6" />
            <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15" />
        </svg>
        Ganti Kode
    </button>
</div>
