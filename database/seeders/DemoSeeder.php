<?php

namespace Database\Seeders;

use App\Models\PaintingStyle;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Local-only demo content. The "paintings" are drawn with GD so the
 * site has something to show before the real photos are uploaded.
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $styles = PaintingStyle::pluck('id', 'slug');

        $tags = collect(['Sarajevo', 'Pejzaž', 'Grad', 'Apstraktno', 'Priroda'])
            ->mapWithKeys(fn ($n) => [$n => Tag::firstOrCreate(['slug' => Str::slug($n)], ['name' => $n])->id]);

        $works = [
            ['Sebilj u sumrak', 'panorama', 'city', [[38, 60, 90], [201, 143, 74]], ['Sarajevo', 'Grad'], true, [[50, 70], [70, 100]]],
            ['Baščaršija, zima', 'realizam', 'city', [[70, 82, 104], [226, 214, 200]], ['Sarajevo', 'Grad'], true, [[40, 50], [60, 80]]],
            ['Trebević u magli', 'panorama', 'landscape', [[60, 76, 72], [190, 170, 130]], ['Pejzaž', 'Priroda'], true, [[60, 90], [100, 150]]],
            ['Crveni ritam', 'apstrakcija', 'abstract', [[120, 30, 28], [230, 160, 90]], ['Apstraktno'], true, [[50, 50], [80, 80]]],
            ['Miljacka', 'realizam', 'landscape', [[34, 52, 64], [160, 190, 196]], ['Sarajevo', 'Priroda'], true, [[50, 70]]],
            ['Zlatni sloj', 'apstrakcija', 'abstract', [[40, 34, 26], [201, 161, 94]], ['Apstraktno'], true, [[40, 60], [70, 100]]],
            ['Vijećnica', 'realizam', 'city', [[92, 60, 48], [236, 200, 150]], ['Sarajevo', 'Grad'], false, [[50, 70], [70, 100]]],
            ['Ljeto na Vrelu Bosne', 'panorama', 'landscape', [[46, 84, 58], [226, 210, 140]], ['Pejzaž', 'Priroda'], false, [[70, 100]]],
            ['Plava tišina', 'apstrakcija', 'abstract', [[22, 36, 64], [130, 170, 210]], ['Apstraktno'], false, [[60, 60], [90, 90]]],
        ];

        foreach ($works as $i => [$title, $style, $kind, $palette, $tagNames, $featured, $sizes]) {
            $slug = Str::slug($title);
            $path = "paintings/demo-{$slug}.jpg";
            [$w, $h] = $sizes[0];
            Storage::disk('public')->put($path, $this->paint($kind, $palette, $w, $h, $i));

            $product = Product::updateOrCreate(['slug' => $slug], [
                'title' => $title,
                'description' => "Original, ulje na platnu. Svaka slika je ručno naslikana i potpisana na poleđini.\n\nDemo opis — zamijenite pravim tekstom u adminu.",
                'image' => $path,
                'technique' => $style === 'apstrakcija' ? 'Akril na platnu' : 'Ulje na platnu',
                'year' => 2023 + ($i % 3),
                'painting_style_id' => $styles[$style],
                'is_featured' => $featured,
                'is_available' => true,
                'sort' => $i,
            ]);

            $multiplier = PaintingStyle::find($styles[$style])->multiplier;
            $product->sizes()->delete();
            foreach ($sizes as $j => [$sw, $sh]) {
                $product->sizes()->create([
                    'width' => $sw, 'height' => $sh, 'sort' => $j,
                    'price' => \App\Support\PriceCalculator::price($sw, $sh, $multiplier),
                ]);
            }
            $product->tags()->sync($tagNames ? array_map(fn ($n) => $tags[$n], $tagNames) : []);
        }

        // A few demo extras: two paintings on sale, a few sizes ready to ship, one sold-out original.
        Product::where('slug', 'crveni-ritam')->update(['discount_percent' => 20, 'discount_ends_at' => now()->addDays(14)]);
        Product::where('slug', 'miljacka')->update(['discount_percent' => 15]);
        foreach (['sebilj-u-sumrak', 'zlatni-sloj', 'miljacka', 'trebevic-u-magli'] as $slug) {
            Product::where('slug', $slug)->first()?->sizes()->first()?->update(['stock' => 1]);
        }
        Product::where('slug', 'plava-tisina')->update(['made_to_order' => false]);

        // Demo gift offer: buy two paintings, pick a small one for free.
        $promo = Promotion::updateOrCreate(
            ['title' => 'Kupite dvije, treća na poklon'],
            ['min_paintings' => 2, 'is_active' => true, 'ends_at' => now()->addMonth()],
        );
        $promo->gifts()->sync(
            Product::whereIn('slug', ['miljacka', 'zlatni-sloj', 'bascarsija-zima'])->with('sizes')->get()
                ->map(fn ($p) => $p->sizes->sortBy('price')->first()->id)->all()
        );

        foreach (['apstrakcija' => 3, 'realizam' => 1, 'panorama' => 0] as $slug => $index) {
            PaintingStyle::where('slug', $slug)->update(['image' => 'paintings/demo-'.Str::slug($works[$index][0]).'.jpg']);
        }
    }

    private function paint(string $kind, array $palette, int $wCm, int $hCm, int $seed): string
    {
        mt_srand($seed * 7919 + 13);
        $W = 900;
        $H = (int) round($W * $hCm / $wCm);
        $im = imagecreatetruecolor($W, $H);
        imagealphablending($im, true);
        [$a, $b] = $palette;

        // Sky / ground gradient
        for ($y = 0; $y < $H; $y++) {
            $t = $y / $H;
            $c = imagecolorallocate($im, ...$this->mix($a, $b, $kind === 'abstract' ? $t * 0.6 : $t));
            imageline($im, 0, $y, $W, $y, $c);
        }

        if ($kind === 'abstract') {
            for ($i = 0; $i < 26; $i++) {
                $col = $this->mix($a, $b, mt_rand(0, 100) / 100);
                $c = imagecolorallocatealpha($im, ...[...$this->jitter($col, 40), mt_rand(55, 100)]);
                $x = mt_rand(-100, $W);
                $y = mt_rand(-100, $H);
                if ($i % 3 === 0) {
                    imagefilledellipse($im, $x, $y, mt_rand(120, 480), mt_rand(120, 480), $c);
                } else {
                    imagefilledrectangle($im, $x, $y, $x + mt_rand(60, 420), $y + mt_rand(20, 160), $c);
                }
            }
            $gold = imagecolorallocatealpha($im, 226, 190, 126, 30);
            imagesetthickness($im, 6);
            imagearc($im, (int) ($W * 0.6), (int) ($H * 0.45), (int) ($W * 0.7), (int) ($W * 0.7), 200, 330, $gold);
        } else {
            $sun = imagecolorallocatealpha($im, 246, 226, 180, 20);
            imagefilledellipse($im, (int) ($W * (0.25 + mt_rand(0, 50) / 100)), (int) ($H * 0.28), 120, 120, $sun);

            $horizon = (int) ($H * 0.62);
            for ($layer = 0; $layer < 3; $layer++) {
                $col = $this->mix($a, [10, 10, 10], 0.25 + $layer * 0.22);
                $c = imagecolorallocatealpha($im, ...[...$col, 15]);
                $points = [0, $H];
                for ($x = 0; $x <= $W; $x += 60) {
                    $base = $horizon + $layer * 50;
                    $y = $kind === 'landscape'
                        ? $base - (int) (sin(($x + $layer * 130 + $seed * 50) / 140) * 60) - mt_rand(0, 30)
                        : $base - mt_rand(20, 120);
                    array_push($points, $x, $y);
                    if ($kind === 'city' && $layer === 1 && mt_rand(0, 4) === 0) {
                        imagefilledellipse($im, $x + 30, $y, 70, 70, $c); // dome
                        imagefilledrectangle($im, $x + 70, $y - 160, $x + 78, $y, $c); // minaret
                        imagefilledpolygon($im, [$x + 66, $y - 160, $x + 74, $y - 195, $x + 82, $y - 160], $c);
                    }
                }
                array_push($points, $W, $H);
                imagefilledpolygon($im, $points, $c);
            }
            if ($kind === 'city') {
                $win = imagecolorallocatealpha($im, 255, 214, 140, 40);
                for ($i = 0; $i < 60; $i++) {
                    $x = mt_rand(0, $W);
                    $y = mt_rand((int) ($H * 0.7), $H - 10);
                    imagefilledrectangle($im, $x, $y, $x + 5, $y + 7, $win);
                }
            }
        }

        // Brush texture
        for ($i = 0; $i < 9000; $i++) {
            $x = mt_rand(0, $W);
            $y = mt_rand(0, $H);
            $c = imagecolorallocatealpha($im, 255, 255, 255, mt_rand(118, 126));
            imageline($im, $x, $y, $x + mt_rand(4, 14), $y + mt_rand(-2, 2), $c);
        }

        ob_start();
        imagejpeg($im, null, 86);
        imagedestroy($im);

        return ob_get_clean();
    }

    private function mix(array $a, array $b, float $t): array
    {
        return array_map(fn ($x, $y) => (int) round($x + ($y - $x) * $t), $a, $b);
    }

    private function jitter(array $c, int $r): array
    {
        return array_map(fn ($x) => max(0, min(255, $x + mt_rand(-$r, $r))), $c);
    }
}
