<a href="{{ route('cart') }}" wire:navigate class="relative inline-flex h-11 w-11 items-center justify-center text-stone transition hover:text-bone md:h-auto md:w-auto" aria-label="Korpa, {{ $count }} {{ $count === 1 ? 'stavka' : 'stavki' }}">
    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.2" aria-hidden="true"><path d="M5 8h14l-1.2 12H6.2L5 8z"/><path d="M9 8V6a3 3 0 0 1 6 0v2"/></svg>
    @if ($count > 0)
        <span class="absolute right-0.5 top-1 flex md:-right-2.5 md:-top-2 h-4 min-w-4 items-center justify-center rounded-full bg-gold px-1 text-[10px] font-medium text-ink">{{ $count }}</span>
    @endif
</a>
