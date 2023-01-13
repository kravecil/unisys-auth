<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\Role;
use App\Models\Permission;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        $include_permissions = $request->boolean('include_permissions');
        $include_desktops = $request->boolean('include_desktops');
        $roles = Role::orderby('title', 'asc');
        if ($include_permissions == true) {
            $roles->with('permissions');
        };
        if ($include_desktops == true) {
            $roles->with('desktops');
        };
        $roles = $roles->get();
        return $roles;
    }

    public function create(Request $request)
    {
        $request->user()->can(['administration', 'db_modify']);
        $validator = Validator::make($request->all(), [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $role = Role::where('title', $request->title)->first();
        if ($role)
            return response()->json([
                'error' => 'Role title is already in use'
            ], 409);
        $role = Role::create([
            'title' => $request->title,
            'description' => $request->description,
        ]);
        $role->permissions()->sync($request->permissions_ids);

        return response()->json(['Role created successfully'], 200);
    }
    public function update(Request $request)
    {
        $request->user()->can(['administration', 'db_modify']);
        $validator = Validator::make($request->all(), [
            'id' => ['required', 'int'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }
        $role = Role::find($request->id);
        if (!$role) {
            return response()->json(['error' => 'Role doesnt exists'], 404);
        }
        $role->update($request->all());
        $role->permissions()->sync($request->permissions_ids);
        $role->desktops()->sync($request->desktops_ids);
        return response()->json(['Role updated successfully'], 200);
    }

    public function delete(Request $request)
    {
        $request->user()->can(['administration', 'db_modify']);
        $validator = Validator::make($request->all(), [
            'id' => ['required', 'int'],
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }
        $role = Role::find($request->input('id'));
        if (!$role) {
            return response()->json(['error' => 'Role doesnt exists'], 404);
        }
        $role->delete();
        return response()->json(['Role deleted successfully'], 200);
    }
}
