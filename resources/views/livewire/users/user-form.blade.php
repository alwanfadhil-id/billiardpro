@extends('layouts.app')

@section('header')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white">User Management</h1>
        <button onclick="toggleDarkMode()" class="btn btn-ghost">
            <span id="theme-icon">🌙</span>
        </button>
    </div>
@endsection

@section('content')
<div class="p-6">
    <div class="max-w-4xl mx-auto">
        <!-- Form Section -->
        <div class="card bg-base-100 shadow-xl mb-8">
            <div class="card-body">
                <h2 class="card-title text-lg text-gray-800 dark:text-white">
                    {{ $isEdit ? 'Edit User' : 'Add New User' }}
                </h2>
                
                <form wire:submit="{{ $isEdit ? 'update' : 'create' }}">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text text-gray-700 dark:text-gray-300">Name</span>
                            </label>
                            <input type="text" 
                                   wire:model="name" 
                                   class="input input-bordered" 
                                   placeholder="Full Name" 
                                   required />
                            @error('name') <span class="text-error text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-control">
                            <label class="label">
                                <span class="label-text text-gray-700 dark:text-gray-300">Email</span>
                            </label>
                            <input type="email" 
                                   wire:model="email" 
                                   class="input input-bordered" 
                                   placeholder="email@example.com" 
                                   required />
                            @error('email') <span class="text-error text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-control">
                            <label class="label">
                                <span class="label-text text-gray-700 dark:text-gray-300">Role</span>
                            </label>
                            <select wire:model="role" class="select select-bordered" required>
                                <option value="admin">Admin</option>
                                <option value="cashier">Cashier</option>
                            </select>
                            @error('role') <span class="text-error text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-control">
                            <label class="label">
                                <span class="label-text text-gray-700 dark:text-gray-300">
                                    {{ $isEdit && !$showPassword ? 'Change Password (optional)' : 'Password' }}
                                </span>
                            </label>
                            <input type="password" 
                                   wire:model="password" 
                                   class="input input-bordered" 
                                   {{ (!$isEdit || $showPassword) ? 'required' : '' }} 
                                   placeholder="{{ $isEdit && !$showPassword ? 'Leave blank to keep current password' : 'Password' }}" />
                            @error('password') <span class="text-error text-sm">{{ $message }}</span> @enderror
                        </div>

                        @if($isEdit)
                        <div class="form-control md:col-span-2">
                            <label class="cursor-pointer label">
                                <span class="label-text text-gray-700 dark:text-gray-300">Show password field</span>
                                <input type="checkbox" 
                                       class="toggle toggle-primary" 
                                       wire:model="showPassword" />
                            </label>
                        </div>
                        @endif
                    </div>

                    <div class="card-actions justify-end mt-6">
                        @if($isEdit)
                            <button type="button" 
                                    wire:click="cancel" 
                                    class="btn btn-ghost">Cancel</button>
                        @endif
                        <button type="submit" 
                                class="btn btn-primary">
                            {{ $isEdit ? 'Update User' : 'Create User' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Search and Users List -->
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4 gap-4">
                    <h2 class="card-title text-lg text-gray-800 dark:text-white">Users List</h2>
                    <input type="text" 
                           wire:model.live="search" 
                           placeholder="Search users..." 
                           class="input input-bordered w-full md:w-64">
                </div>

                <div class="overflow-x-auto">
                    <table class="table table-zebra">
                        <thead>
                            <tr>
                                <th class="text-gray-700 dark:text-gray-300">ID</th>
                                <th class="text-gray-700 dark:text-gray-300">Name</th>
                                <th class="text-gray-700 dark:text-gray-300">Email</th>
                                <th class="text-gray-700 dark:text-gray-300">Role</th>
                                <th class="text-gray-700 dark:text-gray-300">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                                <tr class="text-gray-800 dark:text-white">
                                    <td>{{ $user->id }}</td>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        <span class="badge {{ $user->role === 'admin' ? 'badge-primary' : 'badge-secondary' }}">
                                            {{ ucfirst($user->role) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="flex gap-2">
                                            <button wire:click="edit({{ $user->id }})" 
                                                    class="btn btn-xs btn-outline btn-info">Edit</button>
                                            <button wire:click="delete({{ $user->id }})" 
                                                    class="btn btn-xs btn-outline btn-error"
                                                    onclick="confirm('Are you sure you want to delete this user?') || event.stopImmediatePropagation()">Delete</button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-gray-500 dark:text-gray-400 py-8">
                                        No users found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-6">
                    {{ $users->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Update the theme icon based on current theme
    document.addEventListener('DOMContentLoaded', function() {
        const theme = document.documentElement.getAttribute('data-theme');
        const themeIcon = document.getElementById('theme-icon');
        if (theme === 'dark') {
            themeIcon.textContent = '☀️';
        } else {
            themeIcon.textContent = '🌙';
        }
    });
</script>
@endpush>