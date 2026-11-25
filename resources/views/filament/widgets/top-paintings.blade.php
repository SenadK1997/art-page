<x-filament-widgets::widget>
    <x-filament::section heading="Najprodavanije">
        @if ($rows->isEmpty())
            <p style="opacity:.6">Nema prodaje u ovom periodu.</p>
        @else
            <table style="width:100%;border-collapse:collapse;font-size:14px">
                <thead>
                    <tr style="text-align:left;opacity:.6;font-size:12px">
                        <th style="padding:6px 4px">Slika</th>
                        <th style="padding:6px 4px;text-align:right">Kom.</th>
                        <th style="padding:6px 4px;text-align:right">Prihod</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rows as $row)
                        <tr style="border-top:1px solid rgba(127,127,127,.2)">
                            <td style="padding:8px 4px">{{ $row['name'] }}</td>
                            <td style="padding:8px 4px;text-align:right">{{ $row['qty'] }}</td>
                            <td style="padding:8px 4px;text-align:right;font-weight:600">{{ number_format($row['revenue'], 0, ',', '.') }} KM</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
        <p style="margin-top:12px;font-size:12px;opacity:.6">Poklona dato: {{ $gifts }} · prodano sa stanja: {{ $fromStock }} kom.</p>
    </x-filament::section>
</x-filament-widgets::widget>
