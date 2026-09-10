<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\DuesScheme;
use App\Models\StudentBilling;
use App\Models\CashAccount;
use App\Models\Transaction;
use App\Models\ClassModel;
use App\Models\ClassEnrollment;
use App\Models\User;

class BillingController extends Controller
{
    public function index(Request $request)
    {
        $query = StudentBilling::with(['duesScheme.cashAccount', 'student.enrollments.class']);

        if ($request->filled('schoolId') && $request->schoolId !== 'ALL') {
            $query->where(function ($sq) use ($request) {
                $sq->whereHas('student', function ($q) use ($request) {
                    $q->where('school_id', $request->schoolId);
                })->orWhereHas('duesScheme.cashAccount.class.academicYear', function ($q) use ($request) {
                    $q->where('school_id', $request->schoolId);
                });
            });
        }

        if ($request->filled('studentId')) {
            $query->where('student_id', $request->studentId);
        }

        if ($request->filled('cashAccountId')) {
            $query->whereHas('duesScheme', function ($q) use ($request) {
                $q->where('cash_account_id', $request->cashAccountId);
            });
        }

        if ($request->filled('classId')) {
            $query->whereHas('student.enrollments', function ($q) use ($request) {
                $q->where('class_id', $request->classId);
            });
        }

        return response()->json($query->orderBy('due_date', 'asc')->get());
    }

    public function getBySchool($schoolId)
    {
        $billings = StudentBilling::with(['duesScheme.cashAccount', 'student.enrollments.class'])
            ->whereHas('student', function ($q) use ($schoolId) {
                $q->where('school_id', $schoolId);
            })
            ->orderBy('due_date', 'asc')
            ->get();

        return response()->json($billings);
    }

    public function getByStudent($studentId)
    {
        $billings = StudentBilling::with(['duesScheme.cashAccount', 'student.enrollments.class'])
            ->where('student_id', $studentId)
            ->orderBy('due_date', 'asc')
            ->get();

        return response()->json($billings);
    }

    public function getByClass($classId)
    {
        $billings = StudentBilling::with(['duesScheme.cashAccount', 'student.enrollments.class'])
            ->whereHas('student.enrollments', function ($q) use ($classId) {
                $q->where('class_id', $classId);
            })
            ->orderBy('due_date', 'asc')
            ->get();

        return response()->json($billings);
    }

    public function getByScheme($schemeId)
    {
        $billings = StudentBilling::with(['duesScheme.cashAccount', 'student.enrollments.class'])
            ->where('dues_scheme_id', $schemeId)
            ->orderBy('due_date', 'asc')
            ->get();

        return response()->json($billings);
    }

