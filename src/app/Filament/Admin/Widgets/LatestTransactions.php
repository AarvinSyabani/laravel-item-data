<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Transaction;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class LatestTransactions extends TableWidget
{
    protected static ?int $sort = 3;
    
    protected int | string | array $columnSpan = 'full';
    
    protected static ?string $heading = 'Latest Transactions';
    
    protected function getTableQuery(): Builder
    {
        return Transaction::query()
            ->latest('date')
            ->limit(5);
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('transaction_no')
                ->searchable(),
            Tables\Columns\TextColumn::make('date')
                ->dateTime()
                ->sortable(),
            Tables\Columns\TextColumn::make('type')
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'in' => 'success',
                    'out' => 'danger',
                    default => 'gray',
                })
                ->formatStateUsing(fn (string $state): string => match ($state) {
                    'in' => 'Incoming',
                    'out' => 'Outgoing',
                    default => $state,
                }),
            Tables\Columns\TextColumn::make('transactionItems_count')
                ->counts('transactionItems')
                ->label('Items'),
            Tables\Columns\TextColumn::make('total_value')
                ->money('IDR')
                ->getStateUsing(function (Transaction $record): float {
                    return $record->transactionItems->sum(function ($item) {
                        return $item->price * $item->quantity;
                    });
                }),
        ];
    }

    protected function getTableActions(): array
    {
        return [
            Tables\Actions\Action::make('view')
                ->url(fn (Transaction $record): string => route('filament.admin.resources.transactions.edit', $record))
                ->icon('heroicon-o-eye'),
        ];
    }
}
