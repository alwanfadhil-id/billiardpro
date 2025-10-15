<?php

namespace App\Livewire\Transactions;

use Livewire\Component;
use App\Models\Transaction;
use App\Models\TransactionItem;
use mike42\Escpos\Printer;
use mike42\Escpos\PrintConnectors\NetworkPrintConnector;

class PaymentProcess extends Component
{
    public $transactionId;
    public $transaction;
    public $paymentMethod = 'cash';
    public $amountReceived = 0;
    public $change = 0;
    public $showReceiptModal = false;
    
    public function mount($transaction)
    {
        $this->transactionId = $transaction;
        $this->transaction = Transaction::with(['table', 'items.product'])->find($transaction);
        
        if ($this->transaction) {
            // Update the transaction total to ensure it has the correct value
            $this->updateTransactionTotal();
            
            $this->amountReceived = $this->transaction->total;
            $this->calculateChange();
        }
    }
    
    public function render()
    {
        if (!$this->transaction) {
            session()->flash('error', 'Transaction not found.');
            return view('livewire.transactions.payment-process')->layout('components.layouts.app');
        }
        
        $transactionItems = $this->transaction->items;
        $tableCost = $this->calculateTableCost();
        
        return view('livewire.transactions.payment-process', [
            'transaction' => $this->transaction,
            'transactionItems' => $transactionItems,
            'tableCost' => $tableCost,
        ])->layout('components.layouts.app');
    }

    private function calculateTableCost()
    {
        if (!$this->transaction->ended_at) {
            // For ongoing transactions, calculate up to now
            $startedAt = $this->transaction->started_at;
            $now = now();
            $minutes = $startedAt->diffInMinutes($now);
        } else {
            // For completed transactions, use ended_at
            $startedAt = $this->transaction->started_at;
            $endedAt = $this->transaction->ended_at;
            $minutes = $startedAt->diffInMinutes($endedAt);
        }

        $hours = ceil($minutes / 60); // Round up to next hour
        $hourlyRate = $this->transaction->table->hourly_rate;
        return $hourlyRate * $hours;
    }
    
    public function updatedAmountReceived()
    {
        $this->calculateChange();
    }
    
    public function updatedPaymentMethod()
    {
        $this->calculateChange();
    }
    
    private function calculateChange()
    {
        if ($this->transaction) {
            $this->change = max(0, $this->amountReceived - $this->transaction->total);
        }
    }
    
    public function processPayment()
    {
        if (!$this->transaction) {
            session()->flash('error', 'Transaction not found.');
            return;
        }
        
        // Check if the transaction is already completed
        if ($this->transaction->status === 'completed') {
            session()->flash('error', 'Transaksi ini sudah selesai diproses.');
            return;
        }
        
        if ($this->amountReceived < $this->transaction->total) {
            session()->flash('error', 'Jumlah yang diterima kurang dari total tagihan.');
            return;
        }
        
        // Emit event to show loading
        $this->dispatch('paymentStarted');
        
        // Update transaction status to completed
        $this->transaction->update([
            'status' => 'completed',
            'payment_method' => $this->paymentMethod,
            'change_amount' => $this->change,
            'cash_received' => $this->amountReceived,
            'ended_at' => now(),
        ]);
        
        // Update the table status to available after payment is completed
        if ($this->transaction->table) {
            $this->transaction->table->update(['status' => 'available']);
        }
        
        session()->flash('message', 'Pembayaran berhasil diproses.');
        
        // Tidak redirect, hanya tampilkan modal struk
        $this->showReceiptModal = true;
        
        // Emit event to hide loading
        $this->dispatch('paymentFinished');
    }
    
    private function updateTransactionTotal()
    {
        $this->transaction->refresh();
        $tableCost = $this->calculateTableCost();
        $itemsCost = $this->transaction->items->sum('total_price');
        $this->transaction->update(['total' => $tableCost + $itemsCost]);
    }
    
    public function cancelPayment()
    {
        return redirect()->route('transactions.add-items', ['transaction' => $this->transactionId]);
    }
    
    public function closeReceiptModal()
    {
        $this->showReceiptModal = false;
    }
    
    public function printReceipt()
    {
        // Check if thermal printing is enabled
        if (config('app.printer_enabled', false)) {
            $this->printToThermal();
        } else {
            // Fallback to browser print using JavaScript
            $this->dispatch('printReceipt');
        }
    }
    
