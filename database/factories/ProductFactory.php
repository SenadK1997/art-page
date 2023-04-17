<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {   
        return [
            'title' => $this->faker->name,
            'amount' => $this->faker->numberBetween(0,9),
            'description' => $this->faker->sentence,
            'price' => $this->faker->numberBetween(20, 100),
            'url' => 'https://fastly.picsum.photos/id/251/200/300.jpg?hmac=9xXOWzHXFkhqJDfiXSZARyy0pDmdAyazrrJw6VNgoKc'
        ];
    }
}
