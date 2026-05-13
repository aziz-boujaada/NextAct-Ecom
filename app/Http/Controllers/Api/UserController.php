<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{

    public function __construct()
    {
        $this->middleware('permissions:view_users')->only(['index', 'show']);

        $this->middleware('permissions:create_users')->only(['store']);

        $this->middleware('permissions:edit_users')->only(['update']);

        $this->middleware('permissions:delete_users')->only(['destroy']);

        $this->middleware('permissions:manage_permissions')->only(['assignPermissions']);
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::where('role', 'employee')
            ->with('permissions')
            ->get();

        return response()->json([
            'data' => $users,
            'message' => 'Employees retrieved successfully',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', Password::defaults()],
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['role'] = 'employee';

        $user = User::create($validated);

        return response()->json([
            'data' => $user,
            'message' => 'Employee created successfully',
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = User::with('permissions')->findOrFail($id);

        if ($user->role !== 'employee') {
            return response()->json([
                'message' => 'User is not an employee',
            ], 403);
        }

        return response()->json([
            'data' => $user,
            'message' => 'Employee retrieved successfully',
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);

        $authUser = Auth::user();

        if ($authUser->role !== 'admin') {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $id,
            'password' => ['nullable', Password::defaults()],
            'role' => ['sometimes', 'required', Rule::in([User::ROLE_ADMIN, User::ROLE_EMPLOYEE])]
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return response()->json([
            'data' => $user,
            'message' => 'Employee updated successfully',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);

        if ($user->role !== 'employee') {
            return response()->json([
                'message' => 'User is not an employee',
            ], 403);
        }

        $user->delete();

        return response()->json([
            'message' => 'Employee deleted successfully',
        ]);
    }

    /**
     * Assign permissions to an employee
     */
    public function assignPermissions(Request $request, string $id)
    {
        $user = User::findOrFail($id);

        if ($user->role !== 'employee') {
            return response()->json([
                'message' => 'User is not an employee',
            ], 403);
        }

        $validated = $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $user->permissions()->sync($validated['permissions']);

        return response()->json([
            'data' => $user->load('permissions'),
            'message' => 'Permissions assigned successfully',
        ]);
    }

    /**
     * Get all available permissions
     */
    public function getAvailablePermissions()
    {
        $permissions = Permission::all();

        return response()->json([
            'data' => $permissions,
            'message' => 'Permissions retrieved successfully',
        ]);
    }
}
