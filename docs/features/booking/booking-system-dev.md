# 📋 **REFERENSI PENGEMBANGAN FITUR BOOKING - SISTEM BILLING BILLIARD**

**Versi**: 1.0  
**Framework**: Laravel 11 + Livewire 3  
**Tujuan**: Panduan lengkap implementasi fitur booking untuk developer & AI assistant

---

## 🎯 **OVERVIEW & TUJUAN**

### **Business Need**
- Memungkinkan **booking meja di waktu mendatang** (hari ini + max 7 hari)
- Mengurangi konflik antara **walk-in** dan **reservasi**
- Meningkatkan pengalaman pelanggan dengan reservasi terstruktur

### **Use Cases**
1. **Pelanggan datang ke kasir** → booking untuk jam tertentu
2. **Telepon reservasi** → kasir input via sistem  
3. **Online booking** (future enhancement) → integrasi website

### **Batasan Fitur**
- ✅ Booking via kasir saja (untuk phase 1)
- ✅ Maksimal booking 7 hari sebelumnya
- ✅ Durasi: 1-3 jam per booking
- ✅ Grace period: 15 menit

---

## 🗃️ **DATABASE SCHEMA**

### **Tabel Baru: `bookings`**

```sql
CREATE TABLE bookings (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    table_id BIGINT NOT NULL,
    customer_name VARCHAR(255) NOT NULL,
    customer_phone VARCHAR(20) NULL,
    booking_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    status ENUM('confirmed', 'cancelled', 'completed', 'no_show') DEFAULT 'confirmed',
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (table_id) REFERENCES tables(id) ON DELETE CASCADE,
    
    INDEX idx_booking_date (booking_date),
    INDEX idx_table_date (table_id, booking_date),
    INDEX idx_status (status)
);
```

### **Modifikasi Tabel Existing: `tables`**

```sql
-- Tambah status 'reserved' ke enum existing
ALTER TABLE tables 
MODIFY COLUMN status ENUM('available', 'occupied', 'maintenance', 'reserved') DEFAULT 'available';
```

---

## 🔗 **MODEL & RELASI**

### **Model Baru: `Booking.php`**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    protected $fillable = [
        'table_id',
        'customer_name', 
        'customer_phone',
        'booking_date',
        'start_time',
        'end_time', 
        'status',
        'notes'
    ];

    protected $casts = [
        'booking_date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    // Relasi ke meja
    public function table(): BelongsTo
    {
        return $this->belongsTo(Table::class);
    }

    // Scope helpers
    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }
    
    public function scopeForDate($query, $date)
    {
        return $query->where('booking_date', $date);
    }
    
    public function scopeUpcoming($query)
    {
        return $query->where('booking_date', '>=', now()->format('Y-m-d'))
                    ->where('status', 'confirmed')
                    ->orderBy('booking_date')
                    ->orderBy('start_time');
    }

    // Business logic methods
    public function isActive(): bool
    {
        return $this->status === 'confirmed';
    }
    
    public function isOverdue(): bool
    {
        if (!$this->isActive()) return false;
        
        $bookingDateTime = \Carbon\Carbon::parse($this->booking_date->format('Y-m-d') . ' ' . $this->start_time);
        return now()->greaterThan($bookingDateTime->addMinutes(15));
    }
    
    public function markAsNoShow(): void
    {
        $this->update(['status' => 'no_show']);
        $this->table->update(['status' => 'available']);
    }
    
    public function completeBooking(): void
    {
        $this->update(['status' => 'completed']);
    }
    
    public function cancelBooking(): void
    {
        $this->update(['status' => 'cancelled']);
        $this->table->update(['status' => 'available']);
    }
}
```

### **Modifikasi Model: `Table.php`**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Table extends Model
{
    // ... existing code ...
    
    // Tambah relasi bookings
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    // Cek ketersediaan untuk booking
    public function isAvailableForBooking(string $date, string $startTime, string $endTime, ?int $excludeBookingId = null): bool
    {
        $conflictingBooking = $this->bookings()
            ->confirmed()
            ->forDate($date)
            ->where(function ($query) use ($startTime, $endTime) {
                $query->whereBetween('start_time', [$startTime, $endTime])
                      ->orWhereBetween('end_time', [$startTime, $endTime])
                      ->orWhere(function ($q) use ($startTime, $endTime) {
                          $q->where('start_time', '<=', $startTime)
                            ->where('end_time', '>=', $endTime);
                      });
            });
        
        if ($excludeBookingId) {
            $conflictingBooking->where('id', '!=', $excludeBookingId);
        }
        
        return $conflictingBooking->doesntExist();
    }
    
    // Cek apakah meja bisa dibooking (available & tidak maintenance)
    public function canBeBooked(): bool
    {
        return in_array($this->status, ['available', 'reserved']);
    }
}
```

