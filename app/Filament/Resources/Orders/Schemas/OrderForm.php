<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\Order;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class OrderForm
{
    public const STATUS_COLORS = [
        'new' => 'warning',
        'confirmed' => 'info',
        'in_progress' => 'primary',
        'shipped' => 'info',
        'completed' => 'success',
        'cancelled' => 'danger',
    ];

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Grid::make(1)->columnSpan(2)->schema([
                    Section::make('Status')
                        ->schema([
                            ToggleButtons::make('status')
                                ->hiddenLabel()
                                ->options(config('shop.order_statuses'))
                                ->colors(self::STATUS_COLORS)
                                ->inline()
                                ->required(),
                        ]),

                    Section::make('Slike u narudžbi')
                        ->description('Detalji: ram, da li je sa stanja, fotografija kupca.')
                        ->schema([
                            TextEntry::make('items_table')
                                ->hiddenLabel()
                                ->state(fn (?Order $record) => $record ? self::itemsHtml($record) : null)
                                ->html(),
                        ]),

                    Section::make('Cijena')
                        ->description('Dogovorili ste drugu cijenu ili popust s kupcem? Promijenite ovdje — ukupno se računa samo.')
                        ->schema([
                            Repeater::make('items')
                                ->hiddenLabel()
                                ->relationship()
                                ->table([
                                    TableColumn::make('Stavka'),
                                    TableColumn::make('Širina'),
                                    TableColumn::make('Visina'),
                                    TableColumn::make('Kom.'),
                                    TableColumn::make('Cijena / kom (KM)'),
                                ])
                                ->schema([
                                    TextInput::make('title')->required()->maxLength(150),
                                    TextInput::make('width')->numeric()->integer()->minValue(1)->required(),
                                    TextInput::make('height')->numeric()->integer()->minValue(1)->required(),
                                    TextInput::make('qty')->numeric()->integer()->minValue(1)->maxValue(99)->default(1)->required()->live(onBlur: true),
                                    TextInput::make('unit_price')->numeric()->integer()->minValue(0)->required()->live(onBlur: true),
                                ])
                                ->mutateRelationshipDataBeforeCreateUsing(fn (array $data) => $data + ['kind' => 'extra'])
                                ->addActionLabel('Dodaj stavku (npr. ram, dostava po dogovoru)')
                                ->reorderable(false)
                                ->defaultItems(0),
                            Grid::make(3)->schema([
                                TextInput::make('discount')
                                    ->label('Popust')
                                    ->numeric()->integer()->minValue(0)->default(0)
                                    ->suffix('KM')
                                    ->live(onBlur: true),
                                TextInput::make('shipping')
                                    ->label('Dostava')
                                    ->numeric()->integer()->minValue(0)->default(0)
                                    ->suffix('KM')
                                    ->live(onBlur: true),
                                TextEntry::make('total_preview')
                                    ->label('Ukupno za naplatu')
                                    ->state(fn (Get $get) => number_format(self::previewTotal($get), 0, ',', '.').' KM')
                                    ->weight('bold')
                                    ->size('lg')
                                    ->helperText(fn (?Order $record) => $record?->original_total !== null
                                        ? 'Kupac je naručio za '.number_format($record->original_total, 0, ',', '.').' KM'
                                        : null),
                            ]),
                        ]),

                    Section::make('Interna bilješka')
                        ->description('Vidite je samo vi — npr. dogovoreni rok ili šta je kupac rekao na telefonu.')
                        ->schema([
                            Textarea::make('admin_note')->hiddenLabel()->rows(3),
                        ]),
                ]),

                Grid::make(1)->columnSpan(1)->schema([
                    Section::make('Kupac')
                        ->schema([
                            TextInput::make('full_name')->label('Ime i prezime')->required(),
                            TextInput::make('phone')->label('Telefon')->tel()->required()
                                ->suffixAction(
                                    \Filament\Actions\Action::make('call')
                                        ->icon('heroicon-o-phone')
                                        ->url(fn (?string $state) => $state ? 'tel:'.preg_replace('/[^0-9+]/', '', $state) : null)
                                ),
                            TextInput::make('email')->label('Email')->email(),
                            TextInput::make('address')->label('Adresa')->required(),
                            Grid::make(2)->schema([
                                TextInput::make('zip')->label('Poštanski broj'),
                                TextInput::make('city')->label('Grad')->required(),
                            ]),
                            TextInput::make('country')->label('Država')->required(),
                            Textarea::make('note')->label('Napomena kupca')->rows(3)->disabled()->dehydrated(false),
                        ]),
                ]),
            ]);
    }

    private static function previewTotal(Get $get): int
    {
        $subtotal = collect($get('items') ?? [])->sum(fn ($i) => (int) ($i['unit_price'] ?? 0) * (int) ($i['qty'] ?? 0));

        return max(0, $subtotal - (int) $get('discount') + (int) $get('shipping'));
    }

    private static function itemsHtml(Order $order): HtmlString
    {
        $badge = fn (string $text, string $bg, string $fg = 'inherit') => '<span style="font-size:11px;padding:2px 6px;border-radius:4px;background:'.$bg.';color:'.$fg.'">'.$text.'</span> ';

        $rows = $order->items->map(function ($item) use ($badge) {
            $kind = match ($item->kind) {
                'commission' => $badge('PO MJERI', 'rgba(201,161,94,.15)', '#b98a3e'),
                'gift' => $badge('POKLON'.($item->promotion_title ? ' · '.e($item->promotion_title) : ''), 'rgba(201,161,94,.25)', '#8d6828'),
                'extra' => $badge('DODATNO', 'rgba(127,127,127,.15)'),
                default => '',
            };
            $style = $item->style_name ? e($item->style_name).' · ' : '';
            if ($item->customer_photo) {
                $url = route('admin.photo', $item);
                $style = '<a href="'.$url.'" target="_blank" style="display:inline-block;margin:6px 0"><img src="'.$url.'" alt="" style="height:72px;border-radius:4px;vertical-align:middle"> <span style="color:#b98a3e;text-decoration:underline">Fotografija kupca — otvori</span></a><br>'.$style;
            }
            if (in_array($item->kind, ['painting', 'gift'], true)) {
                $kind .= $item->from_stock >= $item->qty
                    ? '<span style="font-size:11px;padding:2px 6px;border-radius:4px;background:rgba(16,185,129,.15);color:#059669">SA STANJA</span> '
                    : '<span style="font-size:11px;padding:2px 6px;border-radius:4px;background:rgba(127,127,127,.15)">NASLIKATI'.($item->from_stock ? ' ('.($item->qty - $item->from_stock).' kom)' : '').'</span> ';
            }
            if ($item->original_unit_price) {
                $style .= 'akcija, bilo '.number_format($item->original_unit_price, 0, ',', '.').' KM · ';
            }

            return '<tr style="border-top:1px solid rgba(127,127,127,.2)">'
                .'<td style="padding:10px 8px">'.$kind.'<strong>'.e($item->title).'</strong><br><span style="opacity:.7;font-size:13px">'.$style.$item->width.' × '.$item->height.' cm · Ram: '.e($item->frameLabel() ?? 'bez rama').'</span></td>'
                .'<td style="padding:10px 8px;text-align:right;white-space:nowrap">'.$item->qty.' × '.number_format($item->unit_price, 0, ',', '.').' KM</td>'
                .'<td style="padding:10px 8px;text-align:right;white-space:nowrap"><strong>'.number_format($item->lineTotal(), 0, ',', '.').' KM</strong></td>'
                .'</tr>';
        })->implode('');

        return new HtmlString(
            '<table style="width:100%;border-collapse:collapse">'.$rows
            .($order->discount ? '<tr><td style="padding:6px 8px" colspan="2">Popust</td><td style="padding:6px 8px;text-align:right">−'.number_format($order->discount, 0, ',', '.').' KM</td></tr>' : '')
            .($order->shipping ? '<tr><td style="padding:6px 8px" colspan="2">Dostava</td><td style="padding:6px 8px;text-align:right">'.number_format($order->shipping, 0, ',', '.').' KM</td></tr>' : '')
            .'<tr style="border-top:2px solid rgba(127,127,127,.35)"><td style="padding:12px 8px" colspan="2">Ukupno · <span style="opacity:.7">plaćanje pouzećem</span></td>'
            .'<td style="padding:12px 8px;text-align:right;font-size:18px"><strong>'.number_format($order->total, 0, ',', '.').' KM</strong></td></tr></table>'
        );
    }
}
