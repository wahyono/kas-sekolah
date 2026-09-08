<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Transaction;
use App\Models\Expense;
use App\Models\CashAccount;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaction::with(['cashAccount', 'billing.student', 'creator']);

        if ($request->filled('schoolId')) {
            $query->whereHas('cashAccount.class.academicYear', function ($q) use ($request) {
                $q->where('school_id', $request->schoolId);
            });
        }

        if ($request->filled('cashAccountId')) {
            $query->where('cash_account_id', $request->cashAccountId);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        return response()->json($query->orderByDesc('created_at')->get());
    }

    public function getBySchool($schoolId)
    {
        $txs = Transaction::with(['cashAccount.class', 'billing.student', 'creator'])
            ->whereHas('cashAccount.class.academicYear', function ($q) use ($schoolId) {
                $q->where('school_id', $schoolId);
            })
            ->orderByDesc('created_at')
            ->get();

        return response()->json($txs);
    }

    public function getByAccount($cashAccountId)
    {
        $txs = Transaction::with(['cashAccount.class', 'billing.student', 'creator'])
            ->where('cash_account_id', $cashAccountId)
            ->orderByDesc('created_at')
            ->get();

        return response()->json($txs);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'cashAccountId' => 'required|string',
            'type' => 'required|in:INCOME,EXPENSE',
            'amount' => 'required|numeric|min:1',
            'category' => 'required|string',
            'description' => 'required|string',
            'receiptUrl' => 'nullable|string',
            'createdBy' => 'required|string',
        ]);

        return DB::transaction(function () use ($validated) {
            $cashAccount = CashAccount::findOrFail($validated['cashAccountId']);
            $amount = (float) $validated['amount'];

            if ($validated['type'] === 'INCOME') {
                $cashAccount->increment('current_balance', $amount);
            } else {
                if ($cashAccount->current_balance < $amount) {
                    return response()->json(['message' => 'Saldo kas tidak mencukupi untuk pengeluaran ini'], 400);
                }
                $cashAccount->decrement('current_balance', $amount);
            }

            $tx = Transaction::create([
                'cash_account_id' => $validated['cashAccountId'],
                'type' => $validated['type'],
                'amount' => $amount,
                'category' => $validated['category'],
                'description' => $validated['description'],
                'receipt_url' => $validated['receiptUrl'] ?? null,
                'created_by' => $validated['createdBy'],
                'created_at' => now(),
            ]);

            return response()->json([
                'transaction' => $tx,
                'currentBalance' => $cashAccount->fresh()->current_balance,
            ], 201);
        });
    }

    public function getExpenses(Request $request)
    {
        $query = Expense::with(['cashAccount', 'requester', 'approver']);

        if ($request->filled('schoolId')) {
            $query->whereHas('cashAccount.class.academicYear', function ($q) use ($request) {
                $q->where('school_id', $request->schoolId);
            });
        }

        if ($request->filled('cashAccountId')) {
            $query->where('cash_account_id', $request->cashAccountId);
        }

        if ($request->filled('status')) {
            $query->where('approval_status', $request->status);
        }

        return response()->json($query->orderByDesc('created_at')->get());
    }

    public function createExpense(Request $request)
    {
        $validated = $request->validate([
            'cashAccountId' => 'required|string',
            'amount' => 'required|numeric|min:1',
            'title' => 'required|string',
            'description' => 'required|string',
            'receiptUrl' => 'nullable|string',
            'requestedBy' => 'required|string',
        ]);

        $expense = Expense::create([
            'cash_account_id' => $validated['cashAccountId'],
            'amount' => $validated['amount'],
            'title' => $validated['title'],
            'description' => $validated['description'],
            'receipt_url' => $validated['receiptUrl'] ?? null,
            'approval_status' => 'PENDING',
            'requested_by' => $validated['requestedBy'],
        ]);

        return response()->json($expense->load(['requester']), 201);
    }

    public function approveExpense($id, Request $request)
    {
        $validated = $request->validate([
            'status' => 'required|in:APPROVED,REJECTED',
            'approvedBy' => 'required|string',
        ]);

        return DB::transaction(function () use ($id, $validated) {
            $expense = Expense::with('cashAccount')->findOrFail($id);

            if ($expense->approval_status !== 'PENDING') {
                return response()->json(['message' => 'Pengeluaran sudah diproses sebelumnya'], 400);
            }

            $expense->update([
                'approval_status' => $validated['status'],
                'approved_by' => $validated['approvedBy'],
            ]);

            // If APPROVED, decrement cash balance & record transaction ledger
            if ($validated['status'] === 'APPROVED') {
                $cashAccount = $expense->cashAccount;
                $amount = (float) $expense->amount;

                if ($cashAccount->current_balance < $amount) {
                    return response()->json(['message' => 'Saldo kas tidak mencukupi untuk pengeluaran ini'], 400);
                }

                $cashAccount->decrement('current_balance', $amount);

                Transaction::create([
                    'cash_account_id' => $cashAccount->id,
                    'type' => 'EXPENSE',
                    'amount' => $amount,
                    'category' => 'Pengeluaran Kelas',
                    'description' => "Pengeluaran Disetujui: {$expense->title}",
                    'receipt_url' => $expense->receipt_url,
                    'created_by' => $validated['approvedBy'],
                    'created_at' => now(),
                ]);
            }

            return response()->json($expense->fresh(['cashAccount', 'requester', 'approver']));
        });
    }
}
