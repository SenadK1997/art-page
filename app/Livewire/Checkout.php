<?php

namespace App\Livewire;

use App\Support\Cart;
use App\Support\PlaceOrder;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts::site')]
#[Title('Narudžba')]
class Checkout extends Component
{
    public string $full_name = '';

    public string $phone = '';

    public string $email = '';

    public string $address = '';

    public string $city = '';

    public string $zip = '';

    public string $country = 'Bosna i Hercegovina';

    public string $note = '';

    // Honeypot: real people never see or fill this field.
    public string $website = '';

    public function mount(Cart $cart)
    {
        if ($cart->isEmpty()) {
            return $this->redirectRoute('cart', navigate: true);
        }
    }

    protected function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'min:3', 'max:120'],
            'phone' => ['required', 'string', 'regex:/^[0-9+\/\-\s()]{6,25}$/'],
            'email' => ['nullable', 'email', 'max:160'],
            'address' => ['required', 'string', 'max:200'],
            'city' => ['required', 'string', 'max:100'],
            'zip' => ['nullable', 'string', 'max:20'],
            'country' => ['required', 'string', 'max:100'],
            'note' => ['nullable', 'string', 'max:2000'],
        ];
    }

    protected function messages(): array
    {
        return [
            'required' => 'Ovo polje je obavezno.',
            'full_name.min' => 'Upišite ime i prezime.',
            'phone.regex' => 'Upišite ispravan broj telefona.',
            'email.email' => 'Upišite ispravnu email adresu.',
        ];
    }

    public function submit(PlaceOrder $placeOrder, Cart $cart)
    {
        $data = $this->validate();

        if ($this->website !== '') {
            return $this->redirectRoute('home');
        }

        $key = 'checkout:'.request()->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->addError('full_name', 'Previše pokušaja. Pokušajte ponovo za nekoliko minuta.');

            return;
        }
        RateLimiter::hit($key, 600);

        if ($cart->isEmpty()) {
            return $this->redirectRoute('cart', navigate: true);
        }

        $order = $placeOrder->handle(array_map(fn ($v) => $v === '' ? null : $v, $data));
        $this->dispatch('cart-updated');

        return $this->redirect(URL::signedRoute('order.thanks', $order));
    }

    public function render(Cart $cart)
    {
        return view('livewire.checkout', [
            'lines' => $cart->lines(),
            'summary' => $cart->summary(),
        ]);
    }
}
