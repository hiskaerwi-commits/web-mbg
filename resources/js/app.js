import './bootstrap';

document.querySelector('[data-footer-help-close]')?.addEventListener('click', () => {
    document.querySelector('[data-footer-help-prompt]').hidden = true;
});

document.querySelectorAll('[data-berita-carousel]').forEach((carousel) => {
    const slides = carousel.querySelectorAll('[data-berita-slide]');
    const dots = carousel.querySelectorAll('[data-berita-dot]');
    const previous = carousel.querySelector('[data-berita-prev]');
    const next = carousel.querySelector('[data-berita-next]');
    if (slides.length < 2) return;

    let active = 0;

    const goTo = (index) => {
        slides[active].classList.add('pointer-events-none', 'opacity-0');
        slides[active].classList.remove('opacity-100');
        dots[active].classList.remove('bg-[#C9A45C]');
        dots[active].classList.add('bg-white/40');

        active = index;

        slides[active].classList.remove('pointer-events-none', 'opacity-0');
        slides[active].classList.add('opacity-100');
        dots[active].classList.add('bg-[#C9A45C]');
        dots[active].classList.remove('bg-white/40');
    };

    dots.forEach((dot, index) => {
        dot.addEventListener('click', () => goTo(index));
    });

    previous?.addEventListener('click', () => goTo((active - 1 + slides.length) % slides.length));
    next?.addEventListener('click', () => goTo((active + 1) % slides.length));

    setInterval(() => goTo((active + 1) % slides.length), 6000);
});
