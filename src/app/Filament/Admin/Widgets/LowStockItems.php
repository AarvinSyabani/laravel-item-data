<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Item;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class LowStockItems extends TableWidget
{
    protected static ?int $sort = 2;
    
    protected int | string | array $columnSpan = 'full';
    
    protected static ?string $heading = 'Low Stock Items';
    
    protected function getTableQuery(): Builder
    {
        return Item::query()
            ->where('stock', '<', 10)
            ->orderBy('stock')
            ->limit(10);
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('name')
                ->searchable(),
            Tables\Columns\TextColumn::make('sku')
                ->searchable(),
            Tables\Columns\TextColumn::make('category.name')
                ->sortable(),
            Tables\Columns\TextColumn::make('supplier.name')
                ->sortable(),
            Tables\Columns\TextColumn::make('price')
                ->money('IDR')
                ->sortable(),
            Tables\Columns\TextColumn::make('stock')
                ->numeric()
                ->sortable()
                ->color('danger'),
        ];
    }

    protected function getTableActions(): array
    {
        return [
            Tables\Actions\Action::make('view')
                ->url(fn (Item $record): string => route('filament.admin.resources.items.edit', $record))
                ->icon('heroicon-o-eye'),
        ];
    }
}
