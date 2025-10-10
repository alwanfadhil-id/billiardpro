<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\Table;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\Http\Response;
use Carbon\Carbon;

class ReportService
{
    /**
     * Generate daily report data
     */
    public function getDailyReportData($date = null)
    {
        $date = $date ? Carbon::parse($date) : Carbon::today();
        
        $transactions = Transaction::whereDate('created_at', $date)
            ->with(['table', 'user', 'items.product'])
            ->get();
            
        $totalRevenue = $transactions->sum('total');
        
        return [
            'date' => $date->format('Y-m-d'),
            'transactions' => $transactions,
            'total_revenue' => $totalRevenue,
            'transaction_count' => $transactions->count(),
            'avg_transaction_value' => $transactions->count() > 0 ? $totalRevenue / $transactions->count() : 0,
        ];
    }

    /**
     * Generate monthly report data
     */
    public function getMonthlyReportData($month = null, $year = null)
    {
        $month = $month ?? Carbon::now()->month;
        $year = $year ?? Carbon::now()->year;
        
        $transactions = Transaction::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->with(['table', 'user', 'items.product'])
            ->get();
            
        $totalRevenue = $transactions->sum('total');
        
        // Group by day of month
        $dailyData = $transactions->groupBy(function ($transaction) {
            return Carbon::parse($transaction->created_at)->day;
        });
        
        return [
            'month' => $month,
            'year' => $year,
            'transactions' => $transactions,
            'daily_data' => $dailyData,
            'total_revenue' => $totalRevenue,
            'transaction_count' => $transactions->count(),
            'avg_transaction_value' => $transactions->count() > 0 ? $totalRevenue / $transactions->count() : 0,
        ];
    }

    /**
     * Generate yearly report data
     */
    public function getYearlyReportData($year = null)
    {
        $year = $year ?? Carbon::now()->year;
        
        $transactions = Transaction::whereYear('created_at', $year)
            ->with(['table', 'user', 'items.product'])
            ->get();
            
        $totalRevenue = $transactions->sum('total');
        
        // Group by month
        $monthlyData = $transactions->groupBy(function ($transaction) {
            return Carbon::parse($transaction->created_at)->format('F');
        });
        
        return [
            'year' => $year,
            'transactions' => $transactions,
            'monthly_data' => $monthlyData,
            'total_revenue' => $totalRevenue,
            'transaction_count' => $transactions->count(),
            'avg_transaction_value' => $transactions->count() > 0 ? $totalRevenue / $transactions->count() : 0,
        ];
    }

    /**
     * Generate product sales report
     */
    public function getProductSalesReport($startDate = null, $endDate = null)
    {
        $query = Transaction::with(['items.product', 'items' => function($q) {
            $q->select('transaction_id', 'product_id', 'quantity', 'total_price')
              ->with(['product' => function($p) {
                  $p->select('id', 'name', 'category');
              }]);
        }]);

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        $transactions = $query->get();

        // Aggregate product sales
        $productSales = collect();
        
        foreach ($transactions as $transaction) {
            foreach ($transaction->items as $item) {
                $productId = $item->product_id;
                
                if ($productSales->has($productId)) {
                    $existing = $productSales->get($productId);
                    $productSales->put($productId, [
                        'product' => $existing['product'],
                        'quantity_sold' => $existing['quantity_sold'] + $item->quantity,
                        'total_revenue' => $existing['total_revenue'] + $item->total_price,
                        'transaction_count' => $existing['transaction_count'] + 1
                    ]);
                } else {
                    $productSales->put($productId, [
                        'product' => $item->product,
                        'quantity_sold' => $item->quantity,
                        'total_revenue' => $item->total_price,
                        'transaction_count' => 1
                    ]);
                }
            }
        }

        return [
            'products' => $productSales->values(),
            'total_products_sold' => $productSales->sum('quantity_sold'),
            'total_revenue_from_products' => $productSales->sum('total_revenue'),
        ];
    }

    /**
     * Generate table usage report
     */
    public function getTableUsageReport($startDate = null, $endDate = null)
    {
        $query = Transaction::with(['table' => function($q) {
            $q->select('id', 'name', 'hourly_rate');
        }]);

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        $transactions = $query->get();

        // Group by table
        $tableUsage = $transactions->groupBy('table_id')->map(function ($transactions, $tableId) {
            $table = $transactions->first()->table;
            return [
                'table' => $table,
                'usage_count' => $transactions->count(),
                'total_duration' => $transactions->sum('duration_minutes'),
                'total_revenue' => $transactions->sum('total'),
                'avg_duration' => $transactions->avg('duration_minutes'),
                'avg_revenue' => $transactions->avg('total'),
            ];
        });

        return [
            'tables' => $tableUsage->values(),
            'total_transactions' => $transactions->count(),
            'total_revenue' => $transactions->sum('total'),
        ];
    }
}