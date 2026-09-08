<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\School;
use App\Models\AcademicYear;
use App\Models\ClassModel;
use App\Models\ClassEnrollment;
use App\Models\CashAccount;
use App\Models\User;
use App\Models\DuesScheme;
use App\Models\StudentBilling;
use App\Models\Transaction;
use App\Models\Expense;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $passwordHash = Hash::make('Password123!');

        // 1. School
        $school = School::firstOrCreate(
            ['code' => 'SDN08PAGI'],
            [
                'name' => 'SD Negeri 08 PAGI',
                'address' => 'Jl. Merdeka No. 8, Jakarta',
                'phone' => '021-5550808',
            ]
        );

        $centralSchool = School::firstOrCreate(
            ['code' => 'sistemkas'],
            [
                'name' => 'Pusat Sistem Kas',
                'address' => 'Gedung Pusat Administrasi, Jakarta',
                'phone' => '021-8889999',
            ]
        );

        // 2. Academic Year
        $academicYear = AcademicYear::firstOrCreate(
            ['school_id' => $school->id, 'year' => '2026/2027'],
            ['is_current' => true]
        );

        // 3. Class
        $class = ClassModel::firstOrCreate(
            ['academic_year_id' => $academicYear->id, 'name' => 'Kelas 5-A']
        );

        // 4. Cash Account
        $cashAccount = CashAccount::firstOrCreate(
            ['class_id' => $class->id],
            [
                'name' => 'Kas Utama Kelas 5-A',
                'current_balance' => 500000.00,
                'currency' => 'IDR',
            ]
        );

        // 5. System Admin
        User::updateOrCreate(
            ['email' => 'sysadmin@sistemkas.com'],
            [
                'school_id' => $centralSchool->id,
                'name' => 'System Admin',
                'password_hash' => $passwordHash,
                'role' => 'SUPER_ADMIN',
                'is_active' => true,
            ]
        );

        // 6. School Admin
        User::updateOrCreate(
            ['email' => 'admin@sdn08pagi.sch.id'],
            [
                'school_id' => $school->id,
                'name' => 'Budi Santoso',
                'password_hash' => $passwordHash,
                'role' => 'ADMIN',
                'is_active' => true,
            ]
        );

        // 7. Treasurer (Bendahara)
        $treasurer = User::updateOrCreate(
            ['email' => 'bendahara@sdn08pagi.sch.id'],
            [
                'school_id' => $school->id,
                'name' => 'Siti Rahma',
                'password_hash' => $passwordHash,
                'role' => 'TREASURER',
                'is_active' => true,
            ]
        );

        // 8. Korlas (Koordinator Kelas 5-A)
        $korlas = User::updateOrCreate(
            ['email' => 'korlas5a@sdn08pagi.sch.id'],
            [
                'school_id' => $school->id,
                'name' => 'Korlas 5-A',
                'password_hash' => $passwordHash,
                'role' => 'KORLAS',
                'managed_class' => $class->id,
                'is_active' => true,
            ]
        );

        // 9. Students
        $studentsData = [
            [
                'name' => 'Andi Wijaya',
                'email' => 'andi@sdn08pagi.sch.id',
                'nis' => '20260501',
                'nisn' => '0051234567',
                'gender' => 'MALE',
                'religion' => 'ISLAM',
            ],
            [
                'name' => 'Dewi Lestari',
                'email' => 'dewi@sdn08pagi.sch.id',
                'nis' => '20260502',
                'nisn' => '0051234568',
                'gender' => 'FEMALE',
                'religion' => 'CHRISTIAN',
            ],
            [
                'name' => 'Rian Pratama',
                'email' => 'rian@sdn08pagi.sch.id',
                'nis' => '20260503',
                'nisn' => '0051234569',
                'gender' => 'MALE',
                'religion' => 'HINDU',
            ],
        ];

        $createdStudents = [];
        foreach ($studentsData as $s) {
            $student = User::updateOrCreate(
                ['email' => $s['email']],
                [
                    'school_id' => $school->id,
                    'name' => $s['name'],
                    'nis' => $s['nis'],
                    'nisn' => $s['nisn'],
                    'gender' => $s['gender'],
                    'religion' => $s['religion'],
                    'password_hash' => $passwordHash,
                    'role' => 'STUDENT',
                    'is_active' => true,
                ]
            );

            ClassEnrollment::firstOrCreate([
                'class_id' => $class->id,
                'student_id' => $student->id,
            ]);

            $createdStudents[] = $student;
        }

        // 10. Parent (Wali Murid Andi Wijaya)
        $andiStudent = $createdStudents[0] ?? null;
        if ($andiStudent) {
            User::updateOrCreate(
                ['email' => 'ortu.andi@sdn08pagi.sch.id'],
                [
                    'school_id' => $school->id,
                    'name' => 'Wali Murid Andi Wijaya',
                    'password_hash' => $passwordHash,
                    'role' => 'PARENT',
                    'student_id' => $andiStudent->id,
                    'is_active' => true,
                ]
            );
        }

        // 11. Initial Dues Scheme
        $scheme = DuesScheme::firstOrCreate(
            ['cash_account_id' => $cashAccount->id, 'title' => 'Iuran Kas Bulanan September 2026'],
            [
                'amount' => 20000.00,
                'due_date' => now()->addDays(20),
            ]
        );

        // 12. Student Billings
        foreach ($createdStudents as $idx => $st) {
            $status = $idx === 0 ? 'PAID' : 'PENDING';
            $paid = $idx === 0 ? 20000.00 : 0.00;

            $billing = StudentBilling::firstOrCreate(
                ['dues_scheme_id' => $scheme->id, 'student_id' => $st->id],
                [
                    'amount_due' => 20000.00,
                    'amount_paid' => $paid,
                    'status' => $status,
                    'due_date' => now()->addDays(20),
                ]
            );

            if ($status === 'PAID') {
                Transaction::firstOrCreate(
                    ['billing_id' => $billing->id],
                    [
                        'cash_account_id' => $cashAccount->id,
                        'type' => 'INCOME',
                        'amount' => 20000.00,
                        'category' => 'Iuran Kas',
                        'description' => "Pembayaran Iuran Kas September 2026 - {$st->name}",
                        'created_by' => $treasurer->id,
                        'created_at' => now(),
                    ]
                );
            }
        }

        // 13. Sample Expense
        Expense::firstOrCreate(
            ['cash_account_id' => $cashAccount->id, 'title' => 'Spidol & Penghapus Whiteboard'],
            [
                'amount' => 45000.00,
                'description' => 'Pembelian 3 pcs spidol snowman dan 1 penghapus papan tulis untuk ruang kelas 5-A',
                'approval_status' => 'PENDING',
                'requested_by' => $treasurer->id,
            ]
        );
    }
}
