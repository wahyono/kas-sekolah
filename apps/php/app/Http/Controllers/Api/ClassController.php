<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ClassModel;
use App\Models\CashAccount;
use App\Models\AcademicYear;
use App\Models\School;

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
            'name' => 'required|string',
            'academicYearId' => 'nullable|string',
            'schoolId' => 'nullable|string',
        ]);

        try {
            $academicYearId = $validated['academicYearId'] ?? null;

            // If academicYearId is not provided or empty, resolve or auto-create for school
            if (empty($academicYearId)) {
                $user = auth()->user();
                $schoolId = $validated['schoolId'] ?? $request->header('X-School-Id') ?? ($user ? $user->school_id : null);
                $academicYear = null;

                if ($schoolId) {
                    $academicYear = AcademicYear::where('school_id', $schoolId)->where('is_current', true)->first()
                        ?? AcademicYear::where('school_id', $schoolId)->latest()->first();
                }

                if (!$academicYear) {
                    $academicYear = AcademicYear::where('is_current', true)->first()
                        ?? AcademicYear::latest()->first();
                }

                // If no academic year exists at all, auto-create default current academic year
                if (!$academicYear) {
                    $firstSchool = School::first();
                    $targetSchoolId = $schoolId ?? ($firstSchool ? $firstSchool->id : null);
                    if (!$targetSchoolId) {
                        $defaultSchool = School::create([
                            'name' => 'Sekolah Utama',
                            'address' => 'Jl. Pendidikan No. 1',
                        ]);
                        $targetSchoolId = $defaultSchool->id;
                    }

                    $academicYear = AcademicYear::create([
                        'school_id' => $targetSchoolId,
                        'year' => date('Y') . '/' . (date('Y') + 1),
                        'is_current' => true,
                    ]);
                }

                $academicYearId = $academicYear->id;
            }

            // Check if class with same name already exists in this academic year
            $existing = ClassModel::where('academic_year_id', $academicYearId)
                ->where('name', trim($validated['name']))
                ->first();

            if ($existing) {
                return response()->json([
                    'message' => "Kelas '{$validated['name']}' sudah terdaftar pada tahun ajaran ini."
                ], 422);
            }

            $class = ClassModel::create([
                'academic_year_id' => $academicYearId,
                'name' => trim($validated['name']),
            ]);

            // Auto-create default Cash Account for this class if not already exists
            CashAccount::firstOrCreate(
                ['class_id' => $class->id],
                [
                    'name' => "Kas Utama {$class->name}",
                    'current_balance' => 0.00,
                    'currency' => 'IDR',
                ]
            );

            return response()->json($class->load(['academicYear', 'cashAccounts']), 201);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Gagal membuat kelas: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $class = ClassModel::with(['academicYear', 'cashAccounts', 'students'])->findOrFail($id);
        return response()->json($class);
    }
}
