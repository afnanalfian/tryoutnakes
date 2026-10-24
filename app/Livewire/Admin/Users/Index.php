<?php

namespace App\Livewire\Admin\Users;

use App\Livewire\Concerns\Paginates;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class Index extends Component
{
    use Paginates;

    #[Url(as: 'q')]
    public string $search = '';
    #[Url]
    public string $role = '';
    #[Url]
    public string $status = '';

    public ?int $editingId = null;
    public array $form = ['name' => '', 'email' => '', 'phone' => '', 'role' => 'user', 'is_active' => true, 'password' => ''];
    public ?string $generatedPassword = null;

    public function updating($name): void
    {
        if (in_array($name, ['search', 'role', 'status'], true)) {
            $this->resetPage();
        }
    }

    public function create(): void
    {
        $this->editingId = null;
        $this->form = ['name' => '', 'email' => '', 'phone' => '', 'role' => 'user', 'is_active' => true, 'password' => ''];
        $this->resetValidation();
        $this->dispatch('open-modal', 'user-form');
    }

    public function edit(int $id): void
    {
        $u = User::query()->findOrFail($id);
        $this->editingId = $u->id;
        $this->form = ['name' => $u->name, 'email' => $u->email, 'phone' => (string) $u->phone, 'role' => $u->role, 'is_active' => $u->is_active, 'password' => ''];
        $this->resetValidation();
        $this->dispatch('open-modal', 'user-form');
    }

    public function save(): void
    {
        $this->form['email'] = strtolower(trim($this->form['email']));
        $this->validate([
            'form.name' => 'required|string|max:100',
            'form.email' => ['required', 'email', Rule::unique('users', 'email')->ignore($this->editingId)],
            'form.phone' => 'nullable|string|max:20',
            'form.role' => 'required|in:admin,user',
            'form.password' => ($this->editingId ? 'nullable' : 'required').'|string|min:8',
        ]);

        if ($this->editingId === auth()->id() && ($this->form['role'] !== 'admin' || ! $this->form['is_active'])) {
            $this->addError('form.role', 'Kamu tidak bisa menurunkan peran / menonaktifkan akunmu sendiri.');

            return;
        }

        $data = [
            'name' => $this->form['name'], 'email' => $this->form['email'], 'phone' => normalize_phone($this->form['phone']),
            'role' => $this->form['role'], 'is_active' => (bool) $this->form['is_active'],
        ];
        if ($this->form['password']) {
            $data['password'] = $this->form['password'];
        }

        $user = User::query()->updateOrCreate(['id' => $this->editingId], $data);
        if (! $this->editingId) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }
        $this->dispatch('close-modal', 'user-form');
        $this->dispatch('toast', type: 'success', message: 'Pengguna disimpan.');
    }

    public function toggleActive(int $id): void
    {
        abort_if($id === auth()->id(), 403);
        $u = User::query()->findOrFail($id);
        $u->update(['is_active' => ! $u->is_active]);
        $this->dispatch('toast', type: 'success', message: $u->is_active ? 'Akun diaktifkan.' : 'Akun dinonaktifkan.');
    }

    public function resetPassword(int $id): void
    {
        $u = User::query()->findOrFail($id);
        $pass = Str::password(10, symbols: false);
        $u->update(['password' => $pass]);
        $this->generatedPassword = $u->email.' → '.$pass;
        $this->dispatch('open-modal', 'password-result');
    }

    public function delete(int $id): void
    {
        abort_if($id === auth()->id(), 403);
        User::query()->whereKey($id)->delete();
        $this->dispatch('toast', type: 'success', message: 'Pengguna dihapus.');
    }

    public function render()
    {
        $users = User::query()
            ->when($this->search, fn ($q) => $q->where(fn ($w) => $w->where('name', 'like', "%{$this->search}%")->orWhere('email', 'like', "%{$this->search}%")->orWhere('phone', 'like', "%{$this->search}%")))
            ->when($this->role, fn ($q) => $q->where('role', $this->role))
            ->when($this->status !== '', fn ($q) => $q->where('is_active', $this->status === 'active'))
            ->withCount(['entitlements', 'attempts'])
            ->latest()->paginate(15);

        return view('livewire.admin.users.index', compact('users'))->title('Pengguna');
    }
}
