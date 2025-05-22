<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = [
            [
                'name' => 'Tech Supplies Inc.',
                'address' => '123 Tech Street, Silicon Valley, CA',
                'phone' => '555-123-4567',
                'email' => 'contact@techsupplies.com',
                'contact_person' => 'John Doe',
            ],
            [
                'name' => 'Furniture World',
                'address' => '456 Wood Avenue, Craftsville, NY',
                'phone' => '555-987-6543',
                'email' => 'info@furnitureworld.com',
                'contact_person' => 'Jane Smith',
            ],
            [
                'name' => 'Fashion Forward',
                'address' => '789 Style Blvd, Fashion District, LA',
                'phone' => '555-456-7890',
                'email' => 'orders@fashionforward.com',
                'contact_person' => 'Michael Johnson',
            ],
            [
                'name' => 'Book Haven',
                'address' => '321 Reader Lane, Booktown, MA',
                'phone' => '555-234-5678',
                'email' => 'sales@bookhaven.com',
                'contact_person' => 'Sarah Williams',
            ],
            [
                'name' => 'Gourmet Distributors',
                'address' => '654 Taste Street, Foodville, IL',
                'phone' => '555-876-5432',
                'email' => 'orders@gourmetdist.com',
                'contact_person' => 'Robert Brown',
            ],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::create($supplier);
        }
    }
}
