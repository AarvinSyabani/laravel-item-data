<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('viewAny', Item::class);
        
        $items = Item::with(['category', 'supplier'])->get();
        
        return response()->json([
            'data' => $items
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Item::class);
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sku' => 'required|string|max:50|unique:items',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'supplier_id' => 'required|exists:suppliers,id',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }
        
        $item = Item::create($validator->validated());
        
        // Log activity
        activity()
            ->causedBy($request->user())
            ->performedOn($item)
            ->log('Created item');
        
        return response()->json([
            'message' => 'Item berhasil dibuat',
            'data' => $item
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Item $item)
    {
        $this->authorize('view', $item);
        
        // Load relationships
        $item->load(['category', 'supplier']);
        
        return response()->json([
            'data' => $item
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Item $item)
    {
        $this->authorize('update', $item);
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sku' => 'required|string|max:50|unique:items,sku,' . $item->id,
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'supplier_id' => 'required|exists:suppliers,id',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Check if user has permission to update stock
        if ($request->has('stock') && $item->stock != $request->stock) {
            $this->authorize('updateStock', $item);
        }
        
        $item->update($validator->validated());
        
        // Log activity
        activity()
            ->causedBy($request->user())
            ->performedOn($item)
            ->log('Updated item');
        
        return response()->json([
            'message' => 'Item berhasil diperbarui',
            'data' => $item
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Item $item)
    {
        $this->authorize('delete', $item);
        
        // Check if item has transaction items
        if ($item->transactionItems()->exists()) {
            return response()->json([
                'message' => 'Item tidak dapat dihapus karena memiliki transaksi terkait'
            ], 422);
        }
        
        // Log activity
        activity()
            ->causedBy($request->user())
            ->performedOn($item)
            ->log('Deleted item');
            
        $item->delete();
        
        return response()->json([
            'message' => 'Item berhasil dihapus'
        ]);
    }
    
    /**
     * Get low stock items
     */
    public function lowStock()
    {
        $this->authorize('viewAny', Item::class);
        
        $items = Item::where('stock', '<', 10)
            ->with(['category', 'supplier'])
            ->orderBy('stock')
            ->limit(10)
            ->get();
        
        return response()->json([
            'data' => $items
        ]);
    }
}
