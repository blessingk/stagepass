<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

#[Layout('components.layouts.admin')]
class UserIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $role = '';
    public $showDeleteModal = false;
    public $showFormModal = false;
    public $editingUser = null;
    public $name = '';
    public $email = '';
    public $password = '';
    public $password_confirmation = '';
    public $is_admin = false;

    protected $listeners = ['refresh' => '$refresh'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingRole()
    {
        $this->resetPage();
    }

    public function create()
    {
        $this->reset(['name', 'email', 'password', 'password_confirmation', 'is_admin', 'editingUser']);
        $this->showFormModal = true;
    }

    public function edit(User $user)
    {
        $this->editingUser = $user;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->is_admin = $user->is_admin;
        $this->showFormModal = true;
    }

    public function save()
    {
        $rules = [
            'name' => 'required|min:3|max:255',
            'email' => 'required|email|max:255|unique:users,email' . ($this->editingUser ? ',' . $this->editingUser->id : ''),
            'is_admin' => 'boolean',
        ];

        if (!$this->editingUser) {
            $rules['password'] = ['required', 'confirmed', Password::defaults()];
        } elseif ($this->password) {
            $rules['password'] = ['nullable', 'confirmed', Password::defaults()];
        }

        $validated = $this->validate($rules);

        if ($this->editingUser) {
            $user = $this->editingUser;
            $user->name = $validated['name'];
            $user->email = $validated['email'];
            $user->is_admin = $validated['is_admin'];
            if ($this->password) {
                $user->password = Hash::make($this->password);
            }
            $user->save();
            $message = 'User updated successfully.';
        } else {
            User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'is_admin' => $validated['is_admin'],
            ]);
            $message = 'User created successfully.';
        }

        $this->showFormModal = false;
        session()->flash('message', $message);
    }

    public function confirmDelete(User $user)
    {
        if ($user->id === auth()->id()) {
            session()->flash('error', 'You cannot delete your own account.');
            return;
        }
        $this->editingUser = $user;
        $this->showDeleteModal = true;
    }

    public function delete()
    {
        if ($this->editingUser && $this->editingUser->id !== auth()->id()) {
            $this->editingUser->delete();
            session()->flash('message', 'User deleted successfully.');
        }
        $this->showDeleteModal = false;
    }

    public function render()
    {
        $users = User::query()
            ->when($this->search, function ($query) {
                $query->where(function ($query) {
                    $query->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->role !== '', function ($query) {
                $query->where('is_admin', $this->role === 'admin');
            })
            ->latest()
            ->paginate(10);

        return view('livewire.admin.user-index', [
            'users' => $users
        ]);
    }
} 