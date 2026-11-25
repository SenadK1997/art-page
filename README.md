# Foco Art

Online gallery and shop for the painter Amer Fočo: original paintings in several sizes, made-to-measure paintings priced by a calculator, stock and sales, and cash-on-delivery ordering.

**Stack:** Laravel 13, Livewire 4, Tailwind 4, Filament 5 admin at `/admin`, MySQL.

## The price formula

Kept from the first version of the site (`app/Support/PriceCalculator.php`):

```
price (KM) = round(width_cm × height_cm / 100 × multiplier)
```

The multiplier is the price of 100 cm² for a style: Apstrakcija 5, Realizam 10, Panorama 15. The artist can change these in the admin under **Stilovi i cijene**. The calculator and the admin's size form both use this formula. The browser shows the price live, and the server always recomputes it.

## Owner's calculator app (`/kalkulator`)

A one-page price calculator for the artist. It has style buttons, width and height with +/− buttons and presets, a big price, an optional discount, the same size in all styles, and "Pošalji ponudu" (share to WhatsApp, Viber or SMS) or "Kopiraj".

It installs as its own phone app (PWA) called "Kalkulator" with the FA icon:
- **Android/Chrome:** tap "Instaliraj" in the page header, or use the browser menu → "Add to Home screen".
- **iPhone/Safari:** Share → "Dodaj na početni ekran" (the page explains this once).

The icon opens straight into the calculator, full screen, and keeps working offline. Prices come from Admin → Stilovi i cijene. The app updates whenever it's opened online. Installing needs **https** (localhost works for testing).

## Admin (`/admin`)

- **Narudžbe:** a badge for new orders, one-click "Potvrdi", and status buttons. Each item shows whether it ships from stock, is still to paint, or is a gift, plus the customer's own photo. **Cijena** lets the admin change item prices and quantities and add a discount and delivery cost after talking to the customer. The total recalculates, and the original stays visible.
- **Izvještaj prodaje:** revenue, amount collected, average order, discounts and gifts given, a revenue chart, revenue by style and the best sellers, for any period.
- **Slike:** a photo plus extra photos, the style, categories, and a sizes table (width, height, price, in stock). Typing a size fills the price from the formula, and "Izračunaj sve cijene" refills them all. There's also a sale (% off and an end date) and a one-off-original toggle.
- **Poklon akcije:** "buy these / N paintings / over X KM → pick one free painting from this list". Every condition is optional, and offers have dates and an on/off switch. Nothing shows on the site while no offer is active.
- **Stilovi i cijene:** the multiplier per style, min/max made-to-measure sizes, and whether customers may send their own photo.
- **Kategorije:** Priroda, Sarajevo… shown as gallery filters and on the homepage.
- **Postavke:** contact phone, the email for new orders, delivery price and the free-delivery threshold, the made-to-order lead time, and **Telegram** (paste the bot token, "Pronađi moj chat", "Pošalji probnu poruku"). The token is stored encrypted.

Customer photos are private (`storage/app/private`). Only a signed-in admin can open them. Photos from carts that never became orders are deleted after 3 days, which needs the scheduler: `* * * * * php artisan schedule:run`.

## Local setup

```bash
composer install
npm install && npm run build
cp .env.example .env && php artisan key:generate
php artisan migrate --seed      # styles; demo paintings are added in APP_ENV=local
php artisan storage:link
php artisan make:filament-user  # first admin login
php artisan serve
```

## Deploy

> **First deploy of this version: use a NEW, empty database.** The old site's tables (`products`, `orders`, `users`…) have a different structure, so migrations can't run on top of them. Point `DB_*` at the new database and `LEGACY_DB_*` at the old one, run the commands below, then `php artisan app:import-legacy --images=<old storage/app/public/images> --with-orders` (see "Importing the old site"). The old database is only read, never changed, so you can always go back.


```bash
git pull
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
php artisan db:seed --class=PaintingStyleSeeder --force   # first deploy only
php artisan storage:link                                    # first deploy only
php artisan make:filament-user                              # first deploy only: admin login
php artisan optimize && php artisan filament:optimize
```

Env keys to set in production: `APP_NAME`, `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL` (https, so Telegram buttons link to the order), `APP_LOCALE=bs`, `DB_*`, `MAIL_*`. Phone, order email, delivery and Telegram are set in Admin → Postavke. Add the cron line above for the scheduler.

## Importing the old site

The command reads the old database through the `legacy` connection and never writes to it. It's safe to run more than once.

```bash
# .env: LEGACY_DB_DATABASE=old_db_name (+ LEGACY_DB_HOST/USERNAME/PASSWORD if different)
php artisan app:import-legacy --dry-run
php artisan app:import-legacy --images=/path/to/old/storage/app/public/images --with-orders
```

Old paintings come in without a style, so set one in the admin afterwards.

## SEO

- `/sitemap.xml` lists every page, painting (with images) and category. It updates by itself, so submit it once in Google Search Console.
- `/robots.txt` points to the sitemap and blocks admin, cart and checkout. It allows indexing only when `APP_ENV=production`, so staging never ends up in Google.
- Each page has a canonical URL, a description and a share image (the painting itself on product pages). Product pages carry schema.org price and availability data, and every page carries the organisation with its Instagram, Facebook and TikTok profiles.

## Tests

```bash
php artisan test
```
