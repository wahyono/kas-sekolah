<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\DuesScheme;
use App\Models\StudentBilling;
use App\Models\CashAccount;
use App\Models\Transaction;

class BillingController extends Controller
{
    public function index(Request $request)
    {
        $query = StudentBilling::with(['duesScheme.cashAccount', 'student.enrollments.class']);

        if ($request->filled('schoolId')) {
            $query->whereHas('student', function ($q) use ($request) {
                $q->where('school_id', $request->schoolId);
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
            'cashAccountId' => 'required|string',
            'title' => 'required|string',
            'amount' => 'required|numeric|min:1',
            'dueDate' => 'required|date',
            'studentIds' => 'nullable|array',
        ]);

        return DB::transaction(function () use ($validated, $request) {
            $scheme = DuesScheme::create([
                'cash_account_id' => $validated['cashAccountId'],
                'title' => $validated['title'],
                'amount' => $validated['amount'],
                'due_date' => $validated['dueDate'],
            ]);

            $studentIds = $request->input('studentIds');
            if (empty($studentIds)) {
                $cashAccount = CashAccount::with(['class.enrollments', 'classModel.enrollments'])->findOrFail($validated['cashAccountId']);
                $targetClass = $cashAccount->class ?? $cashAccount->classModel;
                if ($targetClass && $targetClass->enrollments) {
                    $studentIds = $targetClass->enrollments->pluck('student_id')->toArray();
                } else {
                    $studentIds = [];
                }
            }

            $billings = [];
            foreach ($studentIds as $studentId) {
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
                'message' => 'Dues scheme created successfully',
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
