<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $roles = Role::with(['permissions'])->paginate(10);
        $permissions = Permission::all();
        return view('roles.index',compact('roles','permissions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $validated = $request->validate([
            'name' => 'required|unique:roles,name',
            'permissions' => 'array',
            //'permissions.*'=> 'exists:permissions,id'
        ]);

        //dd($request);

        $role = Role::create(['name' => $request->name]);

        if ($request->has('permissions')) {

            $role->givePermissionTo($request->permissions);
        }

        //return response
        return response()->json([
            'status' => 'success',
            'message' => 'Role created successfully',
            'role' => $role,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Role $role)
    {
        //
        $permissions = Permission::all();
        $rolePermissions = $role->permissions->pluck('id')->toArray();
        return view('roles.edit',compact('permissions','rolePermissions','role'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Role $role)
    {
        //
         $request->validate([
            'name' => 'required|unique:roles,name,'.$role->id,
            'permissions' => 'array',
        ]);

        $role->update(['name' => $request->name]);
        $role->syncPermissions($request->permissions ?? []);

        //send notification to user in this role
        //$users = $role->users;
        // foreach ($users as $user) {
        //     $user->notify(new \App\Notifications\GeneralNotification(
        //         'Role Updated',
        //         'Your role has been updated to ' . $role->name,
        //         'bi-info',
        //         route('home')
        //     ));
        // }   

        // return redirect()->route('roles.index')
        //     ->with('success', 'Role updated successfully');
        return response()->json([
            'success' => true 
        ],200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        //
        $role->delete();
        //return response
        return response()->json([
            'status' => 'success',
            'message' => 'Role deleted successfully'
        ], 200);
    }
}
