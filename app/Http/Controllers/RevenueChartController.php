<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RevenueChartController extends Controller
{
    public function getRevenueData(Request $request)
    {
        // Get revenue data for the last 7 days
        $data = [];
        $labels = [];
        $values = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $startDate = $date->copy()->startOfDay();
            $endDate = $date->copy()->endOfDay();
            
            $dailyRevenue = Transaction::whereBetween('created_at', [$startDate, $endDate])
                ->where('status', 'completed')
                ->sum('total');
                
            $labels[] = $date->format('M d');
            $values[] = (int) $dailyRevenue;
        }
        
        return response()->json([
            'labels' => $labels,
            'values' => $values
        ]);
    }
}