---

## 🎨 **LIVEWIRE COMPONENTS**

### **1. BookingList - Menampilkan Daftar Booking**

**File:** `app/Livewire/Bookings/BookingList.php`

```php
<?php

namespace App\Livewire\Bookings;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Booking;

class BookingList extends Component
{
    use WithPagination;
    
    public $selectedDate;
    public $viewMode = 'list'; // 'list' or 'calendar'
    
    public function mount()
    {
        $this->selectedDate = now()->format('Y-m-d');
    }
    
    public function getBookingsProperty()
    {
        return Booking::with('table')
            ->upcoming()
            ->when($this->selectedDate, function ($query) {
                $query->forDate($this->selectedDate);
            })
            ->orderBy('start_time')
            ->paginate(20);
    }
    
    public function cancelBooking($bookingId)
    {
        $booking = Booking::findOrFail($bookingId);
        
        if ($booking->isActive()) {
            $booking->cancelBooking();
            session()->flash('message', 'Booking berhasil dibatalkan.');
        }
    }
    
    public function checkIn($bookingId)
    {
        $booking = Booking::findOrFail($bookingId);
        
        if ($booking->isActive()) {
            // Create transaction from booking
            $transaction = $this->createTransactionFromBooking($booking);
            $booking->completeBooking();
            
            return redirect()->route('transactions.payment', $transaction->id);
        }
    }
    
    private function createTransactionFromBooking(Booking $booking)
    {
        // Implementation similar to StartSession component
        // ... existing transaction creation logic ...
    }
    
    public function render()
    {
        return view('livewire.bookings.booking-list', [
            'bookings' => $this->bookings
        ]);
    }
}
```

### **2. CreateBooking - Form Buat Booking Baru**

**File:** `app/Livewire/Bookings/CreateBooking.php`

```php
<?php

namespace App\Livewire\Bookings;

use Livewire\Component;
use App\Models\Table;
use App\Models\Booking;
use Carbon\Carbon;

class CreateBooking extends Component
{
    public $tableId;
    public $customerName;
    public $customerPhone;
    public $bookingDate;
    public $startTime;
    public $endTime;
    public $notes;
    
    public $availableTables;
    public $availableTimeSlots = [];
    
    protected $rules = [
        'tableId' => 'required|exists:tables,id',
        'customerName' => 'required|string|max:255',
        'customerPhone' => 'nullable|string|max:20',
        'bookingDate' => 'required|date|after_or_equal:today|before_or_equal:'.null,
        'startTime' => 'required|date_format:H:i',
        'endTime' => 'required|date_format:H:i|after:start_time',
        'notes' => 'nullable|string|max:500'
    ];
    
    public function mount()
    {
        $this->bookingDate = now()->format('Y-m-d');
        $this->loadAvailableTables();
        $this->generateTimeSlots();
        
        // Set max booking date (7 days from now)
        $this->rules['bookingDate'] = 'required|date|after_or_equal:today|before_or_equal:'.now()->addDays(7)->format('Y-m-d');
    }
    
    public function loadAvailableTables()
    {
        $this->availableTables = Table::where('status', '!=', 'maintenance')
            ->orderBy('name')
            ->get();
    }
    
    public function generateTimeSlots()
    {
        $slots = [];
        $start = Carbon::createFromTime(9, 0); // Buka jam 9 pagi
        $end = Carbon::createFromTime(23, 0);  // Tutup jam 11 malam
        
        while ($start <= $end) {
            $slots[] = $start->format('H:i');
            $start->addHour();
        }
        
        $this->availableTimeSlots = $slots;
    }
    
    public function updatedBookingDate()
    {
        $this->validateBooking();
    }
    
    public function updatedStartTime()
    {
        $this->validateBooking();
        
        // Auto-set end time (1 hour after start)
        if ($this->startTime) {
            $start = Carbon::createFromFormat('H:i', $this->startTime);
            $this->endTime = $start->addHour()->format('H:i');
        }
    }
    
    public function validateBooking()
    {
        $this->validateOnly('tableId');
        $this->validateOnly('bookingDate');
        $this->validateOnly('startTime');
        
        if ($this->tableId && $this->bookingDate && $this->startTime && $this->endTime) {
            $table = Table::find($this->tableId);
            
            if (!$table->isAvailableForBooking($this->bookingDate, $this->startTime, $this->endTime)) {
                $this->addError('timeConflict', 'Meja sudah dibooking pada jam tersebut. Silakan pilih jam lain.');
                return false;
            }
        }
        
        return true;
    }
    
    public function saveBooking()
    {
        $this->validate();
        
        if (!$this->validateBooking()) {
            return;
        }
        
        try {
            \DB::transaction(function () {
                $booking = Booking::create([
                    'table_id' => $this->tableId,
                    'customer_name' => $this->customerName,
                    'customer_phone' => $this->customerPhone,
                    'booking_date' => $this->bookingDate,
                    'start_time' => $this->startTime,
                    'end_time' => $this->endTime,
                    'notes' => $this->notes,
                    'status' => 'confirmed'
                ]);
                
                // Update table status to reserved
                $table = Table::find($this->tableId);
                $table->update(['status' => 'reserved']);
            });
            
            session()->flash('message', 'Booking berhasil dibuat!');
            $this->resetForm();
            
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal membuat booking: ' . $e->getMessage());
        }
    }
    
    private function resetForm()
    {
        $this->reset(['tableId', 'customerName', 'customerPhone', 'startTime', 'endTime', 'notes']);
        $this->bookingDate = now()->format('Y-m-d');
    }
    
    public function render()
    {
        return view('livewire.bookings.create-booking');
    }
}
```

