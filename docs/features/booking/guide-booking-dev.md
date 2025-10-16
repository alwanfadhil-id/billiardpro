# 🎯 **PENGEMBANGAN FITUR BOOKING v1.1.0**

## 📋 **CONTEXT & INSTRUCTIONS:**

**PROJECT:** BilliardPro - Sistem Billing Billiard  
**CURRENT VERSION:** v1.0.0 (Foundation)  
**TARGET VERSION:** v1.1.0 (Booking System)  
**FRAMEWORK:** Laravel 11 + Livewire 3 + Tailwind CSS + DaisyUI

---

## 🎯 **OBJECTIVE :**
Bantu saya develop **fitur booking system** mengikuti roadmap v1.1.0 yang sudah direncanakan. Ikuti **structured approach** dan **best practices** untuk pemula.

---

## 📁 **PROJECT STRUCTURE YANG SUDAH ADA:**
```
billiardpro/
├── app/
│   ├── Models/
│   │   ├── User.php
│   │   ├── Table.php
│   │   ├── Product.php
│   │   ├── Transaction.php
│   │   └── TransactionItem.php
│   └── Livewire/
│       ├── Dashboard/TableGrid.php
│       ├── Transactions/
│       └── Reports/
├── database/migrations/
├── resources/views/
└── routes/web.php
```

---

## 🗓️ **ROADMAP V1.1.0 - BOOKING SYSTEM:**

### **PHASE 1: DATABASE & MODELS** ✅
- [x] Migration `bookings` table
- [x] Model `Booking.php` 
- [x] Update Model `Table.php` dengan relasi
- [x] Database testing

### **PHASE 2: LIVEWIRE COMPONENTS** 🚧
- [ ] **CreateBooking** - Form buat booking baru
- [ ] **BookingList** - Daftar & manage booking
- [ ] **Integration** dengan dashboard existing
- [ ] Form validation & error handling

### **PHASE 3: ROUTES & NAVIGATION** 📝
- [ ] Routes untuk booking system
- [ ] Navigation menu updates
- [ ] Integration dengan TableGrid existing

### **PHASE 4: TESTING & POLISH** 🔧
- [ ] Manual testing flow
- [ ] Bug fixes & validation
- [ ] UI/UX improvements

---

## 🛠️ **TECHNICAL CONSTRAINTS :**

### **Database Schema `bookings`:**
```php
Schema::create('bookings', function (Blueprint $table) {
    $table->id();
    $table->foreignId('table_id')->constrained()->onDelete('cascade');
    $table->string('customer_name');
    $table->string('customer_phone')->nullable();
    $table->string('customer_email')->nullable();
    $table->date('booking_date');
    $table->time('start_time');
    $table->time('end_time');
    $table->enum('status', ['confirmed', 'cancelled', 'completed', 'no_show'])->default('confirmed');
    $table->text('notes')->nullable();
    $table->enum('source', ['website', 'walk_in', 'phone'])->default('walk_in');
    $table->timestamps();
});
```

### **UI/UX Requirements:**
- Gunakan **Tailwind CSS + DaisyUI** components
- Warna status: `available` (hijau), `occupied` (merah), `maintenance` (abu), `reserved` (kuning)
- Responsive design (tablet-friendly)
- Modal forms untuk better UX

### **Business Logic:**
- Booking maksimal **7 hari** sebelumnya
- Durasi: **1-3 jam** per booking
- Grace period: **15 menit** (auto-cancel jika no show)
- Tidak boleh double booking di meja & jam yang sama

---

## 📝 **SPECIFIC REQUESTS :**

### **REQUEST 1: BUAT LIVEWIRE COMPONENT `CreateBooking`**
```markdown
**Buatkan:** Livewire component untuk form create booking
**File:** `app/Livewire/Bookings/CreateBooking.php`
**View:** `resources/views/livewire/bookings/create-booking.blade.php`

**Requirements:**
- Form dengan field: table_id, customer_name, customer_phone, booking_date, start_time, end_time, notes
- Validasi: required fields, date validation, time conflict detection
- Tampilkan available tables berdasarkan selected date/time
- Gunakan DaisyUI components (card, form-control, input, select)
- Success/error messages menggunakan alert components
```

