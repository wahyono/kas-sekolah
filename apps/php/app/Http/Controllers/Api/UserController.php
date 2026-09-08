<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\ClassEnrollment;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with(['school', 'enrollments.class']);

        if ($request->filled('schoolId')) {
            $query->where(function ($q) use ($request) {
                $q->where('school_id', $request->schoolId)
                  ->orWhere('role', 'SUPER_ADMIN');
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%")
                  ->orWhere('nis', 'like', "%{$s}%")
                  ->orWhere('nisn', 'like', "%{$s}%");
            });
        }

        return response()->json($query->orderBy('name')->get());
    }

    public function show($id)
    {
        $user = User::with(['school', 'enrollments.class', 'childStudent'])->findOrFail($id);
        return response()->json($user);
    }

    public function update($id, Request $request)
    {
        $user = User::findOrFail($id);

        $data = $request->only([
            'name', 'email', 'phone', 'gender', 'religion',
            'nis', 'nisn', 'role', 'managed_class', 'is_active', 'student_id'
        ]);

        if ($request->filled('password')) {
            $data['password_hash'] = Hash::make($request->password);
        }

        $user->update($data);

        // Update class enrollment if classId provided
        if ($request->filled('classId')) {
            ClassEnrollment::updateOrCreate(
                ['student_id' => $user->id],
                ['class_id' => $request->classId]
            );
        }

        return response()->json($user->load(['school', 'enrollments.class']));
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        try {
            $user->delete();
            return response()->json(['message' => 'Akun pengguna berhasil dihapus']);
        } catch (\Exception $e) {
            $user->update(['is_active' => false]);
            return response()->json(['message' => 'Akun dinonaktifkan karena memiliki riwayat data keuangan']);
        }
    }
}
