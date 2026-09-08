<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CashAccount;

class CashAccountController extends Controller
{
    public function getByClass($classId)
    {
        $account = CashAccount::where('class_id', $classId)->first();
        if (!$account) {
            $account = CashAccount::create([
                'class_id' => $classId,
                'name' => "Kas Kelas",
                'current_balance' => 0.00,
                'currency' => 'IDR',
            ]);
        }
        return response()->json([$account]);
    }

    public function show($id)
    {
        $account = CashAccount::with('class')->findOrFail($id);
        return response()->json($account);
    }
}
