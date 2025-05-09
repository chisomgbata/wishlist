<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->colorName() . ' ' . $this->faker->randomElements(['shirt', 'pants', 'shoes', 'hat', 'jacket'])[0],
            'price' => $this->faker->randomFloat(2, 1, 1000),
            'description' => $this->faker->realText(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
