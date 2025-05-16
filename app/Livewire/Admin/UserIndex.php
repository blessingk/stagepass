<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.admin')]
class UserIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $role = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingRole()
    {
        $this->resetPage();
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
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.admin.user-index', [
            'users' => $users
        ]);
    }
} 