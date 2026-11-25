<?php

namespace App\Livewire;

use App\Models\PaintingStyle;
use App\Support\Cart;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

class CommissionCalculator extends Component
{
    use WithFileUploads;

    /** Optional photo the customer wants painted. */
    public $photo = null;

    /** Starting choice from a shared quote link: ?stil=realizam&w=70&h=100&ram=gold */
    public array $initial = [];

    public function mount(): void
    {
        $this->initial = array_filter([
            'style' => request()->query('stil'),
            'width' => (int) request()->query('w') ?: null,
            'height' => (int) request()->query('h') ?: null,
            'frame' => array_key_exists((string) request()->query('ram'), config('shop.frames')) ? request()->query('ram') : null,
        ]);
    }

    public function updatedPhoto(): void
    {
        $this->validateOnly('photo', ['photo' => $this->photoRules()], $this->photoMessages());
    }

    public function removePhoto(): void
    {
        $this->reset('photo');
    }

    public function add(int $styleId, int $width, int $height, string $frame, Cart $cart)
    {
        $style = PaintingStyle::active()->find($styleId);

        validator(compact('styleId', 'width', 'height', 'frame') + ['photo' => $this->photo], [
            'styleId' => ['required', Rule::exists('painting_styles', 'id')->where('is_active', true)],
            'width' => ['required', 'integer', 'between:'.($style->min_width ?? 1).','.($style->max_width ?? 1)],
            'height' => ['required', 'integer', 'between:'.($style->min_height ?? 1).','.($style->max_height ?? 1)],
            'frame' => ['required', Rule::in(array_keys(config('shop.frames')))],
            'photo' => $this->photoRules(),
        ], [
            'width.between' => 'Širina za ovaj stil mora biti između :min i :max cm.',
            'height.between' => 'Visina za ovaj stil mora biti između :min i :max cm.',
            'styleId.*' => 'Izaberite stil.',
        ] + $this->photoMessages())->validate();

        $path = null;
        if ($this->photo && $style->allows_photo) {
            $path = $this->photo->storeAs('cart-uploads', Str::uuid().'.'.$this->photo->extension(), 'local');
        }

        $cart->addCommission($styleId, $width, $height, $frame, 1, $path);
        $this->dispatch('cart-updated');

        return $this->redirectRoute('cart', navigate: true);
    }

    private function photoRules(): array
    {
        return ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,heic', 'max:15360'];
    }

    private function photoMessages(): array
    {
        return [
            'photo.image' => 'Odaberite fotografiju (JPG, PNG ili WEBP).',
            'photo.mimes' => 'Odaberite fotografiju (JPG, PNG ili WEBP).',
            'photo.max' => 'Fotografija može imati najviše 15 MB.',
            'photo.uploaded' => 'Fotografija se nije učitala. Probajte manju (do 15 MB).',
        ];
    }

    public function render()
    {
        $styles = PaintingStyle::active()->get();

        return view('livewire.commission-calculator', [
            'styles' => $styles,
            'payload' => [
                'styles' => $styles->map(fn ($s) => [
                    'id' => $s->id,
                    'slug' => $s->slug,
                    'name' => $s->name,
                    'multiplier' => $s->multiplier,
                    'image' => $s->imageUrl(),
                    'minW' => $s->min_width,
                    'maxW' => $s->max_width,
                    'minH' => $s->min_height,
                    'maxH' => $s->max_height,
                    'photo' => $s->allows_photo,
                ])->values(),
                'frames' => collect(config('shop.frames'))->map(fn ($f, $k) => ['key' => $k] + $f)->values(),
                'initial' => $this->initial,
            ],
        ]);
    }
}