---

## 📱 **VIEW TEMPLATES**

### **1. Booking List View**

**File:** `resources/views/livewire/bookings/booking-list.blade.php`

```blade
<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Manajemen Booking</h1>
        <a href="{{ route('bookings.create') }}" class="btn btn-primary">
            + Booking Baru
        </a>
    </div>

    <!-- Date Filter -->
    <div class="bg-base-200 p-4 rounded-lg mb-6">
        <div class="flex items-center gap-4">
            <label class="font-semibold">Filter Tanggal:</label>
            <input type="date" wire:model.live="selectedDate" class="input input-bordered">
            <div class="flex gap-2">
                <button wire:click="$set('selectedDate', '{{ now()->format('Y-m-d') }}')" 
                        class="btn btn-sm {{ $selectedDate === now()->format('Y-m-d') ? 'btn-primary' : 'btn-ghost' }}">
                    Hari Ini
                </button>
                <button wire:click="$set('selectedDate', '{{ now()->addDay()->format('Y-m-d') }}')" 
                        class="btn btn-sm {{ $selectedDate === now()->addDay()->format('Y-m-d') ? 'btn-primary' : 'btn-ghost' }}">
                    Besok
                </button>
            </div>
        </div>
    </div>

    <!-- Bookings Table -->
    <div class="bg-base-100 rounded-lg shadow">
        <div class="overflow-x-auto">
            <table class="table table-zebra">
                <thead>
                    <tr>
                        <th>Meja</th>
                        <th>Pelanggan</th>
                        <th>Tanggal</th>
                        <th>Jam</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bookings as $booking)
                        <tr>
                            <td class="font-semibold">{{ $booking->table->name }}</td>
                            <td>
                                <div>{{ $booking->customer_name }}</div>
                                @if($booking->customer_phone)
                                    <div class="text-sm text-gray-500">{{ $booking->customer_phone }}</div>
                                @endif
                            </td>
                            <td>{{ $booking->booking_date->format('d/m/Y') }}</td>
                            <td>{{ \Carbon\Carbon::parse($booking->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($booking->end_time)->format('H:i') }}</td>
                            <td>
                                <span class="badge 
                                    {{ $booking->status === 'confirmed' ? 'badge-warning' : '' }}
                                    {{ $booking->status === 'completed' ? 'badge-success' : '' }}
                                    {{ $booking->status === 'cancelled' ? 'badge-error' : '' }}
                                    {{ $booking->status === 'no_show' ? 'badge-neutral' : '' }}">
                                    {{ ucfirst($booking->status) }}
                                </span>
                                @if($booking->isOverdue())
                                    <span class="badge badge-error badge-sm">Terlambat</span>
                                @endif
                            </td>
                            <td>
                                <div class="flex gap-2">
                                    @if($booking->status === 'confirmed')
                                        <button wire:click="checkIn({{ $booking->id }})" 
                                                wire:confirm="Check-in pelanggan untuk booking ini?"
                                                class="btn btn-success btn-sm">
                                            Check-in
                                        </button>
                                        <button wire:click="cancelBooking({{ $booking->id }})" 
                                                wire:confirm="Batalkan booking ini?"
                                                class="btn btn-error btn-sm">
                                            Batalkan
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-8 text-gray-500">
                                Tidak ada booking untuk tanggal yang dipilih.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="p-4">
            {{ $bookings->links() }}
        </div>
    </div>
</div>
```

