<?php

namespace App\Livewire;

use App\Models\PaintingStyle;
use App\Models\Product;
use App\Models\Tag;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts::site')]
class Gallery extends Component
{
    use WithPagination;

    #[Url(as: 'stil', except: '')]
    public string $style = '';

    #[Url(as: 'tema', except: '')]
    public string $tag = '';

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(as: 'ponuda', except: '')]
    public string $offer = '';

    #[Url(as: 'poredaj', except: 'novo')]
    public string $sort = 'novo';

    public function updated($property): void
    {
        if (in_array($property, ['style', 'tag', 'search', 'sort', 'offer'], true)) {
            $this->resetPage();
        }
    }

    public function clearFilters(): void
    {
        $this->reset('style', 'tag', 'search', 'sort', 'offer');
        $this->resetPage();
    }

    public function render()
    {
        $products = Product::visible()
            ->with('sizes.product', 'style')
            ->when($this->offer === 'akcija', fn ($q) => $q->onSale())
            ->when($this->offer === 'na-stanju', fn ($q) => $q->inStock())
            ->when($this->style, fn ($q) => $q->whereHas('style', fn ($s) => $s->where('slug', $this->style)))
            ->when($this->tag, fn ($q) => $q->whereHas('tags', fn ($t) => $t->where('slug', $this->tag)))
            ->when(trim($this->search) !== '', fn ($q) => $q->where('title', 'like', '%'.trim($this->search).'%'))
            ->when($this->sort === 'cijena-gore', fn ($q) => $q->orderBy(
                \App\Models\ProductSize::selectRaw('min(price)')->whereColumn('product_id', 'products.id')
            ))
            ->when($this->sort === 'cijena-dolje', fn ($q) => $q->orderByDesc(
                \App\Models\ProductSize::selectRaw('min(price)')->whereColumn('product_id', 'products.id')
            ))
            ->when($this->sort === 'novo', fn ($q) => $q->orderByDesc('is_featured')->orderBy('sort')->latest())
            ->paginate(12);

        $tags = Tag::whereHas('products', fn ($q) => $q->visible())->orderBy('name')->get();
        $category = $this->tag ? $tags->firstWhere('slug', $this->tag) : null;

        return view('livewire.gallery', [
            'products' => $products,
            'styles' => PaintingStyle::active()->get(),
            'tags' => $tags,
        ])->layoutData([
            // Each category is its own page for search engines.
            'title' => $category ? $category->name.' — slike' : 'Galerija',
            'description' => $category
                ? "Slike {$category->name} — originali ".config('shop.artist_genitive').', u više veličina. Plaćanje pouzećem.'
                : 'Galerija originalnih slika '.config('shop.artist_genitive').' — Sarajevo, pejzaži, panorame i apstrakcija. Više veličina, plaćanje pouzećem.',
            'image' => $products->first()?->imageUrl(),
        ]);
    }
}
