@if ($paginator->hasPages())
    <nav class="flex items-center justify-center gap-2 text-[13px]" aria-label="Stranice">
        @if (! $paginator->onFirstPage())
            <button type="button" wire:click="previousPage" class="choice px-4 py-2">← Nazad</button>
        @endif
        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="px-2 text-ash">{{ $element }}</span>
            @endif
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    <button type="button" wire:click="gotoPage({{ $page }})" class="choice min-w-10 px-3 py-2 {{ $page == $paginator->currentPage() ? 'choice-on' : '' }}" @if($page == $paginator->currentPage()) aria-current="page" @endif>{{ $page }}</button>
                @endforeach
            @endif
        @endforeach
        @if ($paginator->hasMorePages())
            <button type="button" wire:click="nextPage" class="choice px-4 py-2">Dalje →</button>
        @endif
    </nav>
@endif
