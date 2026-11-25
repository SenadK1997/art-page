// Fade sections up as they scroll into view ([data-reveal]). Content stays
// visible if this script never runs: hiding only starts with .reveal-ready.
let observer;

function revealAll() {
    observer?.disconnect();
    if (!('IntersectionObserver' in window)) return;

    document.documentElement.classList.add('reveal-ready');
    observer = new IntersectionObserver(
        (entries) => {
            for (const entry of entries) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-revealed');
                    observer.unobserve(entry.target);
                }
            }
        },
        { rootMargin: '0px 0px -8% 0px', threshold: 0.08 },
    );
    document.querySelectorAll('[data-reveal]:not(.is-revealed)').forEach((el) => observer.observe(el));
}

document.addEventListener('DOMContentLoaded', revealAll);
// Pages opened with wire:navigate don't fire DOMContentLoaded again.
document.addEventListener('livewire:navigated', revealAll);
