@props(['summary'])
<dl class="space-y-3 text-[14px]">
    <div class="flex justify-between"><dt class="text-stone">Slike</dt><dd>{{ number_format($summary['subtotal'], 0, ',', '.') }} KM</dd></div>
    <div class="flex justify-between">
        <dt class="text-stone">Dostava</dt>
        <dd class="{{ $summary['shipping'] === 0 ? 'text-ok' : '' }}">
            @if ($summary['shipping'] === null)
                <span class="text-stone">Prema cjenovniku kurira</span>
            @elseif ($summary['shipping'] === 0)
                Besplatna
            @else
                {{ number_format($summary['shipping'], 0, ',', '.') }} KM
            @endif
        </dd>
    </div>
    @if ($summary['savings'] > 0)
        <div class="flex justify-between text-gold-light"><dt>Ušteda</dt><dd>−{{ number_format($summary['savings'], 0, ',', '.') }} KM</dd></div>
    @endif
</dl>
@php($freeOver = $settings->int('free_shipping_over'))
@if ($summary['shipping'] && $freeOver && $summary['subtotal'] < $freeOver)
    <p class="mt-3 text-[12px] text-stone">Još {{ number_format($freeOver - $summary['subtotal'], 0, ',', '.') }} KM do besplatne dostave.</p>
@endif
<div class="mt-6 flex items-baseline justify-between border-t border-line pt-6">
    <span class="text-[13px] uppercase tracking-[0.14em] text-stone">Ukupno</span>
    <span class="font-display text-[40px] text-gold-light">{{ number_format($summary['total'], 0, ',', '.') }} KM</span>
</div>
