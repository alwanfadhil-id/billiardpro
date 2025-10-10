<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Barryvdh\DomPDF\Facade\Pdf;

class ExportService
{
    /**
     * Export data to CSV format
     */
    public function exportToCsv($data, $filename = 'export', $headers = [])
    {
        $filename = $filename . '.csv';
        
        // Prepare CSV content
        $output = '';
        
        if (!empty($headers)) {
            $output .= implode(',', $headers) . "\n";
        }
        
        foreach ($data as $row) {
            $csvRow = [];
            foreach ($row as $value) {
                $csvRow[] = is_array($value) || is_object($value) ? json_encode($value) : $value;
            }
            $output .= '"' . implode('","', $csvRow) . '"' . "\n";
        }
        
        return response($output)
            ->withHeaders([
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
    }

    /**
     * Export data to Excel format
     */
    public function exportToExcel($data, $filename = 'export', $headers = [])
    {
        $filename = $filename . '.xlsx';
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Add headers if provided
        if (!empty($headers)) {
            $col = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue($col . '1', $header);
                $col++;
            }
        }
        
        // Add data starting from row 2 (or 1 if no headers)
        $rowIndex = !empty($headers) ? 2 : 1;
        foreach ($data as $dataRow) {
            $col = 'A';
            foreach ($dataRow as $value) {
                $value = is_array($value) || is_object($value) ? json_encode($value) : $value;
                $sheet->setCellValue($col . $rowIndex, $value);
                $col++;
            }
            $rowIndex++;
        }
        
        // Create temporary file
        $writer = new Xlsx($spreadsheet);
        $tempFile = tempnam(sys_get_temp_dir(), 'export_');
        $writer->save($tempFile);
        
        // Return response with file download
        $response = response()->download($tempFile, $filename)->deleteFileAfterSend(true);
        
        return $response;
    }

    /**
     * Export data to PDF format
     */
    public function exportToPdf($data, $filename = 'export', $headers = [], $title = 'Export Report')
    {
        // Format data into table structure for PDF
        $viewData = [
            'title' => $title,
            'headers' => $headers,
            'data' => $data,
            'date' => now()->format('Y-m-d H:i:s'),
        ];
        
        $pdf = Pdf::loadView('exports.pdf-template', $viewData);
        return $pdf->download($filename . '.pdf');
    }

    /**
     * Format daily report data for export
     */
    public function formatDailyReportForExport($reportData)
    {
        $formatted = [];
        
        foreach ($reportData['transactions'] as $transaction) {
            $formatted[] = [
                'ID' => $transaction->id,
                'Table' => $transaction->table->name,
                'Cashier' => $transaction->user->name,
                'Started At' => $transaction->started_at->format('Y-m-d H:i:s'),
                'Ended At' => $transaction->ended_at ? $transaction->ended_at->format('Y-m-d H:i:s') : 'N/A',
                'Duration (min)' => $transaction->duration_minutes,
                'Items' => $transaction->items->map(function($item) {
                    return $item->quantity . 'x ' . $item->product->name;
                })->join(', '),
                'Total' => $transaction->total,
                'Payment Method' => $transaction->payment_method,
                'Status' => $transaction->status,
            ];
        }
        
        return $formatted;
    }

    /**
     * Format monthly report data for export
     */
    public function formatMonthlyReportForExport($reportData)
    {
        $formatted = [];
        
        foreach ($reportData['transactions'] as $transaction) {
            $formatted[] = [
                'Date' => $transaction->created_at->format('Y-m-d'),
                'ID' => $transaction->id,
                'Table' => $transaction->table->name,
                'Cashier' => $transaction->user->name,
                'Started At' => $transaction->started_at->format('H:i'),
                'Ended At' => $transaction->ended_at ? $transaction->ended_at->format('H:i') : 'N/A',
                'Duration (min)' => $transaction->duration_minutes,
                'Total' => $transaction->total,
                'Payment Method' => $transaction->payment_method,
            ];
        }
        
        return $formatted;
    }

    /**
     * Format product sales report for export
     */
    public function formatProductSalesReportForExport($reportData)
    {
        $formatted = [];
        
        foreach ($reportData['products'] as $productData) {
            $formatted[] = [
                'Product Name' => $productData['product']->name,
                'Category' => $productData['product']->category,
                'Quantity Sold' => $productData['quantity_sold'],
                'Total Revenue' => $productData['total_revenue'],
                'Transactions' => $productData['transaction_count'],
            ];
        }
        
        return $formatted;
    }

    /**
     * Format table usage report for export
     */
    public function formatTableUsageReportForExport($reportData)
    {
        $formatted = [];
        
        foreach ($reportData['tables'] as $tableData) {
            $formatted[] = [
                'Table Name' => $tableData['table']->name,
                'Hourly Rate' => $tableData['table']->hourly_rate,
                'Usage Count' => $tableData['usage_count'],
                'Total Duration (min)' => $tableData['total_duration'],
                'Avg Duration (min)' => round($tableData['avg_duration'], 2),
                'Total Revenue' => $tableData['total_revenue'],
                'Avg Revenue' => round($tableData['avg_revenue'], 2),
            ];
        }
        
        return $formatted;
    }
}