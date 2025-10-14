<?php

namespace App\Livewire\Tables;

use App\Models\Table;
use Livewire\Component;

class TableForm extends Component
{
    public $tables;
    public $search = '';
    public $filterStatus = 'all';
    public $name;
    public $type = 'biasa';
    public $hourly_rate;
    public $status = 'available';
    public $editingTableId = null;
    public $showCreateForm = false;

    protected $rules = [
        'name' => 'required|string|max:100',
        'type' => 'required|in:biasa,premium,vip',
        'hourly_rate' => 'required|numeric|min:0',
        'status' => 'required|in:available,occupied,maintenance',
    ];

    public function mount()
    {
        $this->loadTables();
    }

    public function render()
    {
        return view('livewire.tables.table-form');
    }

    private function loadTables()
    {
        $query = Table::query();

        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }

        if ($this->filterStatus !== 'all') {
            $query->where('status', $this->filterStatus);
        }

        $this->tables = $query->orderBy('name')->get();
    }

    public function updatingSearch()
    {
        $this->loadTables();
    }

    public function updatingFilterStatus()
    {
        $this->loadTables();
    }

    public function create()
    {
        $this->resetForm();
        $this->showCreateForm = true;
    }

    public function store()
    {
        $this->validate();

        Table::create([
            'name' => $this->name,
            'type' => $this->type,
            'hourly_rate' => $this->hourly_rate,
            'status' => $this->status,
        ]);

        $this->showCreateForm = false;
        $this->loadTables();
        $this->resetForm();
    }

    public function edit($id)
    {
        $table = Table::findOrFail($id);
        $this->editingTableId = $id;
        $this->name = $table->name;
        $this->type = $table->type;
        $this->hourly_rate = $table->hourly_rate;
        $this->status = $table->status;
    }

    public function update()
    {
        $this->validate();

        $table = Table::findOrFail($this->editingTableId);
        $table->update([
            'name' => $this->name,
            'type' => $this->type,
            'hourly_rate' => $this->hourly_rate,
            'status' => $this->status,
        ]);

        $this->resetForm();
        $this->loadTables();
    }

    public function delete($id)
    {
        $table = Table::findOrFail($id);
        $table->delete();

        $this->loadTables();
    }

    public function cancel()
    {
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->name = '';
        $this->type = 'biasa';
        $this->hourly_rate = '';
        $this->status = 'available';
        $this->editingTableId = null;
        $this->showCreateForm = false;
    }
}
