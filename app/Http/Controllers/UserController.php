<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Display a listing of the users.
     */
    public function index(Request $request)
    {
        $this->authorize('view-users');
        
        $perPage = $request->input('perPage', 10);
        $search = $request->input('search', '');
        $role = $request->input('role');
        
        $query = User::query()
            ->with('roles')
            ->when($search, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('job_title', 'like', "%{$search}%");
                });
            })
            ->when($role, function ($query, $role) {
                $query->whereHas('roles', function ($query) use ($role) {
                    $query->where('name', $role);
                });
            });
        
        $users = $query->orderBy('name')->paginate($perPage)->withQueryString();
        
        return Inertia::render('Users/Index', [
            'users' => $users,
            'roles' => Role::all()->pluck('name'),
            'filters' => [
                'search' => $search,
                'role' => $role,
                'perPage' => $perPage,
            ],
            'can' => [
                'create' => $request->user()->can('create-users'),
                'edit' => $request->user()->can('edit-users'),
                'delete' => $request->user()->can('delete-users'),
            ]
        ]);
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        $this->authorize('create-users');
        
        return Inertia::render('Users/Create', [
            'roles' => Role::all(),
        ]);
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create-users');
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'phone' => 'nullable|string|max:20',
            'job_title' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'role' => 'required|exists:roles,name',
        ]);
        
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'],
            'job_title' => $validated['job_title'],
            'department' => $validated['department'],
        ]);
        
        $user->assignRole($validated['role']);
        
        return redirect()->route('users.index')
            ->with('success', 'User created successfully.');
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        $this->authorize('view-users');
        
        $user->load(['roles', 'contacts', 'deals', 'leads']);
        
        return Inertia::render('Users/Show', [
            'user' => $user,
            'stats' => [
                'contacts_count' => $user->contacts()->count(),
                'deals_count' => $user->deals()->count(),
                'leads_count' => $user->leads()->count(),
                'open_deals_value' => $user->deals()->whereNull('won')->sum('amount'),
                'won_deals_value' => $user->deals()->where('won', true)->sum('amount'),
                'tasks_completed' => $user->assignedTasks()->where('status', 'Completed')->count(),
                'tasks_pending' => $user->assignedTasks()->where('status', '!=', 'Completed')->count(),
            ],
        ]);
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        $this->authorize('edit-users');
        
        return Inertia::render('Users/Edit', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'job_title' => $user->job_title,
                'department' => $user->department,
                'roles' => $user->roles->pluck('name'),
            ],
            'roles' => Role::all(),
        ]);
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user)
    {
        $this->authorize('edit-users');
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'phone' => 'nullable|string|max:20',
            'job_title' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'role' => 'required|exists:roles,name',
        ]);
        
        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'job_title' => $validated['job_title'],
            'department' => $validated['department'],
        ]);
        
        // Sync roles (remove old ones, add new one)
        $user->syncRoles([$validated['role']]);
        
        return redirect()->route('users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user)
    {
        $this->authorize('delete-users');
        
        // Prevent self-deletion
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }
        
        // Soft delete the user
        $user->delete();
        
        return redirect()->route('users.index')
            ->with('success', 'User deleted successfully.');
    }
    
    /**
     * Update the current user's profile.
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'phone' => 'nullable|string|max:20',
            'job_title' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'current_password' => ['nullable', 'required_with:password', function ($attribute, $value, $fail) use ($user) {
                if ($value && !Hash::check($value, $user->password)) {
                    $fail('The current password is incorrect.');
                }
            }],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
        ]);
        
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->phone = $validated['phone'];
        $user->job_title = $validated['job_title'];
        $user->department = $validated['department'];
        
        if (isset($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }
        
        $user->save();
        
        return redirect()->route('profile.edit')
            ->with('success', 'Profile updated successfully.');
    }
}