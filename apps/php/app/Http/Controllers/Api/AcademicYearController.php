<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AcademicYear;

class AcademicYearController extends Controller
{
    public function index(Request $request)
    {
        $query = AcademicYear::query();
        if ($request->filled('schoolId')) {
            $query->where('school_id', $request->schoolId);
        }
        return response()->json($query->orderByDesc('year')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'schoolId' => 'required|string',
            'year' => 'required|string',
            'isCurrent' => 'nullable|boolean',
        ]);

        if (!empty($validated['isCurrent'])) {
            AcademicYear::where('school_id', $validated['schoolId'])->update(['is_current' => false]);
        }

        $academicYear = AcademicYear::create([
            'school_id' => $validated['schoolId'],
            'year' => $validated['year'],
            'is_current' => $validated['isCurrent'] ?? false,
        ]);

        return response()->json($academicYear, 201);
    }

    public function setCurrent($id)
    {
        $academicYear = AcademicYear::findOrFail($id);
        AcademicYear::where('school_id', $academicYear->school_id)->update(['is_current' => false]);
        $academicYear->update(['is_current' => true]);

        return response()->json($academicYear);
    }
}