### **2. Create Booking Form**

**File:** `resources/views/livewire/bookings/create-booking.blade.php`

```blade
<div class="p-6 max-w-4xl mx-auto">
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('bookings.index') }}" class="btn btn-ghost btn-circle">
            ←
        </a>
        <h1 class="text-2xl font-bold">Buat Booking Baru</h1>
    </div>

    @if (session()->has('message'))
        <div class="alert alert-success mb-6">
            <span>{{ session('message') }}</span>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-error mb-6">
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <form wire:submit="saveBooking" class="space-y-6">
        <!-- Customer Information -->
        <div class="card bg-base-200 shadow">
            <div class="card-body">
                <h2 class="card-title">Informasi Pelanggan</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-semibold">Nama Pelanggan *</span>
                        </label>
                        <input type="text" wire:model="customerName" class="input input-bordered" 
                               placeholder="Masukkan nama pelanggan">
                        @error('customerName') <span class="text-error text-sm">{{ $message }}</span> @enderror
                    </div>
                    
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Nomor Telepon</span>
                        </label>
                        <input type="text" wire:model="customerPhone" class="input input-bordered" 
                               placeholder="Contoh: 08123456789">
                        @error('customerPhone') <span class="text-error text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Booking Details -->
        <div class="card bg-base-200 shadow">
            <div class="card-body">
                <h2 class="card-title">Detail Booking</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Table Selection -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-semibold">Pilih Meja *</span>
                        </label>
                        <select wire:model.live="tableId" class="select select-bordered">
                            <option value="">-- Pilih Meja --</option>
                            @foreach($availableTables as $table)
                                <option value="{{ $table->id }}" 
                                        {{ $table->status === 'reserved' ? 'disabled' : '' }}>
                                    {{ $table->name }} - Rp {{ number_format($table->hourly_rate) }}/jam
                                    {{ $table->status === 'reserved' ? ' (Dipesan)' : '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('tableId') <span class="text-error text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Booking Date -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-semibold">Tanggal Booking *</span>
                        </label>
                        <input type="date" wire:model.live="bookingDate" 
                               min="{{ now()->format('Y-m-d') }}" 
                               max="{{ now()->addDays(7)->format('Y-m-d') }}"
                               class="input input-bordered">
                        @error('bookingDate') <span class="text-error text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Start Time -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-semibold">Jam Mulai *</span>
                        </label>
                        <select wire:model.live="startTime" class="select select-bordered">
                            <option value="">-- Pilih Jam --</option>
                            @foreach($availableTimeSlots as $slot)
                                <option value="{{ $slot }}">{{ $slot }}</option>
                            @endforeach
                        </select>
                        @error('startTime') <span class="text-error text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- End Time -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-semibold">Jam Selesai *</span>
                        </label>
                        <select wire:model="endTime" class="select select-bordered">
                            <option value="">-- Pilih Jam --</option>
                            @foreach($availableTimeSlots as $slot)
                                <option value="{{ $slot }}" 
                                        {{ $this->startTime && $slot <= $this->startTime ? 'disabled' : '' }}>
                                    {{ $slot }}
                                </option>
                            @endforeach
                        </select>
                        @error('endTime') <span class="text-error text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- Notes -->
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Catatan Khusus</span>
                    </label>
                    <textarea wire:model="notes" class="textarea textarea-bordered" 
                              placeholder="Contoh: Pelanggan VIP, request meja dekat AC, dll."></textarea>
                    @error('notes') <span class="text-error text-sm">{{ $message }}</span> @enderror
                </div>

                <!-- Time Conflict Warning -->
                @error('timeConflict')
                    <div class="alert alert-warning mt-4">
                        <span>{{ $message }}</span>
                    </div>
                @enderror
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex justify-end gap-4">
            <a href="{{ route('bookings.index') }}" class="btn btn-ghost">Batal</a>
            <button type="submit" class="btn btn-primary" 
                    wire:loading.attr="disabled"
                    wire:target="saveBooking">
                <span wire:loading.remove wire:target="saveBooking">Simpan Booking</span>
                <span wire:loading wire:target="saveBooking" class="loading loading-spinner"></span>
                Simpan Booking
            </button>
        </div>
    </form>
</div>
```

