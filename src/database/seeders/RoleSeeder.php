<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        
        // Create roles
        $superAdmin = Role::create(['name' => 'super_admin']);
        $admin = Role::create(['name' => 'admin']);
        $user = Role::create(['name' => 'user']);
        
        // Create general permissions
        $manageAll = Permission::create(['name' => 'manage.all']);
        
        // Create resource-specific permissions
        // Categories
        $viewCategory = Permission::create(['name' => 'view_category']);
        $createCategory = Permission::create(['name' => 'create_category']);
        $updateCategory = Permission::create(['name' => 'update_category']);
        $deleteCategory = Permission::create(['name' => 'delete_category']);
        $restoreCategory = Permission::create(['name' => 'restore_category']);
        $forceDeleteCategory = Permission::create(['name' => 'force_delete_category']);
        
        // Suppliers
        $viewSupplier = Permission::create(['name' => 'view_supplier']);
        $createSupplier = Permission::create(['name' => 'create_supplier']);
        $updateSupplier = Permission::create(['name' => 'update_supplier']);
        $deleteSupplier = Permission::create(['name' => 'delete_supplier']);
        $restoreSupplier = Permission::create(['name' => 'restore_supplier']);
        $forceDeleteSupplier = Permission::create(['name' => 'force_delete_supplier']);
        
        // Items
        $viewItem = Permission::create(['name' => 'view_item']);
        $createItem = Permission::create(['name' => 'create_item']);
        $updateItem = Permission::create(['name' => 'update_item']);
        $deleteItem = Permission::create(['name' => 'delete_item']);
        $restoreItem = Permission::create(['name' => 'restore_item']);
        $forceDeleteItem = Permission::create(['name' => 'force_delete_item']);
        
        // Transactions
        $viewTransaction = Permission::create(['name' => 'view_transaction']);
        $createTransaction = Permission::create(['name' => 'create_transaction']);
        $updateTransaction = Permission::create(['name' => 'update_transaction']);
        $deleteTransaction = Permission::create(['name' => 'delete_transaction']);
        $restoreTransaction = Permission::create(['name' => 'restore_transaction']);
        $forceDeleteTransaction = Permission::create(['name' => 'force_delete_transaction']);
        
        // Users
        $viewUser = Permission::create(['name' => 'view_user']);
        $createUser = Permission::create(['name' => 'create_user']);
        $updateUser = Permission::create(['name' => 'update_user']);
        $deleteUser = Permission::create(['name' => 'delete_user']);
        $restoreUser = Permission::create(['name' => 'restore_user']);
        $forceDeleteUser = Permission::create(['name' => 'force_delete_user']);
        
        // Give super_admin all permissions
        $superAdmin->givePermissionTo($manageAll);
        $superAdmin->givePermissionTo([
            // Category permissions
            $viewCategory, $createCategory, $updateCategory, $deleteCategory, $restoreCategory, $forceDeleteCategory,
            // Supplier permissions
            $viewSupplier, $createSupplier, $updateSupplier, $deleteSupplier, $restoreSupplier, $forceDeleteSupplier,
            // Item permissions
            $viewItem, $createItem, $updateItem, $deleteItem, $restoreItem, $forceDeleteItem,
            // Transaction permissions
            $viewTransaction, $createTransaction, $updateTransaction, $deleteTransaction, $restoreTransaction, $forceDeleteTransaction,
            // User permissions
            $viewUser, $createUser, $updateUser, $deleteUser, $restoreUser, $forceDeleteUser,
        ]);
        
        // Admin permissions
        $admin->givePermissionTo([
            // Category permissions
            $viewCategory, $createCategory, $updateCategory, $deleteCategory,
            // Supplier permissions
            $viewSupplier, $createSupplier, $updateSupplier, $deleteSupplier,
            // Item permissions
            $viewItem, $createItem, $updateItem, $deleteItem,
            // Transaction permissions
            $viewTransaction, $createTransaction, $updateTransaction, $deleteTransaction,
        ]);
        
        // User permissions
        $user->givePermissionTo([
            // Category permissions
            $viewCategory,
            // Supplier permissions
            $viewSupplier,
            // Item permissions
            $viewItem,
            // Transaction permissions
            $viewTransaction, $createTransaction,
        ]);
    }
}
