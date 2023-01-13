<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use App\Models\Department;

class DepartmentController extends Controller
{
    public function index(Request $request)
    {
        return Department::orderBy('number')
        ->orderBy('title')
        ->get();
    }

    public function show(Request $request, $id)
    {
        $department = Department::with('leaders')
            ->firstWhere('id', $id);
        return $department;
    }

    public function getParent(Request $request, $id)
    {
        $request->user()->can(['administration']);
        $department = Department::findOrFail($id);
        $parentDepartment = Department::findOrFail($department->department_id);
        return $parentDepartment;
    }

    public function listChildren(Request $request, $id)
    {
        $departments = Department::findOrFail($id)
            ->childrenDepartments()
            ->get();

        $rf = function ($d) use (&$result, &$rf) {
            foreach ($d as $value) {
                $result[] = $value->id;

                $rf($value->departments);
            }
        };
        return $departments;
    }

    public function create(Request $request)
    {
        $request->user()->can(['administration', 'db_modify']);
        $validator = Validator::make($request->all(), [
            'number' => ['string', 'max:255'],
            'title' => ['required', 'string', 'max:255'],
            'department_id' => ['integer'],
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $department = Department::where('title', $request->title)->first();
        if ($department)
            return response()->json([
                'error' => 'Department title is already in use'
            ], 409);
        $department = Department::create([
            'number' => $request->number,
            'title' => $request->title,
            'department_id' => $request->department_id,
        ]);
        return response()->json(['Department created successfully'], 200);
    }
    public function update(Request $request)
    {
        $request->user()->can(['administration', 'db_modify']);
        $validator = Validator::make($request->all(), [
            'id' => ['required', 'int'],
            'number' => ['string', 'nullable', 'max:255'],
            'title' => ['string', 'max:255'],
            'department_id' => ['integer', 'nullable', 'different:id'],
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }
        $department = Department::find($request->id);
        if (!$department) {
            return response()->json(['error' => 'Department doesnt exists'], 404);
        }
        $department->update($request->all());
        return response()->json(['Department updated successfully'], 200);
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
        $department = Department::find($request->input('id'));
        if (!$department) {
            return response()->json(['error' => 'Department doesnt exists'], 404);
        }
        $department->delete();
        return response()->json(['Department deleted successfully'], 200);
    }
}
