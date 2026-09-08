<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\School;

class SchoolController extends Controller
{
    public function index()
    {
        return response()->json(School::orderBy('name')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'code' => 'required|string|unique:schools,code',
            'address' => 'nullable|string',
            'phone' => 'nullable|string',
        ]);

        $school = School::create($validated);
        return response()->json($school, 201);
    }

    public function update($id, Request $request)
    {
        $school = School::findOrFail($id);
        $school->update($request->only(['name', 'code', 'address', 'phone']));
        return response()->json($school);
    }

    public function show($id)
    {
        $school = School::with(['academicYears.classes.cashAccounts'])->findOrFail($id);
        return response()->json($school);
    }

    public function destroy($id)
    {
        $school = School::findOrFail($id);
        $school->delete();
        return response()->json(['message' => 'School deleted successfully']);
    }
}
