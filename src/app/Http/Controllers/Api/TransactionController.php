<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('viewAny', Transaction::class);
        
        $transactions = Transaction::with('transactionItems.item')->get();
        
        return response()->json([
            'data' => $transactions
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Transaction::class);
        
        // Additional authorization check for transaction type
        if ($request->input('type') === 'in') {
            $this->authorize('processIncoming', Transaction::class);
        } else if ($request->input('type') === 'out') {
            $this->authorize('processOutgoing', Transaction::class);
        }
        
        $validator = Validator::make($request->all(), [
            'transaction_no' => 'required|string|max:50|unique:transactions',
            'date' => 'required|date',
            'type' => 'required|in:in,out',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Validate sufficient stock for outgoing transactions
        if ($request->input('type') === 'out') {
            foreach ($request->input('items') as $item) {
                $inventoryItem = Item::find($item['item_id']);
                if ($inventoryItem->stock < $item['quantity']) {
                    return response()->json([
                        'message' => "Stok tidak cukup untuk item: {$inventoryItem->name}",
                        'errors' => [
                            "items" => ["Stok tidak cukup untuk item: {$inventoryItem->name}"]
                        ]
                    ], 422);
                }
            }
        }
        
        try {
            DB::beginTransaction();
            
            // Create transaction
            $transaction = Transaction::create([
                'transaction_no' => $request->input('transaction_no'),
                'date' => $request->input('date'),
                'type' => $request->input('type'),
            ]);
            
            // Create transaction items and update stock
            foreach ($request->input('items') as $item) {
                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'item_id' => $item['item_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);
                
                // Update item stock
                $inventoryItem = Item::find($item['item_id']);
                
                if ($request->input('type') === 'in') {
                    $inventoryItem->stock += $item['quantity'];
                } else {
                    $inventoryItem->stock -= $item['quantity'];
                }
                
                $inventoryItem->save();
            }
            
            DB::commit();
            
            // Log activity
            activity()
                ->causedBy($request->user())
                ->performedOn($transaction)
                ->log('Created ' . ($request->input('type') === 'in' ? 'incoming' : 'outgoing') . ' transaction');
            
            return response()->json([
                'message' => 'Transaksi berhasil dibuat',
                'data' => $transaction->load('transactionItems.item')
            ], 201);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'message' => 'Gagal membuat transaksi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Transaction $transaction)
    {
        $this->authorize('view', $transaction);
        
        $transaction->load('transactionItems.item');
        
        return response()->json([
            'data' => $transaction
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Transaction $transaction)
    {
        $this->authorize('update', $transaction);
        
        // We should be very careful with updating transactions
        // In a real-world scenario, you might want to disallow updates
        // after a certain period or implement a full reversal + new transaction
        
        $validator = Validator::make($request->all(), [
            'transaction_no' => 'required|string|max:50|unique:transactions,transaction_no,' . $transaction->id,
            'date' => 'required|date',
            // Typically, you wouldn't allow changing the type of a transaction
            // But if you do, you need to handle stock properly
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Only allow updating metadata (transaction_no, date), not the items
        // For complete data integrity and audit trail
        $transaction->update([
            'transaction_no' => $request->input('transaction_no'),
            'date' => $request->input('date'),
        ]);
        
        // Log activity
        activity()
            ->causedBy($request->user())
            ->performedOn($transaction)
            ->log('Updated transaction metadata');
        
        return response()->json([
            'message' => 'Transaksi berhasil diperbarui',
            'data' => $transaction
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Transaction $transaction)
    {
        $this->authorize('delete', $transaction);
        
        try {
            DB::beginTransaction();
            
            // Revert stock changes
            foreach ($transaction->transactionItems as $transactionItem) {
                $item = $transactionItem->item;
                
                if ($transaction->type === 'in') {
                    // If this was an incoming transaction, reduce the stock
                    $item->stock -= $transactionItem->quantity;
                } else {
                    // If this was an outgoing transaction, increase the stock
                    $item->stock += $transactionItem->quantity;
                }
                
                $item->save();
            }
            
            // Delete transaction items first
            $transaction->transactionItems()->delete();
            
            // Delete transaction
            $transaction->delete();
            
            DB::commit();
            
            // Log activity
            activity()
                ->causedBy($request->user())
                ->log('Deleted transaction #' . $transaction->transaction_no);
            
            return response()->json([
                'message' => 'Transaksi berhasil dihapus dan stok disesuaikan'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'message' => 'Gagal menghapus transaksi',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get latest transactions
     */
    public function latest()
    {
        $this->authorize('viewAny', Transaction::class);
        
        $transactions = Transaction::with('transactionItems.item')
            ->latest('date')
            ->limit(5)
            ->get();
        
        return response()->json([
            'data' => $transactions
        ]);
    }
}