---

## 🛣️ **ROUTES**

**File:** `routes/web.php`

```php
// Tambah route booking
Route::middleware(['auth', 'verified'])->group(function () {
    // ... existing routes ...
    
    // Booking Routes
    Route::prefix('bookings')->group(function () {
        Route::get('/', \App\Livewire\Bookings\BookingList::class)->name('bookings.index');
        Route::get('/create', \App\Livewire\Bookings\CreateBooking::class)->name('bookings.create');
    });
});
```

---

## ⚙️ **BACKGROUND PROCESSING**

### **Auto-Cancel Overdue Bookings**

**File:** `app/Console/Commands/CancelOverdueBookings.php`

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Booking;

class CancelOverdueBookings extends Command
{
    protected $signature = 'bookings:cancel-overdue';
    protected $description = 'Automatically cancel bookings that are 15 minutes overdue';

    public function handle()
    {
        $overdueBookings = Booking::with('table')
            ->confirmed()
            ->where('booking_date', today())
            ->get()
            ->filter(function ($booking) {
                return $booking->isOverdue();
            });

        foreach ($overdueBookings as $booking) {
            $booking->markAsNoShow();
            $this->info("Cancelled booking #{$booking->id} for table {$booking->table->name}");
        }

        $this->info("Cancelled {$overdueBookings->count()} overdue bookings.");
    }
}
```

### **Schedule Registration**

**File:** `app/Console/Kernel.php`

```php
protected function schedule(Schedule $schedule)
{
    // ... existing schedule ...
    
    // Run every minute to check for overdue bookings
    $schedule->command('bookings:cancel-overdue')->everyMinute();
}
```

---

## 🎨 **UI/UX ENHANCEMENTS**

### **Modifikasi Dashboard Existing**

**File:** `app/Livewire/Dashboard/TableGrid.php`

```php
// Tambah method untuk handle booking status
public function getTableStatusColor($table)
{
    return match($table->status) {
        'available' => 'bg-green-500',
        'occupied' => 'bg-red-500', 
        'maintenance' => 'bg-gray-500',
        'reserved' => 'bg-yellow-500',
        default => 'bg-gray-300'
    };
}

// Tambah info booking di modal meja
public function getTableBookings($tableId)
{
    return Booking::with('table')
        ->confirmed()
        ->where('table_id', $tableId)
        ->where('booking_date', '>=', now()->format('Y-m-d'))
        ->orderBy('booking_date')
        ->orderBy('start_time')
        ->get();
}
```

### **View Modifications**

```blade
<!-- Di resources/views/livewire/dashboard/table-grid.blade.php -->

<!-- Tambah badge reserved -->
@if($table->status === 'reserved')
    <div class="badge badge-warning absolute top-2 right-2">Dipesan</div>
@endif

<!-- Tambah section booking info di modal -->
@if($table->status === 'reserved' || $table->status === 'available')
    <div class="mt-4">
        <h4 class="font-semibold mb-2">Booking Mendatang:</h4>
        @php $bookings = $this->getTableBookings($table->id); @endphp
        @forelse($bookings as $booking)
            <div class="text-sm p-2 bg-base-200 rounded mb-1">
                <div>{{ $booking->customer_name }}</div>
                <div class="text-gray-600">
                    {{ $booking->booking_date->format('d/m') }} • 
                    {{ \Carbon\Carbon::parse($booking->start_time)->format('H:i') }} - 
                    {{ \Carbon\Carbon::parse($booking->end_time)->format('H:i') }}
                </div>
            </div>
        @empty
            <div class="text-sm text-gray-500">Tidak ada booking</div>
        @endforelse
    </div>
