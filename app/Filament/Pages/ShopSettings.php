<?php

namespace App\Filament\Pages;

use App\Support\Settings;
use App\Support\Telegram;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\HtmlString;

class ShopSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?string $navigationLabel = 'Postavke';

    protected static ?string $title = 'Postavke';

    protected static ?string $slug = 'postavke';

    protected static ?int $navigationSort = 20;

    public ?array $data = [];

    /** Chats that wrote to the bot, found with "Pronađi moj chat". */
    public array $chats = [];

    public function mount(Settings $settings): void
    {
        $this->form->fill([
            'owner_email' => $settings->get('owner_email'),
            'phone' => $settings->get('phone'),
            'shipping_fee' => $settings->get('shipping_fee'),
            'free_shipping_over' => $settings->get('free_shipping_over'),
            'made_to_order_time' => $settings->get('made_to_order_time'),
            'telegram_enabled' => $settings->bool('telegram_enabled'),
            'telegram_bot_token' => $settings->get('telegram_bot_token'),
            'telegram_chat_id' => $settings->get('telegram_chat_id'),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->columns(2)
            ->components([
                Section::make('Kontakt')
                    ->schema([
                        TextInput::make('phone')
                            ->label('Telefon u podnožju stranice')
                            ->tel()
                            ->placeholder('061 234 567'),
                        TextInput::make('owner_email')
                            ->label('Email za nove narudžbe')
                            ->email()
                            ->helperText('Na ovaj email stiže svaka nova narudžba. Prazno = bez emaila.'),
                    ]),

                Section::make('Dostava i izrada')
                    ->description('Plaćanje je uvijek pouzećem: kupac plaća kuriru pri preuzimanju.')
                    ->schema([
                        TextInput::make('shipping_fee')
                            ->label('Cijena dostave')
                            ->numeric()->integer()->minValue(0)
                            ->suffix('KM')
                            ->helperText('Prazno = piše „prema cjenovniku kurira“ i ne dodaje se na račun.'),
                        TextInput::make('free_shipping_over')
                            ->label('Besplatna dostava iznad')
                            ->numeric()->integer()->minValue(0)
                            ->suffix('KM')
                            ->helperText('Prazno = dostava se uvijek naplaćuje.'),
                        TextInput::make('made_to_order_time')
                            ->label('Rok izrade (tekst za kupce)')
                            ->placeholder('izrada 2–3 sedmice')
                            ->maxLength(60),
                    ]),

                Section::make('Telegram obavještenja')
                    ->key('telegram')
                    ->description('Dobijte poruku na Telegram čim neko naruči.')
                    ->columnSpanFull()
                    ->schema([
                        Text::make(new HtmlString(
                            '<ol style="list-style:decimal;padding-left:1.25rem;line-height:1.9">'
                            .'<li>U Telegramu otvorite <b>@BotFather</b>, pošaljite <code>/newbot</code> i pratite upute.</li>'
                            .'<li>BotFather vam da <b>token</b> (npr. <code>123456:ABC-...</code>) — zalijepite ga ispod.</li>'
                            .'<li>Otvorite svog novog bota u Telegramu i pošaljite mu bilo koju poruku (npr. „zdravo“).</li>'
                            .'<li>Kliknite <b>Pronađi moj chat</b>, izaberite sebe, pa <b>Pošalji probnu poruku</b> i <b>Sačuvaj</b>.</li>'
                            .'</ol>'
                        )),
                        Toggle::make('telegram_enabled')->label('Šalji obavještenja na Telegram'),
                        TextInput::make('telegram_bot_token')
                            ->label('Bot token')
                            ->password()
                            ->revealable()
                            ->autocomplete('off')
                            ->regex('/^\d+:[A-Za-z0-9_-]{20,}$/')
                            ->validationMessages(['regex' => 'Token izgleda kao 123456789:ABC... — kopirajte ga cijelog iz BotFathera.']),
                        Select::make('telegram_chat_id')
                            ->label('Chat')
                            ->options(fn (Get $get) => $this->chats + (filled($get('telegram_chat_id')) ? [$get('telegram_chat_id') => 'Sačuvani chat ('.$get('telegram_chat_id').')'] : []))
                            ->helperText('Kliknite „Pronađi moj chat“ nakon što pošaljete poruku botu.'),
                        Actions::make([
                            Action::make('findChat')
                                ->label('Pronađi moj chat')
                                ->icon(Heroicon::OutlinedMagnifyingGlass)
                                ->color('gray')
                                ->action(fn (Get $get, Set $set) => $this->findChats($get, $set)),
                            Action::make('testTelegram')
                                ->label('Pošalji probnu poruku')
                                ->icon(Heroicon::OutlinedPaperAirplane)
                                ->color('gray')
                                ->action(fn (Get $get) => $this->sendTest($get)),
                        ]),
                    ]),
            ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Form::make([EmbeddedSchema::make('form')])
                ->id('form')
                ->livewireSubmitHandler('save')
                ->footer([
                    Actions::make([
                        Action::make('save')->label('Sačuvaj')->submit('save')->keyBindings(['mod+s']),
                    ])->key('form-actions'),
                ]),
        ]);
    }

    public function save(Settings $settings): void
    {
        $data = $this->form->getState();

        if ($data['telegram_enabled'] && (blank($data['telegram_bot_token']) || blank($data['telegram_chat_id']))) {
            Notification::make()->title('Za Telegram trebaju token i chat.')->warning()->send();

            return;
        }

        $settings->set($data);
        Notification::make()->title('Postavke su sačuvane.')->success()->send();
    }

    private function findChats(Get $get, Set $set): void
    {
        $token = trim((string) $get('telegram_bot_token'));
        if ($token === '') {
            Notification::make()->title('Prvo zalijepite bot token.')->warning()->send();

            return;
        }

        try {
            $this->chats = app(Telegram::class)->recentChats($token);
        } catch (\Throwable $e) {
            Notification::make()->title('Telegram ne prihvata token')->body($e->getMessage())->danger()->send();

            return;
        }

        if ($this->chats === []) {
            Notification::make()->title('Nema poruka')->body('Pošaljite botu bilo koju poruku u Telegramu, pa kliknite ponovo.')->warning()->send();

            return;
        }

        $set('telegram_chat_id', array_key_first($this->chats));
        Notification::make()->title('Chat pronađen: '.reset($this->chats))->success()->send();
    }

    private function sendTest(Get $get): void
    {
        $result = app(Telegram::class)->send(
            '✅ <b>Foco Art</b> — obavještenja rade. Ovdje će stizati nove narudžbe.',
            null,
            trim((string) $get('telegram_bot_token')),
            (string) $get('telegram_chat_id'),
        );

        $result['ok']
            ? Notification::make()->title('Poruka poslana — provjerite Telegram.')->success()->send()
            : Notification::make()->title('Poruka nije poslana')->body($result['error'])->danger()->send();
    }
}
