<?php

namespace App\Livewire\Dashboard;

use App\Models\Table;
use Livewire\Component;

class TableGrid extends Component
{
    public $tables;
    public $search = '';
    public $filterStatus = 'all';

    protected $queryString = ['search', 'filterStatus'];

    public function mount()
    {
        $this->loadTables();
    }

    public function loadTables()
    {
        $query = Table::with(['transactions' => function($query) {
            $query->where('status', 'ongoing');
        }]);
        
        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }
        
        if ($this->filterStatus !== 'all') {
            $query->where('status', $this->filterStatus);
        }
        
        $this->tables = $query->get();
    }

    public function updated($property)
    {
        if ($property === 'search' || $property === 'filterStatus') {
            $this->loadTables();
        }
    }

    public function handleTableClick($tableId)
    {
        // This would eventually handle the business logic
        // For now, we'll just reload the tables to reflect any changes
        $this->loadTables();
    }

    public function render()
    {
        return view('livewire.dashboard.table-grid');
    }
}