    private function printToThermal()
    {
        try {
            // Get printer configuration from environment
            $printerIp = config('app.printer_ip', env('PRINTER_IP', '192.168.1.100'));
            $printerPort = config('app.printer_port', env('PRINTER_PORT', 9100));
            $printerName = config('app.printer_name', env('PRINTER_NAME', 'POS-58'));

            // Include the thermal printer library
            require_once base_path('vendor/autoload.php');
            
            // Create network connector
            $connector = new \mike42\Escpos\PrintConnectors\NetworkPrintConnector($printerIp, $printerPort);
            $printer = new \mike42\Escpos\Printer($connector);

            // Prepare receipt content
            $this->generateReceiptContent($printer);

            // Close the connection
            $printer->close();

            session()->flash('message', 'Receipt printed successfully to thermal printer!');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to print to thermal printer: ' . $e->getMessage());
        }
    }
    
    private function generateReceiptContent($printer)
    {
        // Initialize the printer
        $printer->initialize();
        
        // Header
        $printer->setJustification(\mike42\Escpos\Printer::JUSTIFY_CENTER);
        $printer->setTextSize(2, 2); // Larger text
        $printer->text("BILLIARDPRO\n");
        $printer->setTextSize(1, 1); // Normal text
        $printer->text("Jl. Contoh Alamat No. 123\n");
        $printer->text("Telp: (021) 12345678\n");
        $printer->feed();

        // Receipt Info
        $printer->setJustification(\mike42\Escpos\Printer::JUSTIFY_LEFT);
        $printer->text("Receipt No: " . $this->transaction->id . "\n");
        // Use ended_at if available, otherwise use the time of transaction completion
        $dateToShow = $this->transaction->ended_at ?? $this->transaction->updated_at ?? now();
        $printer->text("Date: " . $dateToShow->format('d/m/Y') . "\n");
        $printer->text("Time: " . $dateToShow->format('H:i') . "\n");
        $printer->text("Cashier: " . $this->transaction->user->name . "\n");
        $printer->text("Table: " . $this->transaction->table->name . "\n");
        $printer->feed();

        // Duration Info
        if ($this->transaction->ended_at) {
            $durationMinutes = $this->transaction->started_at->diffInMinutes($this->transaction->ended_at);
        } else {
            $durationMinutes = $this->transaction->started_at->diffInMinutes(now());
        }
        $durationHours = ceil($durationMinutes / 60);
        $printer->text("Start Time: " . $this->transaction->started_at->format('H:i') . "\n");
        $printer->text("End Time: " . ($this->transaction->ended_at ? $this->transaction->ended_at->format('H:i') : now()->format('H:i')) . "\n");
        $printer->text("Duration (Rounded): " . $durationHours . " hour(s)\n");
        $printer->feed();

        // Table Charge
        $tableCharge = $this->transaction->table->hourly_rate * $durationHours;
        $printer->text("Table Rental (" . $durationHours . " × Rp " . number_format($this->transaction->table->hourly_rate, 0, ',', '.') . ")\n");
        $printer->text("Rp " . number_format($tableCharge, 0, ',', '.') . "\n");
        $printer->feed();

        // Items
        if ($this->transaction->items->count() > 0) {
            foreach ($this->transaction->items as $item) {
                $printer->text($item->quantity . " × " . $item->product->name . "\n");
                $printer->text("Rp " . number_format($item->total_price, 0, ',', '.') . "\n");
                $printer->feed();
            }
        }

        // Total, Payment, Change
        $printer->setEmphasis(true);
        $printer->text("Total: Rp " . number_format($this->transaction->total, 0, ',', '.') . "\n");
        $printer->setEmphasis(false);
        $printer->text("Payment: " . ucfirst($this->transaction->payment_method) . "\n");
        $printer->text("Received: Rp " . number_format($this->transaction->cash_received ?? 0, 0, ',', '.') . "\n");
        $printer->text("Change: Rp " . number_format($this->transaction->change_amount ?? 0, 0, ',', '.') . "\n");
        $printer->feed();

        // Footer
        $printer->setJustification(\mike42\Escpos\Printer::JUSTIFY_CENTER);
        $printer->text("Terima kasih!\n");
        $printer->text("Barang yang sudah dibeli tidak dapat ditukar/dikembalikan\n");
        $printer->feed(3);
    }
}