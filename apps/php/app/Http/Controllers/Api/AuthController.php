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
        $validated = $request->validate([
            'schoolId' => 'required|string',
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'nullable|string|min:6',
            'role' => 'nullable|string',
            'phone' => 'nullable|string',
            'managedClass' => 'nullable|string',
            'studentId' => 'nullable|string',
            'nis' => 'nullable|string',
            'nisn' => 'nullable|string',
            'gender' => 'nullable|string',
            'religion' => 'nullable|string',
            'classId' => 'nullable|string',
        ]);

        $plainPassword = !empty($validated['password']) ? $validated['password'] : bin2hex(random_bytes(5));

        $user = User::create([
            'school_id' => $validated['schoolId'],
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password_hash' => Hash::make($plainPassword),
            'role' => $validated['role'] ?? 'STUDENT',
            'phone' => $validated['phone'] ?? null,
            'managed_class' => $validated['managedClass'] ?? null,
            'student_id' => $validated['studentId'] ?? null,
            'nis' => $validated['nis'] ?? null,
            'nisn' => $validated['nisn'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'religion' => $validated['religion'] ?? null,
            'is_active' => true,
        ]);

        if (!empty($validated['classId'])) {
            \App\Models\ClassEnrollment::create([
                'class_id' => $validated['classId'],
                'student_id' => $user->id,
            ]);
        }

        return response()->json($user->load(['school', 'enrollments.class']), 201);
    }

    public function me(Request $request)
    {
        return response()->json($request->user());
    }
}
