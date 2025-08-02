<?php

namespace Database\Seeders\Database;

use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    protected $size;
    protected $faker;
    public function __construct()
    {
        $this->size = 50;
        $this->faker = Faker::create();
    }
    public function run(): void
    {
        for ($i = 0; $i < $this->size; $i++) {
            Product::create([
                'name' => $this->faker->word(),
                'description' => $this->faker->sentence(),
                'price' => $this->faker->randomFloat(2, 50000, 150000),
                'stock' => $this->faker->randomNumber(2),
                'image_path' => $this->faker->imageUrl(640, 480, 'food', true, 'Product', true),
                'is_active' => $this->faker->boolean(),
                'tags' => $this->faker->words(3, true),
            ]);
        }
    }
}
