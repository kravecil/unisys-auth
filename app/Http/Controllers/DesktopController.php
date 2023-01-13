<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Response;
use App\Models\Desktop;
use App\Models\User;

class DesktopController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $desktops = Desktop::orderby('id', 'asc')->get();
        return ($desktops);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $request->user()->can(['administration', 'db_modify']);
        $validator = Validator::make($request->all(), [
            'title' => ['required', 'unique:desktops,title', 'string', 'max:255'],
            'path' => ['string', 'nullable', 'max:255'],
            'description' => ['nullable', 'string', 'max:255'],
            'under_construction' => ['required', 'boolean'],
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $desktop = Desktop::where('title', $request->title)->first();
        if ($desktop)
            return response()->json([
                'error' => 'Desktop title is already in use'
            ], 409);



        $desktop = Desktop::create([
            'title' => $request->title,
            'path' => $request->path,
            'description' => $request->description,
            'under_construction' => $request->under_construction,
        ]);
        return response()->json(['Desktop created successfully'], 200);
    }



    public function update(Request $request)
    {
        $request->user()->can(['administration', 'db_modify']);
        $attributes = ($request->all());
        $validator = Validator::make($request->all(), [
            'id' => ['required', 'int'],
            'title' => ['required', 'string', 'max:255'],
            'path' => ['string', 'nullable', 'max:255'],
            'description' => ['nullable', 'string', 'max:255'],
            'under_construction' => ['required', 'boolean'],
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }
        $desktop = Desktop::find($request->id);
        if (!$desktop) {
            return response()->json(['error' => 'Desktop doesnt exists'], 404);
        }
        $desktop->update($attributes);

        return response()->json(['Desktop updated successfully'], 200);
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
        $desktop = Desktop::find($request->input('id'));
        if (!$desktop) {
            return response()->json(['error' => 'Desktop doesnt exists'], 404);
        }
        $desktop->delete();
        return response()->json(['Desktop deleted successfully'], 200);
    }
}
