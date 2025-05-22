<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Item;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

class ItemSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::all();
        $suppliers = Supplier::all();

        $items = [
            // Electronics
            [
                'name' => 'Smartphone X',
                'description' => 'Latest smartphone with advanced features',
                'sku' => 'ELEC-001',
                'price' => 8999000,
                'stock' => 25,
                'category_id' => $categories->where('name', 'Electronics')->first()->id,
                'supplier_id' => $suppliers->where('name', 'Tech Supplies Inc.')->first()->id,
            ],
            [
                'name' => 'Laptop Pro',
                'description' => 'High-performance laptop for professionals',
                'sku' => 'ELEC-002',
                'price' => 15999000,
                'stock' => 15,
                'category_id' => $categories->where('name', 'Electronics')->first()->id,
                'supplier_id' => $suppliers->where('name', 'Tech Supplies Inc.')->first()->id,
            ],
            [
                'name' => 'Wireless Earbuds',
                'description' => 'Premium wireless earbuds with noise cancellation',
                'sku' => 'ELEC-003',
                'price' => 1999000,
                'stock' => 50,
                'category_id' => $categories->where('name', 'Electronics')->first()->id,
                'supplier_id' => $suppliers->where('name', 'Tech Supplies Inc.')->first()->id,
            ],

            // Furniture
            [
                'name' => 'Office Desk',
                'description' => 'Spacious office desk with drawers',
                'sku' => 'FURN-001',
                'price' => 2499000,
                'stock' => 10,
                'category_id' => $categories->where('name', 'Furniture')->first()->id,
                'supplier_id' => $suppliers->where('name', 'Furniture World')->first()->id,
            ],
            [
                'name' => 'Ergonomic Chair',
                'description' => 'Comfortable chair designed for long working hours',
                'sku' => 'FURN-002',
                'price' => 1899000,
                'stock' => 20,
                'category_id' => $categories->where('name', 'Furniture')->first()->id,
                'supplier_id' => $suppliers->where('name', 'Furniture World')->first()->id,
            ],

            // Clothing
            [
                'name' => 'Casual T-Shirt',
                'description' => 'Comfortable cotton t-shirt',
                'sku' => 'CLOTH-001',
                'price' => 249000,
                'stock' => 100,
                'category_id' => $categories->where('name', 'Clothing')->first()->id,
                'supplier_id' => $suppliers->where('name', 'Fashion Forward')->first()->id,
            ],
            [
                'name' => 'Denim Jeans',
                'description' => 'Classic denim jeans for everyday wear',
                'sku' => 'CLOTH-002',
                'price' => 599000,
                'stock' => 75,
                'category_id' => $categories->where('name', 'Clothing')->first()->id,
                'supplier_id' => $suppliers->where('name', 'Fashion Forward')->first()->id,
            ],

            // Books
            [
                'name' => 'Business Strategy Guide',
                'description' => 'Comprehensive guide to business strategy',
                'sku' => 'BOOK-001',
                'price' => 349000,
                'stock' => 30,
                'category_id' => $categories->where('name', 'Books')->first()->id,
                'supplier_id' => $suppliers->where('name', 'Book Haven')->first()->id,
            ],
            [
                'name' => 'Programming Fundamentals',
                'description' => 'Introduction to programming concepts',
                'sku' => 'BOOK-002',
                'price' => 299000,
                'stock' => 25,
                'category_id' => $categories->where('name', 'Books')->first()->id,
                'supplier_id' => $suppliers->where('name', 'Book Haven')->first()->id,
            ],

            // Food & Beverages
            [
                'name' => 'Premium Coffee Beans',
                'description' => 'High-quality coffee beans from selected regions',
                'sku' => 'FOOD-001',
                'price' => 189000,
                'stock' => 40,
                'category_id' => $categories->where('name', 'Food & Beverages')->first()->id,
                'supplier_id' => $suppliers->where('name', 'Gourmet Distributors')->first()->id,
            ],
            [
                'name' => 'Organic Tea Collection',
                'description' => 'Assorted organic tea varieties',
                'sku' => 'FOOD-002',
                'price' => 159000,
                'stock' => 35,
                'category_id' => $categories->where('name', 'Food & Beverages')->first()->id,
                'supplier_id' => $suppliers->where('name', 'Gourmet Distributors')->first()->id,
            ],
            [
                'name' => 'Chocolate Gift Box',
                'description' => 'Assorted premium chocolates in elegant packaging',
                'sku' => 'FOOD-003',
                'price' => 279000,
                'stock' => 20,
                'category_id' => $categories->where('name', 'Food & Beverages')->first()->id,
                'supplier_id' => $suppliers->where('name', 'Gourmet Distributors')->first()->id,
            ],
        ];

        foreach ($items as $item) {
            Item::create($item);
        }
    }
}
