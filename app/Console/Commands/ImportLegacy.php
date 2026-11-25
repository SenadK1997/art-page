<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\Product;
use App\Models\Tag;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Copies paintings, sizes, tags (and optionally orders) from the first
 * version of the site. Reads the `legacy` connection (LEGACY_DB_* in .env)
 * and never writes to it. Safe to re-run: paintings are matched by slug.
 */
#[Signature('app:import-legacy
    {--images= : Folder holding the old storage/app/public/images files}
    {--with-orders : Also import old orders}
    {--dry-run : Only show what would be imported}')]
#[Description('Import paintings, sizes and tags from the old Foco-art database')]
class ImportLegacy extends Command
{
    public function handle(): int
    {
        $legacy = DB::connection('legacy');

        if (! config('database.connections.legacy.database')) {
            $this->error('Set LEGACY_DB_DATABASE (and LEGACY_DB_* if needed) in .env first.');

            return self::FAILURE;
        }

        $products = $legacy->table('products')->get();
        $sizes = $legacy->table('product_images')
            ->join('images', 'images.id', '=', 'product_images.images_id')
            ->select('product_images.product_id', 'images.width', 'images.height', 'images.price')
            ->get()->groupBy('product_id');
        $tags = $legacy->table('tags')->pluck('name', 'id');
        $productTags = $legacy->table('product_tags')->get()->groupBy('product_id');

        $this->info("Found {$products->count()} paintings, {$tags->count()} tags.");

        if ($this->option('dry-run')) {
            $this->table(['Old id', 'Title', 'Sizes'], $products->map(fn ($p) => [
                $p->id, $p->title,
                ($sizes[$p->id] ?? collect())->map(fn ($s) => "{$s->width}×{$s->height}={$s->price}")->implode(', '),
            ]));

            return self::SUCCESS;
        }

        $imagesDir = $this->option('images');
        $tagMap = $tags->mapWithKeys(fn ($name, $id) => [
            $id => Tag::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name])->id,
        ]);

        $productMap = [];
        $copied = 0;
        foreach ($products as $i => $old) {
            $image = null;
            if ($old->url) {
                $image = 'paintings/'.$old->url;
                $source = $imagesDir ? rtrim($imagesDir, '/').'/'.$old->url : null;
                if ($source && is_file($source) && ! Storage::disk('public')->exists($image)) {
                    Storage::disk('public')->put($image, file_get_contents($source));
                    $copied++;
                } elseif ($source && ! is_file($source)) {
                    $this->warn("Image not found for \"{$old->title}\": {$source}");
                }
            }

            $product = Product::updateOrCreate(
                ['slug' => Str::slug($old->title) ?: 'slika-'.$old->id],
                [
                    'title' => $old->title,
                    'description' => $old->description,
                    'image' => $image,
                    'is_available' => true,
                    'sort' => $i,
                ],
            );
            $productMap[$old->id] = $product->id;

            $product->sizes()->delete();
            foreach (($sizes[$old->id] ?? collect())->sortBy('price')->values() as $j => $s) {
                $product->sizes()->create([
                    'width' => (int) $s->width,
                    'height' => (int) $s->height,
                    'price' => (int) round($s->price),
                    'sort' => $j,
                ]);
            }

            $product->tags()->sync(
                ($productTags[$old->id] ?? collect())->pluck('tags_id')->map(fn ($id) => $tagMap[$id] ?? null)->filter()->all()
            );
        }

        $this->info(count($productMap).' paintings imported, '.$copied.' images copied.');

        if ($this->option('with-orders')) {
            $this->importOrders($legacy, $productMap);
        }

        $this->line('Check prices and styles in the admin: old paintings have no style set.');

        return self::SUCCESS;
    }

    private function importOrders($legacy, array $productMap): void
    {
        $items = $legacy->table('order_product')->get()->groupBy('order_id');
        $count = 0;

        foreach ($legacy->table('orders')->orderBy('id')->get() as $old) {
            $number = 'FA-OLD-'.$old->id;
            if (Order::where('number', $number)->exists()) {
                continue;
            }

            $order = Order::create([
                'number' => $number,
                'status' => $old->status ? 'completed' : 'new',
                'full_name' => $old->fullname,
                'phone' => $old->phone ?? '—',
                'email' => $old->email,
                'address' => $old->address,
                'city' => '—',
                'zip' => $old->zipcode,
                'country' => $old->country,
                'note' => $old->request ?? null,
                'total' => (int) $old->totalPrice,
                'admin_note' => 'Uvezeno sa stare stranice.',
            ]);
            $order->forceFill(['created_at' => $old->created_at, 'updated_at' => $old->updated_at])->save();

            foreach ($items[$old->id] ?? [] as $item) {
                $order->items()->create([
                    'kind' => 'painting',
                    'product_id' => $productMap[$item->product_id] ?? null,
                    'title' => $item->itemName,
                    'width' => (int) $item->width,
                    'height' => (int) $item->height,
                    'frame' => $item->frame ?: null,
                    'unit_price' => (int) $item->price,
                    'qty' => (int) $item->qty,
                ]);
            }
            $count++;
        }

        $this->info("{$count} old orders imported.");
    }
}
