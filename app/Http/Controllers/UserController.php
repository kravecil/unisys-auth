<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Imports\UsersImport;
use Maatwebsite\Excel\Facades\Excel;

use App\Models\User;
use App\Models\Department;
use App\Models\Role;

class UserController extends Controller
{
    protected $currentUser;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->currentUser = $request->user();
            // also can set other variables here as well

            // let the request continue through the stack
            return $next($request);
        });
    }

    public function user(Request $request, $user_id = null) {
        $user = $user_id == null? $request->user() : User::findOrFail($user_id);

        $user['roles'] = $user->roles;
        $user['permissions'] = $user->permissions();
        $user['desktops'] = $user->desktops();

        return $user;
    }

    public function index(Request $request) {
        $search = $request->input('search');
        $filterByDepartment = $request->input('filterByDepartment');
        $include_departments = $request->boolean('include_departments');
        $include_roles = $request->boolean('include_roles');

        $users = !$request->filled('search')?
            User::orderBy('lastname', 'asc')
                ->orderBy('firstname', 'asc')
                ->orderBy('middlename', 'asc')
                :
            User::orderBy('lastname', 'asc')
                ->orderBy('firstname', 'asc')
                ->orderBy('middlename', 'asc')
                ->where('lastname', 'LIKE', '%' . $search . '%')
                ->orWhere('firstname', 'LIKE', '%' . $search . '%')
                ->orWhere('middlename', 'LIKE', '%' . $search . '%');

        // $users->with('permissions');

        if ($include_roles == true) {
            $users->with('roles');
        }
        if ($include_departments == true) {
            $users->with('department');
        }
        // $users->with('permissions');
        if ($request->filled('filterByDepartment')) {
            $users->where('department_id', "=", $filterByDepartment);
        };

        $users = $users->get()->except([1]);

        return $users;
    }

    public function create(Request $request)
    {
        $request->user()->can(['administration', 'db_modify']);
        $validator = Validator::make($request->all(), [
            'username' => ['required', 'unique:users,username', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:6'],
            'lastname' => ['required', 'string', 'max:255'],
            'firstname' => ['required', 'string', 'max:255'],
            'middlename' => ['string', 'max:255'],
            'is_leader' => ['required', 'boolean'],
            'department_id' => ['required', 'int', 'max:255'],
            'post' => ['string', 'max:255']
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $user = User::where('username', $request->username)->first();
        if ($user)
            return response()->json([
                'error' => 'Username is already in use'
            ], 409);

        // $user->tokens()->where('name', $request->device)->delete();

        // $token = $user->createToken($request->device)->plainTextToken;

        $user = User::create([
            'username' => $request->username,
            'password' => $request->password,
            'lastname' => $request->lastname,
            'firstname' => $request->firstname,
            'middlename' => $request->middlename,
            'is_leader' => $request->is_leader,
            'department_id' => Department::find($request->department_id)?->id,
            'post' => $request->post,
        ]);
        $user->roles()->sync($request->roles_ids);

        return response()->json(['User created successfully'], 200);
    }
    public function update(Request $request)
    {
        $request->user()->can(['administration', 'db_modify']);
        $attributes = array_filter($request->all());
        $validator = Validator::make($request->all(), [
            'id' => ['required', 'int'],
            'password' => ['string', 'nullable', 'min:6'],
            'username' => ['required', 'string', 'max:255'],
            'lastname' => ['required', 'string', 'max:255'],
            'firstname' => ['required', 'string', 'max:255'],
            'middlename' => ['string', 'nullable', 'max:255'],
            'is_leader' => ['required', 'boolean'],
            'department_id' => ['required', 'int'],
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }
        $user = User::find($request->id);
        if (!$user) {
            return response()->json(['error' => 'User doesnt exists'], 404);
        }
        if (empty($request->password))
            $attributes = $request->except('password');
        $user->update($attributes);
        $user->roles()->sync($request->roles_ids);

        return response()->json(['User updated successfully'], 200);
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
        $user = User::find($request->input('id'));
        if (!$user) {
            return response()->json(['error' => 'User doesnt exists'], 404);
        }
        $user->delete();
        return response()->json(['User deleted successfully'], 200);
    }

    public function importExcel(Request $request)
    {

        $request->user()->can(['administration', 'db_modify']);
        $input = $request->all();
        $path = $request->file('file'); //->store('temp');
        $import = new UsersImport();
        $import->import($path);

        //Excel::download(new FailuresExport($array), 'failures.csv')
        // var_dump('errors:', count($import->failures()));
        // foreach ($import->failures() as $failure) {
        //     $failure->row(); // row that went wrong
        //     $failure->attribute(); // either heading key (if using heading row concern) or column index
        //     var_dump($failure->errors()); // Actual error messages from Laravel validator
        //     $failure->values(); // The values of the row that has failed.
        // }
        // Excel::import(new UsersImport, $path);

        // $validator = Validator::make($request->all(), [
        //     'id' => ['required', 'int'],
        // ]);
        // if ($validator->fails()) {
        //     return response()->json(['error' => $validator->errors()], 400);
        // }
        // $user = User::find($request->input('id'));
        // if (!$user) {
        //     return response()->json(['error' => 'User doesnt exists'], 404);
        // }
        // $user->delete();

        return response()->json(['Users import from excel successfull'], 200);
    }
}
