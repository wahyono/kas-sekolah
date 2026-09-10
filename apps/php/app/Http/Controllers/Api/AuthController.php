<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Generate a random Indonesian city security challenge for login verification
     */
    public function getSecurityChallenge()
    {
        $cities = [
            'Jakarta', 'Surabaya', 'Bandung', 'Medan', 'Semarang',
            'Yogyakarta', 'Denpasar', 'Makassar', 'Palembang', 'Malang',
            'Balikpapan', 'Samarinda', 'Padang', 'Pekanbaru', 'Banjarmasin',
            'Pontianak', 'Manado', 'Mataram', 'Kupang', 'Ambon',
            'Jayapura', 'Banda Aceh', 'Batam', 'Bogor', 'Depok',
            'Tangerang', 'Bekasi', 'Solo', 'Cirebon', 'Magelang',
            'Sukabumi', 'Tasikmalaya', 'Cimahi', 'Pekalongan', 'Kediri'
        ];
        $city = $cities[array_rand($cities)];
        $time = time();
        $key = config('app.key') ?: 'kas-sekolah-security-key-2026';
        $hash = hash_hmac('sha256', strtolower($city) . '|' . $time, $key);
        $token = base64_encode($city . '|' . $time . '|' . $hash);

        return response()->json([
            'city' => $city,
            'token' => $token,
        ]);
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|string',
            'password' => 'required|string',
            'securityCity' => 'nullable|string',
            'securityToken' => 'nullable|string',
        ]);

        // Validate random city security filter
        $token = $request->input('securityToken');
        $inputCity = trim(strtolower($request->input('securityCity', '')));
        if ($token || $inputCity) {
            $validChallenge = false;
            if ($token && $inputCity) {
                $decoded = base64_decode($token, true);
                if ($decoded) {
                    $parts = explode('|', $decoded);
                    if (count($parts) === 3) {
                        [$expectedCity, $time, $hash] = $parts;
                        $key = config('app.key') ?: 'kas-sekolah-security-key-2026';
                        $calcHash = hash_hmac('sha256', strtolower($expectedCity) . '|' . $time, $key);
                        // Valid for 15 minutes and case-insensitive matching
                        if (hash_equals($calcHash, $hash) && (time() - (int)$time < 900) && strtolower($expectedCity) === $inputCity) {
                            $validChallenge = true;
                        }
                    }
                }
            }
            if (!$validChallenge) {
                return response()->json(['message' => 'Verifikasi nama kota tidak sesuai atau telah kedaluwarsa. Silakan ketik nama kota yang tertera.'], 422);
            }
        }

        $identifier = $validated['email'];
        $user = User::where('email', $identifier)
            ->orWhere('nis', $identifier)
            ->orWhere('phone', $identifier)
            ->first();

        if (!$user) {
            return response()->json(['message' => 'Kredensial tidak valid'], 401);
        }

        // Verify password safely (handles Bcrypt, Argon2id, etc.)
        $isValid = false;
        try {
            if (password_verify($validated['password'], $user->password_hash)) {
                $isValid = true;
            } elseif (Hash::check($validated['password'], $user->password_hash)) {
                $isValid = true;
            }
        } catch (\Throwable $e) {
            $isValid = false;
        }

        if (!$isValid) {
            return response()->json(['message' => 'Password salah'], 401);
        }

        if (!$user->is_active) {
            return response()->json(['message' => 'Akun tidak aktif. Hubungi administrator.'], 403);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'accessToken' => $token,
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'schoolId' => $user->school_id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'managedClass' => $user->managed_class,
                'studentId' => $user->student_id,
                'nis' => $user->nis,
                'nisn' => $user->nisn,
            ],
        ]);
    }

    public function register(Request $request)
    {
        // 1. Auto resolve schoolId if empty
        if (empty($request->input('schoolId')) && auth('sanctum')->check()) {
            $request->merge(['schoolId' => auth('sanctum')->user()->school_id]);
        }
        if (empty($request->input('schoolId'))) {
            $firstSchool = \App\Models\School::first();
            if ($firstSchool) {
                $request->merge(['schoolId' => $firstSchool->id]);
            }
        }

        // 2. Clean empty strings for optional fields to ensure NULL in DB
        $input = $request->all();
        if (isset($input['password']) && trim($input['password']) === '') {
            $request->merge(['password' => null]);
        }
        if (isset($input['nis']) && trim($input['nis']) === '') {
            $request->merge(['nis' => null]);
        }
        if (isset($input['nisn']) && trim($input['nisn']) === '') {
            $request->merge(['nisn' => null]);
        }

        $validated = $request->validate([
            'schoolId' => 'required|string',
            'name' => 'required|string|max:191',
            'email' => 'required|email|max:191|unique:users,email',
            'password' => 'nullable|string|min:4',
            'role' => 'nullable|string',
            'phone' => 'nullable|string',
            'managedClass' => 'nullable|string',
            'studentId' => 'nullable|string',
            'nis' => 'nullable|string|unique:users,nis',
            'nisn' => 'nullable|string|unique:users,nisn',
            'gender' => 'nullable|string',
            'religion' => 'nullable|string',
            'classId' => 'nullable|string',
        ], [
            'name.required' => 'Nama lengkap siswa wajib diisi.',
            'email.required' => 'Email siswa wajib diisi.',
            'email.unique' => 'Email ini sudah terdaftar di sistem.',
            'password.min' => 'Password minimal 4 karakter.',
            'nis.unique' => 'NIS ini sudah terdaftar pada siswa lain.',
            'nisn.unique' => 'NISN ini sudah terdaftar pada siswa lain.',
            'schoolId.required' => 'Data sekolah tidak ditemukan.',
        ]);

        $plainPassword = !empty($validated['password']) ? $validated['password'] : 'Password123!';

        try {
            return \Illuminate\Support\Facades\DB::transaction(function () use ($validated, $plainPassword) {
                $user = User::create([
                    'school_id' => $validated['schoolId'],
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'password_hash' => Hash::make($plainPassword),
                    'plain_password' => $plainPassword,
                    'role' => $validated['role'] ?? 'STUDENT',
                    'phone' => $validated['phone'] ?? null,
                    'managed_class' => !empty($validated['managedClass']) ? $validated['managedClass'] : (!empty($validated['classId']) ? $validated['classId'] : null),
                    'student_id' => $validated['studentId'] ?? null,
                    'nis' => !empty($validated['nis']) ? trim($validated['nis']) : null,
                    'nisn' => !empty($validated['nisn']) ? trim($validated['nisn']) : null,
                    'gender' => $validated['gender'] ?? 'MALE',
                    'religion' => $validated['religion'] ?? null,
                    'is_active' => true,
                ]);

                if (!empty($validated['classId'])) {
                    \App\Models\ClassEnrollment::create([
                        'class_id' => $validated['classId'],
                        'student_id' => $user->id,
                        'created_at' => now(),
                    ]);
                }

                return response()->json($user->load(['school', 'enrollments.class']), 201);
            });
        } catch (\Illuminate\Database\QueryException $qe) {
            $msg = 'Data siswa (NIS, NISN, atau Email) sudah terdaftar pada siswa lain.';
            if (str_contains($qe->getMessage(), 'users_nis_key')) {
                $msg = 'NIS ini sudah terdaftar pada siswa lain.';
            } elseif (str_contains($qe->getMessage(), 'users_nisn_key')) {
                $msg = 'NISN ini sudah terdaftar pada siswa lain.';
            } elseif (str_contains($qe->getMessage(), 'users_email_key')) {
                $msg = 'Email ini sudah terdaftar pada akun lain.';
            }
            return response()->json([
                'message' => $msg,
                'errors' => ['general' => [$msg]]
            ], 422);
        }
    }

    public function me(Request $request)
    {
        return response()->json($request->user());
    }
}
