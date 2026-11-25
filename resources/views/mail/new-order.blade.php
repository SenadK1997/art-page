<x-mail::message>
# Nova narudžba {{ $order->number }}

**{{ $order->full_name }}** · {{ $order->phone }}@if($order->email) · {{ $order->email }}@endif

{{ $order->address }}, {{ $order->zip }} {{ $order->city }}, {{ $order->country }}

<x-mail::table>
| Slika | Veličina | Ram | Kom. | Cijena |
|:--|:--|:--|--:|--:|
@foreach ($order->items as $item)
| {{ $item->title }}@if($item->style_name) ({{ $item->style_name }})@endif | {{ $item->width }} × {{ $item->height }} cm | {{ $item->frameLabel() ?? '—' }} | {{ $item->qty }} | {{ $item->lineTotal() }} KM |
@endforeach
</x-mail::table>

**Ukupno: {{ $order->total }} KM** — plaćanje pouzećem.

@if ($order->note)
Napomena kupca: {{ $order->note }}
@endif

<x-mail::button :url="url('/admin/orders')">
Otvori narudžbe
</x-mail::button>
</x-mail::message>
