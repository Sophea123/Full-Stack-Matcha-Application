<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * List and filter every account.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));
        $role = trim((string) $request->query('role'));

        $users = User::query()
            ->when($search !== '', function ($query) use ($search): void {
                $term = '%'.Str::lower($search).'%';

                $query->where(function ($query) use ($term): void {
                    $query->whereRaw('lower(name) like ?', [$term])
                        ->orWhereRaw('lower(email) like ?', [$term]);
                });
            })
            ->when($role === 'admin', fn ($query) => $query->where('is_admin', true))
            ->when($role === 'customer', fn ($query) => $query->where('is_admin', false))
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'search' => $search,
            'role' => $role,
            'totalUsers' => User::query()->count(),
            'adminCount' => User::query()->where('is_admin', true)->count(),
            'customerCount' => User::query()->where('is_admin', false)->count(),
        ]);
    }

    /**
     * Show the create form.
     */
    public function create(): View
    {
        return view('admin.users.create', [
            'user' => new User,
            'isSelf' => false,
        ]);
    }

    /**
     * Store a new account.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->merge([
            'email' => Str::lower((string) $request->input('email')),
        ]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'confirmed', Password::min(8)],
            'is_admin' => ['sometimes', 'boolean'],
        ]);

        $user = User::query()->create([
            'name' => $data['name'],
            'email' => Str::lower($data['email']),
            'password' => $data['password'],
        ]);

        $user->forceFill(['is_admin' => $request->boolean('is_admin')])->save();

        return redirect()
            ->route('admin.users.index')
            ->with('status', "{$user->name} was added as a ".($user->is_admin ? 'administrator.' : 'customer.'));
    }

    /**
     * Show the edit form.
     */
    public function edit(User $user): View
    {
        return view('admin.users.edit', [
            'user' => $user,
            'isSelf' => $user->is(auth()->user()),
        ]);
    }

    /**
     * Update an account.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $request->merge([
            'email' => Str::lower((string) $request->input('email')),
        ]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'password' => ['nullable', 'string', 'confirmed', Password::min(8)],
            'is_admin' => ['sometimes', 'boolean'],
        ]);

        $makeAdmin = $request->boolean('is_admin');

        if ($user->is(auth()->user()) && ! $makeAdmin) {
            throw ValidationException::withMessages([
                'is_admin' => 'You cannot remove your own administrator access.',
            ]);
        }

        if ($user->is_admin && ! $makeAdmin && User::query()->where('is_admin', true)->count() <= 1) {
            throw ValidationException::withMessages([
                'is_admin' => 'At least one administrator account must remain.',
            ]);
        }

        $user->name = $data['name'];
        $user->email = Str::lower($data['email']);

        if (! empty($data['password'])) {
            $user->password = $data['password'];
        }

        $user->save();
        $user->forceFill(['is_admin' => $makeAdmin])->save();

        return redirect()
            ->route('admin.users.index')
            ->with('status', "{$user->name}'s account was updated.");
    }

    /**
     * Delete an account.
     */
    public function destroy(User $user): RedirectResponse
    {
        if ($user->is(auth()->user())) {
            throw ValidationException::withMessages([
                'user' => 'You cannot delete your own account while signed in.',
            ]);
        }

        if ($user->is_admin && User::query()->where('is_admin', true)->count() <= 1) {
            throw ValidationException::withMessages([
                'user' => 'At least one administrator account must remain.',
            ]);
        }

        $name = $user->name;

        $user->tokens()->delete();
        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('status', "{$name}'s account was deleted.");
    }
}
