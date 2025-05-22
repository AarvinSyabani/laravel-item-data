<?php

namespace App\Filament\Widgets;

use App\Models\Item;
use Filament\Support\Contracts\TranslatableContentDriver;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Builder;

class LowStockItems extends Widget implements HasTable
{
    use Tables\Concerns\InteractsWithTable;
    
    protected static ?int $sort = 2;
    
    protected int | string | array $columnSpan = 'full';
    
    protected static ?string $heading = 'Low Stock Items';

    public function makeFilamentTranslatableContentDriver(): ?TranslatableContentDriver
    {
        return null;
    }

    public function query(): Builder
    {
        return Item::query()
            ->where('stock', '<', 10)
            ->orderBy('stock')
            ->limit(10);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->query())
            ->columns([
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
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->url(fn (Item $record): string => route('filament.admin.resources.items.edit', $record))
                    ->icon('heroicon-o-eye'),
            ]);
    }
}
