<?php

namespace App\Livewire\Users;

use App\Models\User;
use Livewire\Component;
use Illuminate\Support\Facades\Hash;

class UserList extends Component
{
    public $users;
    public $search = '';
    public $filterRole = 'all';
    public $name;
    public $email;
    public $password;
    public $role = 'cashier';
    public $editingUserId = null;
    public $showCreateForm = false;

    protected $rules = [
        'name' => 'required|string|max:100',
        'email' => 'required|email|max:255|unique:users,email',
        'role' => 'required|in:admin,cashier',
        'password' => 'nullable|string|min:8|confirmed',
    ];

    public function mount()
    {
        $this->loadUsers();
    }

    public function render()
    {
        return view('livewire.users.user-list');
    }

    private function loadUsers()
    {
        $query = User::query();

        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%');
        }

        if ($this->filterRole !== 'all') {
            $query->where('role', $this->filterRole);
        }

        $this->users = $query->orderBy('name')->get();
    }

    public function updatingSearch()
    {
        $this->loadUsers();
    }

    public function updatingFilterRole()
    {
        $this->loadUsers();
    }

    public function create()
    {
        $this->resetForm();
        $this->showCreateForm = true;
    }

    public function store()
    {
        $this->validate();

        $userData = [
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
        ];

        if ($this->password) {
            $userData['password'] = Hash::make($this->password);
        }

        User::create($userData);

        $this->showCreateForm = false;
        $this->loadUsers();
        $this->resetForm();
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        $this->editingUserId = $id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->role = $user->role;
        $this->password = null;  // Don't show existing password for security
    }

    public function update()
    {
        $user = User::findOrFail($this->editingUserId);
        
        $rules = [
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'role' => 'required|in:admin,cashier',
            'password' => 'nullable|string|min:8|confirmed',
        ];
        
        $this->validate($rules);

        $userData = [
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
        ];

        if ($this->password) {
            $userData['password'] = Hash::make($this->password);
        }

        $user->update($userData);

        $this->resetForm();
        $this->loadUsers();
    }

    public function delete($id)
    {
        $user = User::findOrFail($id);
        
        // Prevent deletion of the current user
        if ($user->id === auth()->id()) {
            session()->flash('error', 'Tidak bisa menghapus akun sendiri.');
            return;
        }
        
        $user->delete();

        $this->loadUsers();
    }

    public function cancel()
    {
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->role = 'cashier';
        $this->editingUserId = null;
        $this->showCreateForm = false;
    }
}
