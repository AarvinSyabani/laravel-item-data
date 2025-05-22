<?php

namespace App\Filament\Admin\Widgets;

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
        return [
            Stat::make('Total Items', Item::count())
                ->description('All items in inventory')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('success'),
                
            Stat::make('Low Stock Items', Item::where('stock', '<', 10)->count())
                ->description('Items with stock < 10')
                ->descriptionIcon('heroicon-m-exclamation-circle')
                ->color('danger'),
                
            Stat::make('Total Categories', Category::count())
                ->description('Product categories')
                ->descriptionIcon('heroicon-m-tag')
                ->color('primary'),
                
            Stat::make('Total Suppliers', Supplier::count())
                ->description('Active suppliers')
                ->descriptionIcon('heroicon-m-truck')
                ->color('warning'),
                
            Stat::make('Incoming', Transaction::where('type', 'in')->count())
                ->description('Incoming transactions')
                ->descriptionIcon('heroicon-m-arrow-down-tray')
                ->color('success'),
                
            Stat::make('Outgoing', Transaction::where('type', 'out')->count())
                ->description('Outgoing transactions')
                ->descriptionIcon('heroicon-m-arrow-up-tray')
                ->color('danger'),
        ];
    }
}
