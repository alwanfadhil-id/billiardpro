<?php

namespace App\Livewire\Users;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class UserForm extends Component
{
    use WithPagination;

    public $users;
    public $name, $email, $password, $role, $userId;
    public $isEdit = false;
    public $showPassword = false;
    public $search = '';

    protected $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|email|max:255|unique:users,email',
        'password' => 'required|min:8|confirmed',
        'role' => 'required|in:admin,cashier',
    ];

    protected $messages = [
        'email.unique' => 'The email has already been taken.',
    ];

    public function mount()
    {
        $this->resetFields();
    }

    public function render()
    {
        $usersQuery = User::query();
        
        if ($this->search) {
            $usersQuery->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%');
        }

        $this->users = $usersQuery->paginate(10);

        return view('livewire.users.user-form');
    }

    private function resetFields()
    {
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->password_confirmation = '';
        $this->role = 'cashier';
        $this->userId = '';
        $this->isEdit = false;
        $this->showPassword = false;
    }

    public function create()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'role' => 'required|in:admin,cashier',
        ]);

        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => bcrypt($this->password),
            'role' => $this->role,
        ]);

        session()->flash('message', 'User created successfully.');
        $this->resetFields();
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        $this->userId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->role = $user->role;
        $this->isEdit = true;
        $this->showPassword = true;
    }

    public function update()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $this->userId,
            'role' => 'required|in:admin,cashier',
        ];

        if ($this->password) {
            $rules['password'] = 'min:8|confirmed';
        }

        $this->validate($rules);

        $user = User::findOrFail($this->userId);
        $user->update([
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
        ]);

        if ($this->password) {
            $user->update([
                'password' => bcrypt($this->password),
            ]);
        }

        session()->flash('message', 'User updated successfully.');
        $this->resetFields();
    }

    public function delete($id)
    {
        $user = User::findOrFail($id);
        
        if ($user->id === auth()->id()) {
            session()->flash('error', 'You cannot delete your own account.');
            return;
        }

        $user->delete();
        session()->flash('message', 'User deleted successfully.');
    }

    public function cancel()
    {
        $this->resetFields();
    }
}