    public function createDuesScheme(Request $request)
    {
        $validated = $request->validate([
            'cashAccountId' => 'nullable|string',
            'classId' => 'nullable|string',
            'schoolId' => 'nullable|string',
            'title' => 'required|string',
            'amount' => 'required|numeric|min:1',
            'dueDate' => 'required|date',
            'studentIds' => 'nullable|array',
        ]);

        return DB::transaction(function () use ($validated, $request) {
            $classId = $request->input('classId');
            $schoolId = $request->input('schoolId');
            $cashAccountId = $request->input('cashAccountId');

            // 1. Resolve target class and cash account
            $targetClass = null;
            if ($cashAccountId) {
                $cashAccount = CashAccount::with(['class.enrollments', 'classModel.enrollments'])->find($cashAccountId);
                if ($cashAccount) {
                    $targetClass = $cashAccount->class ?? $cashAccount->classModel;
                }
            }

            if (!$cashAccountId && $classId && $classId !== 'ALL') {
                $targetClass = ClassModel::with(['enrollments', 'academicYear'])->find($classId);
                if ($targetClass) {
                    $cashAccount = CashAccount::firstOrCreate(
                        ['class_id' => $targetClass->id],
                        ['name' => 'Kas Utama ' . $targetClass->name, 'currency' => 'IDR', 'current_balance' => 0]
                    );
                    $cashAccountId = $cashAccount->id;
                }
            }

            // Fallback cash account if not found
            if (!$cashAccountId) {
                $firstClass = null;
                if ($schoolId) {
                    $firstClass = ClassModel::whereHas('academicYear', function ($q) use ($schoolId) {
                        $q->where('school_id', $schoolId);
                    })->first();
                }
                if (!$firstClass) {
                    $firstClass = ClassModel::first();
                }

                if ($firstClass) {
                    $cashAccount = CashAccount::firstOrCreate(
                        ['class_id' => $firstClass->id],
                        ['name' => 'Kas Utama ' . $firstClass->name, 'currency' => 'IDR', 'current_balance' => 0]
                    );
                    $cashAccountId = $cashAccount->id;
                    if (!$targetClass) {
                        $targetClass = $firstClass;
                    }
                }
            }

            if (!$cashAccountId) {
                return response()->json(['message' => 'Akun kas kelas belum tersedia. Silakan buat kelas terlebih dahulu.'], 422);
            }

            // 2. Create the Dues Scheme
            $scheme = DuesScheme::create([
                'cash_account_id' => $cashAccountId,
                'title' => $validated['title'],
                'amount' => $validated['amount'],
                'due_date' => $validated['dueDate'],
            ]);

            // 3. Resolve student IDs
            $studentIds = $request->input('studentIds');
            if (empty($studentIds)) {
                if ($targetClass && $classId && $classId !== 'ALL') {
                    // Specific class enrollments
                    $studentIds = $targetClass->enrollments ? $targetClass->enrollments->pluck('student_id')->toArray() : [];
                    if (empty($studentIds)) {
                        $studentIds = User::where('role', 'STUDENT')->where('managed_class', $targetClass->id)->pluck('id')->toArray();
                    }
                    if (empty($studentIds) && $targetClass->academicYear?->school_id) {
                        $studentIds = User::where('role', 'STUDENT')->where('school_id', $targetClass->academicYear->school_id)->pluck('id')->toArray();
                    }
                } else {
                    // All classes or whole school
                    $resolvedSchoolId = $schoolId ?? ($targetClass?->academicYear?->school_id) ?? auth()->user()?->school_id;
                    $studentQuery = User::where('role', 'STUDENT');
                    if ($resolvedSchoolId && $resolvedSchoolId !== 'ALL') {
                        $studentQuery->where('school_id', $resolvedSchoolId);
                    }
                    $studentIds = $studentQuery->pluck('id')->toArray();
                }
            }

            $billings = [];
            foreach (array_unique($studentIds) as $studentId) {
                $billings[] = StudentBilling::create([
                    'dues_scheme_id' => $scheme->id,
                    'student_id' => $studentId,
                    'amount_due' => $validated['amount'],
                    'amount_paid' => 0.00,
                    'status' => 'PENDING',
                    'due_date' => $validated['dueDate'],
                ]);
            }

            return response()->json([
                'message' => "Tagihan '{$scheme->title}' berhasil dibuat untuk " . count($billings) . " siswa!",
                'scheme' => $scheme,
                'totalBillingsGenerated' => count($billings),
            ], 201);
        });
    }

    public function payBilling($id, Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'createdBy' => 'required|string',
        ]);

        return DB::transaction(function () use ($id, $validated) {
            $billing = StudentBilling::with(['duesScheme.cashAccount', 'student'])->findOrFail($id);
            $payAmount = (float) $validated['amount'];
            $newPaid = (float) $billing->amount_paid + $payAmount;

            $status = $newPaid >= (float) $billing->amount_due ? 'PAID' : 'PARTIAL';

            $billing->update([
                'amount_paid' => $newPaid,
                'status' => $status,
            ]);

            // Add to Cash Account
            $cashAccount = $billing->duesScheme->cashAccount;
            $cashAccount->increment('current_balance', $payAmount);

            // Create Transaction record
            $tx = Transaction::create([
                'cash_account_id' => $cashAccount->id,
                'billing_id' => $billing->id,
                'type' => 'INCOME',
                'amount' => $payAmount,
                'category' => 'Iuran Kas',
                'description' => "Pembayaran {$billing->duesScheme->title} - {$billing->student->name}",
                'created_by' => $validated['createdBy'],
                'created_at' => now(),
            ]);

            return response()->json([
                'billing' => $billing->fresh(),
                'transaction' => $tx,
                'currentBalance' => $cashAccount->fresh()->current_balance,
            ]);
        });
    }
}
