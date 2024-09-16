<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str; // Add this line
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            ['name' => 'Lightweight Jacket', 'price' => 58.79, 'catalog' => 'Women'],
            ['name' => 'Men\'s T-Shirt', 'price' => 25.00, 'catalog' => 'Men'],
            ['name' => 'Leather Handbag', 'price' => 120.00, 'catalog' => 'Bag'],
            ['name' => 'Running Shoes', 'price' => 75.50, 'catalog' => 'Shoes'],
            ['name' => 'Smartwatch', 'price' => 199.99, 'catalog' => 'Watches'],
            ['name' => 'Windbreaker', 'price' => 45.00, 'catalog' => 'Women'],
            ['name' => 'Casual Sneakers', 'price' => 60.00, 'catalog' => 'Shoes'],
            ['name' => 'Formal Watch', 'price' => 150.00, 'catalog' => 'Watches'],
        ];

        foreach ($products as $product) {
            DB::table('product')->insert([
                'name' => $product['name'],
                'price' => $product['price'],
                'paragraph' => Str::words('Description for ' . $product['name'], 350), // Limit to 350 words
                'catalog' => $product['catalog'],
            ]);
        }
    }
}
