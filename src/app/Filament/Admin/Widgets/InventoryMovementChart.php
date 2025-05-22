<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Transaction;
use App\Models\TransactionItem;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class InventoryMovementChart extends ChartWidget
{
    protected static ?string $heading = 'Inventory Movement';

    protected static ?int $sort = 4;
    
    protected function getData(): array
    {
        $days = 30;
        $now = Carbon::now();
        $startDate = $now->copy()->subDays($days);
        
        // Get incoming items
        $incomingData = $this->getTransactionData('in', $startDate, $now);
        
        // Get outgoing items
        $outgoingData = $this->getTransactionData('out', $startDate, $now);
        
        $labels = [];
        $incomingValues = [];
        $outgoingValues = [];
        
        // Create a range of dates
        for ($i = 0; $i < $days; $i++) {
            $date = $startDate->copy()->addDays($i)->format('Y-m-d');
            $labels[] = $startDate->copy()->addDays($i)->format('M d');
            
            $incomingValues[] = $incomingData[$date] ?? 0;
            $outgoingValues[] = $outgoingData[$date] ?? 0;
        }
        
        return [
            'datasets' => [
                [
                    'label' => 'Incoming Items',
                    'data' => $incomingValues,
                    'backgroundColor' => 'rgba(34, 197, 94, 0.5)',
                    'borderColor' => 'rgb(34, 197, 94)',
                ],
                [
                    'label' => 'Outgoing Items',
                    'data' => array_map(function ($value) {
                        return -$value; // Negate values to show below the x-axis
                    }, $outgoingValues),
                    'backgroundColor' => 'rgba(239, 68, 68, 0.5)',
                    'borderColor' => 'rgb(239, 68, 68)',
                ],
            ],
            'labels' => $labels,
        ];
    }
    
    private function getTransactionData(string $type, Carbon $startDate, Carbon $endDate): array
    {
        return TransactionItem::select(
                DB::raw('DATE(transactions.date) as date'),
                DB::raw('SUM(transaction_items.quantity) as total_quantity')
            )
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->where('transactions.type', $type)
            ->whereBetween('transactions.date', [$startDate, $endDate])
            ->groupBy('date')
            ->pluck('total_quantity', 'date')
            ->toArray();
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