@endif
```

---

## 🧪 **TESTING**

### **Feature Test Examples**

**File:** `tests/Feature/BookingTest.php`

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Table;
use App\Models\Booking;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_booking()
    {
        $table = Table::factory()->create(['status' => 'available']);
        
        $response = $this->post('/bookings', [
            'table_id' => $table->id,
            'customer_name' => 'John Doe',
            'customer_phone' => '08123456789',
            'booking_date' => now()->addDay()->format('Y-m-d'),
            'start_time' => '19:00',
            'end_time' => '21:00',
            'notes' => 'Test booking'
        ]);
        
        $response->assertRedirect();
        $this->assertDatabaseHas('bookings', [
            'customer_name' => 'John Doe',
            'status' => 'confirmed'
        ]);
        $this->assertEquals('reserved', $table->fresh()->status);
    }
    
    public function test_prevent_double_booking()
    {
        $table = Table::factory()->create();
        
        // Booking pertama
        Booking::create([
            'table_id' => $table->id,
            'customer_name' => 'First Customer',
            'booking_date' => '2024-01-15',
            'start_time' => '19:00',
            'end_time' => '21:00',
            'status' => 'confirmed'
        ]);
        
        // Booking kedua di waktu yang sama
        $response = $this->post('/bookings', [
            'table_id' => $table->id,
            'customer_name' => 'Second Customer',
            'booking_date' => '2024-01-15',
            'start_time' => '20:00',
            'end_time' => '22:00'
        ]);
        
        $response->assertSessionHasErrors('timeConflict');
    }
    
    public function test_auto_cancel_overdue_bookings()
    {
        $table = Table::factory()->create();
        $booking = Booking::create([
            'table_id' => $table->id,
            'customer_name' => 'Late Customer',
            'booking_date' => now()->format('Y-m-d'),
            'start_time' => now()->subMinutes(20)->format('H:i'), // 20 minutes ago
            'end_time' => now()->addHour()->format('H:i'),
            'status' => 'confirmed'
        ]);
        
        $this->artisan('bookings:cancel-overdue');
        
        $this->assertEquals('no_show', $booking->fresh()->status);
        $this->assertEquals('available', $table->fresh()->status);
    }
}
```

---

## 🚀 **DEPLOYMENT CHECKLIST**

### **Phase 1: Database & Models**
- [ ] Create `bookings` migration
- [ ] Update `tables` enum status
- [ ] Create `Booking` model
- [ ] Update `Table` model dengan relasi & methods

### **Phase 2: Livewire Components**  
- [ ] Create `BookingList` component
- [ ] Create `CreateBooking` component
- [ ] Buat view templates

### **Phase 3: Routes & Integration**
- [ ] Register routes
- [ ] Integrasi dengan dashboard existing
- [ ] Update TableGrid untuk handle status reserved

### **Phase 4: Background Processing**
- [ ] Create cancel-overdue command
- [ ] Register scheduler
- [ ] Test auto-cancel functionality

### **Phase 5: Testing & Polish**
- [ ] Write comprehensive tests
- [ ] UI/UX refinement
- [ ] Performance testing

---

## 📞 **SUPPORT & TROUBLESHOOTING**

### **Common Issues:**
1. **Time Zone Issues** → Pastikan `config/app.php` timezone sesuai
2. **Race Conditions** → Gunakan database transactions untuk operasi kritikal
3. **Performance** → Tambah index pada kolom yang sering di-query

### **Debug Tips:**
```php
// Log booking activities
\Log::info('Booking created', ['booking_id' => $booking->id, 'table' => $table->name]);

// Debug time calculations
dd([
    'now' => now(),
    'booking_time' => $bookingDateTime,
    'is_overdue' => $booking->isOverdue()
]);
```

---

File referensi ini memberikan **panduan lengkap** untuk implementasi fitur booking dari A sampai Z. Setiap komponen sudah dirancang untuk terintegrasi smooth dengan sistem existing. 🎯