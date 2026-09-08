<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ClassModel;
use App\Models\CashAccount;

class ClassController extends Controller
{
    public function index(Request $request)
    {
        $query = ClassModel::with(['academicYear', 'cashAccounts']);

        if ($request->filled('academicYearId')) {
            $query->where('academic_year_id', $request->academicYearId);
        }

        if ($request->filled('schoolId')) {
            $query->whereHas('academicYear', function ($q) use ($request) {
                $q->where('school_id', $request->schoolId);
            });
        }

        return response()->json($query->orderBy('name')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'academicYearId' => 'required|string',
            'name' => 'required|string',
        ]);

        $class = ClassModel::create([
            'academic_year_id' => $validated['academicYearId'],
            'name' => $validated['name'],
        ]);

        // Auto-create default Cash Account for this class
        CashAccount::create([
            'class_id' => $class->id,
            'name' => "Kas Utama {$class->name}",
            'current_balance' => 0.00,
            'currency' => 'IDR',
        ]);

        return response()->json($class->load('cashAccounts'), 201);
    }

    public function show($id)
    {
        $class = ClassModel::with(['academicYear', 'cashAccounts', 'students'])->findOrFail($id);
        return response()->json($class);
    }
}
