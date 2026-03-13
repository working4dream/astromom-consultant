<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Mail\AdminCreatedMail;
use Illuminate\Support\Facades\Mail;

class RoleController extends Controller
{
    public function index() 
    {
        $adminUsers = User::role('admin')->get();
        $permissions = Permission::all();
        $roles = Role::get();
        return view('roles.index', compact('adminUsers', 'permissions', 'roles'));
    }

    public function create() 
    {
        $permissions = Permission::all();
        return view('roles.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'permissions' => 'required|array',
        ]);
        
        $password = strtolower(str_replace(' ', '', $request->first_name)) . '@123';
        $admin = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($password),
        ]);
    
        $role = Role::findByName('admin');
        $admin->assignRole($role);
    
        if ($request->permissions) {
            $admin->syncPermissions($request->permissions);
        }
        Mail::to($admin->email)->send(new AdminCreatedMail($admin, $password));
        return redirect()->route('admin.roles.index')->with("success", 'Permission assigned successfully');
    }

    public function edit(User $admin)
    {
        $permissions = Permission::all();
        return view('roles.edit', compact('admin', 'permissions'));
    }

    public function update(Request $request, User $admin)
    {
        $admin->update(['first_name' => $request->first_name, 'last_name' => $request->last_name, 'email' => $request->email]);
        $admin->syncPermissions($request->permissions);
        return redirect()->route('admin.roles.index')->with("success", 'Permission updated successfully');
    }

    public function destroy($id)
    {
        $admin = User::findOrFail($id);
        $admin->syncPermissions([]);
        $admin->delete();
        return redirect()->route('admin.roles.index')->with('success', 'User & Permission deleted successfully');
    }
}
