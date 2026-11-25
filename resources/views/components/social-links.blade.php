{{-- Instagram, Facebook and TikTok icons (links from config/shop.php). --}}
@php
    $icons = [
        'instagram' => '<rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="0.6" fill="currentColor"/>',
        'facebook' => '<path d="M14 8h3V4h-3a4 4 0 0 0-4 4v3H7v4h3v6h4v-6h3l1-4h-4V8.5a.5.5 0 0 1 .5-.5z"/>',
        'tiktok' => '<path d="M15 3c.4 2.6 2 4.2 4.5 4.5v3.3c-1.7 0-3.2-.5-4.5-1.4V15a6 6 0 1 1-6-6v3.4a2.6 2.6 0 1 0 2.6 2.6V3z"/>',
    ];
@endphp
<div {{ $attributes->merge(['class' => 'flex items-center gap-2']) }}>
    @foreach (config('shop.socials') as $network => $url)
        <a href="{{ $url }}" target="_blank" rel="noopener me" aria-label="{{ ucfirst($network) }}"
           class="flex h-11 w-11 items-center justify-center rounded-full border border-line-strong text-stone transition hover:border-gold hover:text-gold">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round" aria-hidden="true">{!! $icons[$network] ?? '' !!}</svg>
        </a>
    @endforeach
</div>
