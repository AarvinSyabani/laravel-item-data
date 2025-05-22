<?php

namespace App\Filament\Widgets;

use App\Models\Category;
use App\Models\Item;
use App\Models\Supplier;
use App\Models\Transaction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;
    
    protected function getStats(): array
    {
        $totalItems = Item::count();
        $totalCategories = Category::count();
        $totalSuppliers = Supplier::count();
        $lowStockItems = Item::where('stock', '<', 10)->count();
        
        $transactions = Transaction::where('date', '>=', now()->subDays(30))->count();
        $incomingTransactions = Transaction::where('type', 'in')
            ->where('date', '>=', now()->subDays(30))
            ->count();
        $outgoingTransactions = Transaction::where('type', 'out')
            ->where('date', '>=', now()->subDays(30))
            ->count();
            
        return [
            Stat::make('Total Items', $totalItems)
                ->description('Total number of items in inventory')
                ->descriptionIcon('heroicon-m-cube')
                ->color('primary'),
                
            Stat::make('Low Stock Items', $lowStockItems)
                ->description('Items with stock less than 10')
                ->descriptionIcon('heroicon-m-exclamation-circle')
                ->color($lowStockItems > 0 ? 'danger' : 'success'),
                
            Stat::make('Categories', $totalCategories)
                ->description('Total number of categories')
                ->descriptionIcon('heroicon-m-tag')
                ->color('success'),
                
            Stat::make('Suppliers', $totalSuppliers)
                ->description('Total number of suppliers')
                ->descriptionIcon('heroicon-m-truck')
                ->color('warning'),
                
            Stat::make('Transactions (30 days)', $transactions)
                ->description('Total transactions in last 30 days')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('primary'),
                
            Stat::make('Incoming (30 days)', $incomingTransactions)
                ->description('Incoming transactions in last 30 days')
                ->descriptionIcon('heroicon-m-arrow-down-tray')
                ->color('success'),
                
            Stat::make('Outgoing (30 days)', $outgoingTransactions)
                ->description('Outgoing transactions in last 30 days')
                ->descriptionIcon('heroicon-m-arrow-up-tray')
                ->color('danger'),
        ];
    }
}
