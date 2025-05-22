<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Electronics',
                'description' => 'Electronic devices and accessories',
            ],
            [
                'name' => 'Furniture',
                'description' => 'Home and office furniture',
            ],
            [
                'name' => 'Clothing',
                'description' => 'Apparel and fashion items',
            ],
            [
                'name' => 'Books',
                'description' => 'Books and publications',
            ],
            [
                'name' => 'Food & Beverages',
                'description' => 'Food and drink products',
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
