<?php

namespace App\Http\Livewire\Tables;

use Livewire\Component;
use App\Models\Table;
use Livewire\WithPagination;

class TableForm extends Component
{
    use WithPagination;

    public $tableId;
    public $name;
    public $hourlyRate;
    public $status = 'available';

    protected $rules = [
        'name' => 'required|string|max:100|unique:tables,name',
        'hourlyRate' => 'required|numeric|min:0',
        'status' => 'required|in:available,occupied,maintenance',
    ];

    public function render()
    {
        $tables = Table::paginate(10);
        return view('livewire.tables.table-form', [
            'tables' => $tables
        ])->layout('components.layouts.app');
    }

    public function create()
    {
        $this->resetForm();
        return view('livewire.tables.table-create')->layout('components.layouts.app');
    }

    public function edit($id)
    {
        $table = Table::findOrFail($id);
        $this->tableId = $table->id;
        $this->name = $table->name;
        $this->hourlyRate = $table->hourly_rate;
        $this->status = $table->status;
        
        return view('livewire.tables.table-edit', [
            'table' => $table
        ])->layout('components.layouts.app');
    }

    public function save()
    {
        $this->validate();

        if ($this->tableId) {
            $table = Table::findOrFail($this->tableId);
            $table->update([
                'name' => $this->name,
                'hourly_rate' => $this->hourlyRate,
                'status' => $this->status,
            ]);
        } else {
            Table::create([
                'name' => $this->name,
                'hourly_rate' => $this->hourlyRate,
                'status' => $this->status,
            ]);
        }

        session()->flash('message', $this->tableId ? 'Table updated successfully.' : 'Table created successfully.');
        $this->resetForm();
    }

    public function delete($id)
    {
        $table = Table::findOrFail($id);
        $table->delete();
        session()->flash('message', 'Table deleted successfully.');
    }

    private function resetForm()
    {
        $this->tableId = null;
        $this->name = '';
        $this->hourlyRate = '';
        $this->status = 'available';
    }
}