### **REQUEST 2: BUAT LIVEWIRE COMPONENT `BookingList`**
```markdown
**Buatkan:** Livewire component untuk daftar booking
**File:** `app/Livewire/Bookings/BookingList.php`
**View:** `resources/views/livewire/bookings/booking-list.blade.php`

**Requirements:**
- Tampilkan bookings dengan pagination
- Filter by date (hari ini, besok, custom date)
- Actions: check-in, cancel, view details
- Status badges dengan warna sesuai status
- Table layout menggunakan DaisyUI table components
```

### **REQUEST 3: UPDATE DASHBOARD EXISTING**
```markdown
**Modifikasi:** `app/Livewire/Dashboard/TableGrid.php`
**Requirements:**
- Tambah status `reserved` dengan warna kuning
- Di modal table, tampilkan upcoming bookings untuk meja tersebut
- Integrasi dengan booking system (tombol "Booking" untuk available tables)
```

### **REQUEST 4: ROUTES & NAVIGATION**
```markdown
**Update:** `routes/web.php` dan navigation menu
**Requirements:**
- Route untuk `/bookings` (BookingList)
- Route untuk `/bookings/create` (CreateBooking) 
- Tambah menu "Bookings" di navigation sidebar
- Protect routes dengan auth middleware
```

---

## 🎨 **CODE STYLE GUIDELINES :**

### **Livewire Component Structure:**
```php
<?php

namespace App\Livewire\Bookings;

use Livewire\Component;
use App\Models\Table;
use App\Models\Booking;

class CreateBooking extends Component
{
    // Properties
    public $tableId;
    public $customerName;
    // ... other properties
    
    // Validation rules
    protected $rules = [
        'customerName' => 'required|string|max:255',
        // ... other rules
    ];
    
    // Methods
    public function saveBooking()
    {
        $this->validate();
        // Business logic
    }
    
    public function render()
    {
        return view('livewire.bookings.create-booking');
    }
}
```

### **Blade Template Style:**
```blade
<div class="p-6">
    <h1 class="text-2xl font-bold mb-6">Judul Halaman</h1>
    
    @if (session('success'))
        <div class="alert alert-success mb-4">
            {{ session('success') }}
        </div>
    @endif
    
    <div class="card bg-base-200">
        <div class="card-body">
            <!-- Form content using DaisyUI components -->
            <form wire:submit="saveBooking">
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Field Label</span>
                    </label>
                    <input type="text" wire:model="fieldName" class="input input-bordered">
                </div>
            </form>
        </div>
    </div>
</div>
```

---

## 🔄 **GIT COMMIT CONVENTION:**
```bash
# Untuk feature baru
git commit -m "feat: add booking creation form"

# Untuk bug fixes  
git commit -m "fix: booking time validation logic"

# Untuk improvements
git commit -m "improve: booking list UI responsiveness"
```

---

## ❗ **IMPORTANT NOTES :**

1. **Jangan break existing functionality** - billing system harus tetap jalan
2. **Follow Laravel/Livewire best practices** - validation, error handling, eager loading
3. **User-friendly error messages** - untuk pemula yang pakai system
4. **Progressive enhancement** - buat basic version dulu, enhance later
5. **Document complex logic** - berikan comments untuk bagian tricky

---

## 🎯 **EXPECTED OUTPUT**

Untuk setiap request, harap provide:
- ✅ **Complete code** dengan syntax highlighting
- ✅ **File paths** yang jelas
- ✅ **Brief explanation** untuk complex logic
- ✅ **Testing instructions** jika perlu
- ✅ **Git commit message** recommendation
