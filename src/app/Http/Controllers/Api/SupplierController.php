<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('viewAny', Supplier::class);
        
        $suppliers = Supplier::all();
        
        // Use masked email and phone for security when listing all suppliers
        $suppliers->each(function ($supplier) {
            $supplier->phone = $supplier->masked_phone;
            $supplier->email = $supplier->masked_email;
        });
        
        return response()->json([
            'data' => $suppliers
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Supplier::class);
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'contact_person' => 'required|string|max:255',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }
        
        $supplier = Supplier::create($validator->validated());
        
        // Log activity
        activity()
            ->causedBy($request->user())
            ->performedOn($supplier)
            ->log('Created supplier');
        
        return response()->json([
            'message' => 'Supplier berhasil dibuat',
            'data' => $supplier
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Supplier $supplier)
    {
        $this->authorize('view', $supplier);
        
        // If user doesn't have full access permission, show masked sensitive fields
        if (!$request->user()->can('view_sensitive_supplier_info')) {
            $supplier->phone = $supplier->masked_phone;
            $supplier->email = $supplier->masked_email;
        }
        
        return response()->json([
            'data' => $supplier
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Supplier $supplier)
    {
        $this->authorize('update', $supplier);
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'contact_person' => 'required|string|max:255',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }
        
        $supplier->update($validator->validated());
        
        // Log activity
        activity()
            ->causedBy($request->user())
            ->performedOn($supplier)
            ->log('Updated supplier');
        
        return response()->json([
            'message' => 'Supplier berhasil diperbarui',
            'data' => $supplier
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Supplier $supplier)
    {
        $this->authorize('delete', $supplier);
        
        // Check if supplier has items
        if ($supplier->items()->exists()) {
            return response()->json([
                'message' => 'Supplier tidak dapat dihapus karena masih memiliki item terkait'
            ], 422);
        }
        
        // Log activity
        activity()
            ->causedBy($request->user())
            ->performedOn($supplier)
            ->log('Deleted supplier');
            
        $supplier->delete();
        
        return response()->json([
            'message' => 'Supplier berhasil dihapus'
        ]);
    }
}
