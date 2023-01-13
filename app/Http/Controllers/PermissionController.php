<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\Permission;

class PermissionController extends Controller
{
    public function index(Request $request)
    {
        $include_permissions = $request->boolean('include_permissions');
        $permissions = Permission::orderby('description', 'desc');
        $permissions = $permissions->get();
        return $permissions;
    }

    public function create(Request $request)
    {
        $request->user()->can(['administration', 'db_modify']);
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['string'],
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $permission = Permission::where('name', $request->name)->first();
        if ($permission)
            return response()->json([
                'error' => 'Permission name is already in use'
            ], 409);
        $permission = Permission::create([
            'name' => $request->name,
            'description' => $request->description,
        ]);
        return response()->json(['Permission created successfully'], 200);
    }
    public function update(Request $request)
    {
        $request->user()->can(['administration', 'db_modify']);
        $validator = Validator::make($request->all(), [
            'id' => ['required', 'int'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['string'],
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }
        $permission = Permission::find($request->id);
        if (!$permission) {
            return response()->json(['error' => 'Permission doesnt exists'], 404);
        }
        $permission->update($request->all());
        return response()->json(['Permission updated successfully'], 200);
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
        $permission = Permission::find($request->input('id'));
        if (!$permission) {
            return response()->json(['error' => 'Permission doesnt exists'], 404);
        }
        $permission->delete();
        return response()->json(['Permission deleted successfully'], 200);
    }
}
