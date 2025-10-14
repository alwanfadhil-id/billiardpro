<?php

namespace App\Livewire\Transactions;

use App\Models\Transaction;
use Livewire\Component;
use mike42\Escpos\Printer;
use mike42\Escpos\PrintConnectors\NetworkPrintConnector;

class ReceiptPrint extends Component
{
    public $transactionId;
    public $transaction;

    public function mount($transactionId = null)
    {
        $this->transactionId = $transactionId;
        if ($this->transactionId) {
            $this->transaction = Transaction::with(['table', 'items.product', 'user'])->find($this->transactionId);
            if (!$this->transaction) {
                session()->flash('error', 'Transaction not found.');
                // Redirect will be handled in render method
            }
        }
    }

    public function printReceipt()
    {
        // Check if thermal printing is enabled
        if (config('app.printer_enabled', false)) {
            $this->printToThermal();
        } else {
            // Fallback to browser print
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

            // Create network connector
            $connector = new NetworkPrintConnector($printerIp, $printerPort);
            $printer = new Printer($connector);

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
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->setTextSize(2, 2); // Larger text
        $printer->text("BILLIARDPRO\n");
        $printer->setTextSize(1, 1); // Normal text
        $printer->text("Jl. Contoh Alamat No. 123\n");
        $printer->text("Telp: (021) 12345678\n");
        $printer->feed();

        // Receipt Info
        $printer->setJustification(Printer::JUSTIFY_LEFT);
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
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->text("Terima kasih!\n");
        $printer->text("Barang yang sudah dibeli tidak dapat ditukar/dikembalikan\n");
        $printer->feed(3);
    }

    public function render()
    {
        if (!$this->transaction) {
            // Redirect to dashboard if transaction not found
            return redirect()->route('dashboard');
        }
        
        return view('livewire.transactions.receipt-print', [
            'table' => $this->transaction->table,
            'items' => $this->transaction->items()->with('product')->get()
        ]);
    }
}