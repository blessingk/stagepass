<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;

#[Layout('components.layouts.admin')]
class UserForm extends Component
{
    public ?User $user = null;

    #[Rule('required|min:3|max:255')]
    public $name = '';

    #[Rule('required|email|max:255')]
    public $email = '';

    public $password = '';
    public $password_confirmation = '';
    public $is_admin = false;

    public function mount(?User $user = null)
    {
        if ($user && $user->exists) {
            $this->user = $user;
            $this->name = $user->name;
            $this->email = $user->email;
            $this->is_admin = $user->is_admin;
        }
    }

    public function save()
    {
        $rules = [
            'name' => 'required|min:3|max:255',
            'email' => 'required|email|max:255|unique:users,email' . ($this->user ? ',' . $this->user->id : ''),
            'is_admin' => 'boolean',
        ];
        if (!$this->user) {
            $rules['password'] = 'required|min:6|confirmed';
        } elseif ($this->password) {
            $rules['password'] = 'nullable|min:6|confirmed';
        }
        $validated = $this->validate($rules);

        $user = $this->user ?? new User();
        $user->name = $this->name;
        $user->email = $this->email;
        $user->is_admin = $this->is_admin;
        if ($this->password) {
            $user->password = Hash::make($this->password);
        }
        $user->save();

        session()->flash('message', $this->user ? 'User updated successfully.' : 'User created successfully.');
        $this->redirect(route('admin.users.index'));
    }

    public function render()
    {
        return view('livewire.admin.user-form');
    }
}
