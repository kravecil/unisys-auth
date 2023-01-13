<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\DesktopController;


Route::post('login', [ AuthController::class, 'login' ]);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::delete('logout', [ AuthController::class, 'logout' ]);
    Route::post('token', [ AuthController::class, 'token']);

    Route::get('/user/{user_id?}', [ UserController::class, 'user']);
    Route::get('users', [ UserController::class, 'index']);
    Route::post('users/create', [UserController::class, 'create']);
    Route::post('users/update', [UserController::class, 'update']);
    Route::post('users/delete', [UserController::class, 'delete']);
    Route::post('users/importExcel', [UserController::class, 'importExcel']);

    Route::get('departments', [ DepartmentController::class, 'index']);
    Route::get('departments/{id}', [ DepartmentController::class, 'show']);
    Route::get('departments/children/{id}', [ DepartmentController::class, 'listChildren']);
    Route::get('/departments/parent/{id}', [DepartmentController::class, 'getParent']);
    Route::post('/departments/create', [DepartmentController::class, 'create']);
    Route::post('/departments/update', [DepartmentController::class, 'update']);
    Route::post('/departments/delete', [DepartmentController::class, 'delete']);

    Route::get('roles', [RoleController::class, 'index']);
    Route::post('/roles/create', [RoleController::class, 'create']);
    Route::post('/roles/update', [RoleController::class, 'update']);
    Route::post('/roles/delete', [RoleController::class, 'delete']);

    Route::get('permissions', [PermissionController::class, 'index']);
    Route::post('/permissions/create', [PermissionController::class, 'create']);
    Route::post('/permissions/update', [PermissionController::class, 'update']);
    Route::post('/permissions/delete', [PermissionController::class, 'delete']);

    Route::get('desktops', [DesktopController::class, 'index']);
    Route::post('desktops/create', [DesktopController::class, 'create']);
    Route::post('desktops/update', [DesktopController::class, 'update']);
    Route::post('desktops/delete', [DesktopController::class, 'delete']);
});

/** TEMP импорт */
// Route::post('import', function(Request $request) {
//     DB::transaction(function () use ($request){
//         foreach($request->data as $item) {
//             // \App\Models\User::updateOrCreate([ 'username' => ])
//             \App\Models\User::updateOrCreate(
//                 ['username' => $item['username']],
//                 [
//                     'password' => Hash::make($item['password']),
//                     'lastname' => Str::of($item['lastname'])->lower()->ucfirst(),
//                     'firstname' => Str::of($item['firstname'])->lower()->ucfirst(),
//                     'middlename' => Str::of($item['middlename'])->lower()->ucfirst(),
//                     'post' => Str::of($item['post'])->lower(),
//                     'is_leader' => $item['is_leader'] == '1'? true : false,
//                     'department_id' => \App\Models\Department::firstWhere('number', $item['dept_number'])?->id,
//                 ]
//             )
//             ->roles()->attach(\App\Models\Role::find(2));
//         }
//     });
//     return response('', 200);
// });