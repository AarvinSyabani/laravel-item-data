<?php

namespace Database\Seeders;

use App\Models\Item;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    public function run(): void
    {
        $items = Item::all();

        for ($i = 1; $i <= 5; $i++) {
            $transaction = Transaction::create([
                'transaction_no' => 'TRX-IN-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'date' => now()->subDays(rand(1, 30)),
                'type' => 'in',
                'notes' => 'Incoming stock transaction #' . $i,
            ]);

            $randomItems = $items->random(rand(2, 4));
            foreach ($randomItems as $item) {
                $quantity = rand(5, 20);

                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'item_id' => $item->id,
                    'quantity' => $quantity,
                    'price' => $item->price,
                ]);

                $item->stock += $quantity;
                $item->save();
            }
        }

        for ($i = 1; $i <= 8; $i++) {
            $transaction = Transaction::create([
                'transaction_no' => 'TRX-OUT-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'date' => now()->subDays(rand(1, 20)),
                'type' => 'out',
                'notes' => 'Outgoing stock transaction #' . $i,
            ]);

            $randomItems = $items->random(rand(1, 3));
            foreach ($randomItems as $item) {
                $maxQuantity = min(5, $item->stock);
                if ($maxQuantity <= 0) continue;

                $quantity = rand(1, $maxQuantity);

                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'item_id' => $item->id,
                    'quantity' => $quantity,
                    'price' => $item->price,
                ]);

                $item->stock -= $quantity;
                $item->save();
            }
        }
    }
}

