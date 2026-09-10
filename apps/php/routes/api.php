<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SchoolController;
use App\Http\Controllers\Api\AcademicYearController;
use App\Http\Controllers\Api\ClassController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\CashAccountController;
use App\Http\Controllers\Api\BillingController;
use App\Http\Controllers\Api\TransactionController;

$registerRoutes = function () {
    // Auth
    Route::get('/auth/security-challenge', [AuthController::class, 'getSecurityChallenge']);
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::middleware('auth:sanctum')->get('/auth/me', [AuthController::class, 'me']);

    // Schools
    Route::get('/schools', [SchoolController::class, 'index']);
    Route::post('/schools', [SchoolController::class, 'store']);
    Route::put('/schools/{id}', [SchoolController::class, 'update']);
    Route::delete('/schools/{id}', [SchoolController::class, 'destroy']);

    // Academic Years
    Route::get('/academic-years', [AcademicYearController::class, 'index']);
    Route::post('/academic-years', [AcademicYearController::class, 'store']);
    Route::patch('/academic-years/{id}/current', [AcademicYearController::class, 'setCurrent']);

    // Classes
    Route::get('/classes', [ClassController::class, 'index']);
    Route::post('/classes', [ClassController::class, 'store']);
    Route::get('/classes/{id}', [ClassController::class, 'show']);

    // Users & Students
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/{id}', [UserController::class, 'show']);
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);

    // Cash Accounts
    Route::get('/cash-accounts/class/{classId}', [CashAccountController::class, 'getByClass']);
    Route::get('/cash-accounts/{id}', [CashAccountController::class, 'show']);

    // Billings & Dues
    Route::get('/billings', [BillingController::class, 'index']);
    Route::get('/billings/school/{schoolId}', [BillingController::class, 'getBySchool']);
    Route::get('/billings/student/{studentId}', [BillingController::class, 'getByStudent']);
    Route::get('/billings/class/{classId}', [BillingController::class, 'getByClass']);
    Route::get('/billings/scheme/{schemeId}', [BillingController::class, 'getByScheme']);
    Route::post('/billings/dues-scheme', [BillingController::class, 'createDuesScheme']);
    Route::post('/billings/{id}/pay', [BillingController::class, 'payBilling']);

    // Transactions & Expenses
    Route::get('/transactions', [TransactionController::class, 'index']);
    Route::get('/transactions/school/{schoolId}', [TransactionController::class, 'getBySchool']);
    Route::get('/transactions/account/{cashAccountId}', [TransactionController::class, 'getByAccount']);
    Route::post('/transactions', [TransactionController::class, 'store']);
    Route::get('/expenses', [TransactionController::class, 'getExpenses']);
    Route::post('/expenses', [TransactionController::class, 'createExpense']);
    Route::patch('/expenses/{id}/approval', [TransactionController::class, 'approveExpense']);

    // Email Notification Report with Attached PDF
    Route::post('/notifications/email-report', function (Request $request) {
        $validated = $request->validate([
            'email' => 'required|email',
            'reportTitle' => 'nullable|string',
            'reportType' => 'nullable|string',
            'schoolName' => 'nullable|string',
            'summary' => 'nullable|array',
            'items' => 'nullable|array',
        ]);

        $toEmail = $validated['email'];
        $title = $validated['reportTitle'] ?? 'Laporan Keuangan Kas Sekolah';
        $type = strtoupper($validated['reportType'] ?? 'LEDGER');
        $schoolName = $validated['schoolName'] ?? 'Sistem Kas Sekolah';
        $summary = $validated['summary'] ?? [];
        $items = $validated['items'] ?? [];
        $dateStr = date('d F Y, H:i');
        $datePrint = date('d F Y');

        try {
            $isClassMatrix = in_array($type, ['CLASS_MATRIX', 'CLASS_ANNUAL_REPORT']);
            $isDues = in_array($type, ['DUES', 'REPORTS', 'REPORT', 'BILLING', 'BILLINGS']);
            $className = $validated['className'] ?? ($summary['className'] ?? 'Semua Kelas');
            $year = (int)($validated['year'] ?? ($summary['year'] ?? date('Y')));

            if ($isClassMatrix) {
                $pdfTitle = "LAPORAN REKAPITULASI IURAN SISWA ({$className}) - TAHUN {$year}";
                $pdfFilename = 'Laporan-Rekap-Iuran-' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $className) . '-' . date('Ymd-His') . '.pdf';
            } else {
                $pdfTitle = $isDues ? 'LAPORAN REKAPITULASI IURAN SISWA' : 'LAPORAN BUKU BESAR MUTASI KAS';
                $pdfFilename = ($isDues ? 'Laporan-Iuran-Siswa-' : 'Laporan-Mutasi-Kas-') . date('Ymd-His') . '.pdf';
            }

            // Summary Section for PDF
            if ($isClassMatrix) {
                $totStud = ($summary['totalStudents'] ?? count($items)) . ' Siswa';
                $totPaid = 'Rp ' . number_format($summary['totalPaid'] ?? 0, 0, ',', '.');
                $totPen = 'Rp ' . number_format($summary['totalPending'] ?? 0, 0, ',', '.');
                $summaryHtml = "
                    <table style='width: 100%; margin-bottom: 12px;'>
                        <tr>
                            <td width='33%' style='padding-right: 5px;'>
                                <div style='background: #f8fafc; border: 1px solid #e2e8f0; padding: 8px; text-align: center; border-radius: 6px;'>
                                    <div style='font-size: 8px; color: #64748b; font-weight: bold; text-transform: uppercase;'>Total Siswa</div>
                                    <div style='font-size: 12px; font-weight: bold; color: #0f172a; margin-top: 2px;'>$totStud</div>
                                </div>
                            </td>
                            <td width='33%' style='padding: 0 5px;'>
                                <div style='background: #f8fafc; border: 1px solid #e2e8f0; padding: 8px; text-align: center; border-radius: 6px;'>
                                    <div style='font-size: 8px; color: #64748b; font-weight: bold; text-transform: uppercase;'>Total Terbayar (Lunas)</div>
                                    <div style='font-size: 12px; font-weight: bold; color: #16a34a; margin-top: 2px;'>$totPaid</div>
                                </div>
                            </td>
                            <td width='33%' style='padding-left: 5px;'>
                                <div style='background: #f8fafc; border: 1px solid #e2e8f0; padding: 8px; text-align: center; border-radius: 6px;'>
                                    <div style='font-size: 8px; color: #64748b; font-weight: bold; text-transform: uppercase;'>Total Sisa Tunggakan</div>
                                    <div style='font-size: 12px; font-weight: bold; color: #dc2626; margin-top: 2px;'>$totPen</div>
                                </div>
                            </td>
                        </tr>
                    </table>";
            } elseif ($isDues) {
                $totalExp = 'Rp ' . number_format($summary['totalExpected'] ?? 0, 0, ',', '.');
                $totalCol = 'Rp ' . number_format($summary['totalCollected'] ?? 0, 0, ',', '.');
                $totalPen = 'Rp ' . number_format($summary['totalPending'] ?? 0, 0, ',', '.');
                $summaryHtml = "
                    <table style='width: 100%; margin-bottom: 15px;'>
                        <tr>
                            <td width='33%' style='padding-right: 5px;'>
                                <div style='background: #f8fafc; border: 1px solid #e2e8f0; padding: 10px; text-align: center; border-radius: 6px;'>
                                    <div style='font-size: 9px; color: #64748b; font-weight: bold; text-transform: uppercase;'>Total Tagihan</div>
                                    <div style='font-size: 13px; font-weight: bold; color: #0f172a; margin-top: 4px;'>$totalExp</div>
                                </div>
                            </td>
                            <td width='33%' style='padding: 0 5px;'>
                                <div style='background: #f8fafc; border: 1px solid #e2e8f0; padding: 10px; text-align: center; border-radius: 6px;'>
                                    <div style='font-size: 9px; color: #64748b; font-weight: bold; text-transform: uppercase;'>Sudah Terkumpul</div>
                                    <div style='font-size: 13px; font-weight: bold; color: #16a34a; margin-top: 4px;'>$totalCol</div>
                                </div>
                            </td>
                            <td width='33%' style='padding-left: 5px;'>
                                <div style='background: #f8fafc; border: 1px solid #e2e8f0; padding: 10px; text-align: center; border-radius: 6px;'>
                                    <div style='font-size: 9px; color: #64748b; font-weight: bold; text-transform: uppercase;'>Belum Lunas</div>
                                    <div style='font-size: 13px; font-weight: bold; color: #dc2626; margin-top: 4px;'>$totalPen</div>
                                </div>
                            </td>
                        </tr>
                    </table>";
            } else {
                $totalInc = 'Rp ' . number_format($summary['totalIncome'] ?? 0, 0, ',', '.');
                $totalExp = 'Rp ' . number_format($summary['totalExpense'] ?? 0, 0, ',', '.');
                $balance = 'Rp ' . number_format($summary['balance'] ?? 0, 0, ',', '.');
                $summaryHtml = "
                    <table style='width: 100%; margin-bottom: 15px;'>
                        <tr>
                            <td width='33%' style='padding-right: 5px;'>
                                <div style='background: #f8fafc; border: 1px solid #e2e8f0; padding: 10px; text-align: center; border-radius: 6px;'>
                                    <div style='font-size: 9px; color: #64748b; font-weight: bold; text-transform: uppercase;'>Total Pemasukan</div>
                                    <div style='font-size: 13px; font-weight: bold; color: #16a34a; margin-top: 4px;'>$totalInc</div>
                                </div>
                            </td>
                            <td width='33%' style='padding: 0 5px;'>
                                <div style='background: #f8fafc; border: 1px solid #e2e8f0; padding: 10px; text-align: center; border-radius: 6px;'>
                                    <div style='font-size: 9px; color: #64748b; font-weight: bold; text-transform: uppercase;'>Total Pengeluaran</div>
                                    <div style='font-size: 13px; font-weight: bold; color: #dc2626; margin-top: 4px;'>$totalExp</div>
                                </div>
                            </td>
                            <td width='33%' style='padding-left: 5px;'>
                                <div style='background: #f8fafc; border: 1px solid #e2e8f0; padding: 10px; text-align: center; border-radius: 6px;'>
                                    <div style='font-size: 9px; color: #64748b; font-weight: bold; text-transform: uppercase;'>Sisa Saldo Kas</div>
                                    <div style='font-size: 13px; font-weight: bold; color: #4f46e5; margin-top: 4px;'>$balance</div>
                                </div>
                            </td>
                        </tr>
                    </table>";
            }

            // Table Rows for PDF
            $rowsHtml = '';
            if (!empty($items)) {
                foreach ($items as $idx => $it) {
                    $no = $idx + 1;
                    if ($isClassMatrix) {
                        $sName = htmlspecialchars($it['student_name'] ?? 'Siswa');
                        $cName = htmlspecialchars($it['class_name'] ?? '-');
                        $totRowPaid = 'Rp ' . number_format($it['total_paid'] ?? 0, 0, ',', '.');
                        $totRowPending = ($it['total_pending'] ?? 0) > 0 ? ('-Rp ' . number_format($it['total_pending'], 0, ',', '.')) : '0';
                        $pendingColor = ($it['total_pending'] ?? 0) > 0 ? 'color:#dc2626;' : 'color:#64748b;';

                        $mCols = '';
                        $months = $it['months'] ?? [];
                        for ($m = 1; $m <= 12; $m++) {
                            $mCell = $months[$m] ?? ['status' => 'NONE', 'paid' => 0, 'due' => 0];
                            $st = $mCell['status'] ?? 'NONE';
                            if ($st === 'PAID') {
                                $amtStr = number_format(($mCell['paid'] ?? 0) / 1000, 0) . 'k';
                                $mCols .= "<td style='padding:3px;border:1px solid #e2e8f0;text-align:center;color:#16a34a;font-weight:bold;'>$amtStr</td>";
                            } elseif ($st === 'UNPAID') {
                                $amtPending = ($mCell['due'] ?? 0) - ($mCell['paid'] ?? 0);
                                $amtStr = '-' . number_format($amtPending / 1000, 0) . 'k';
                                $mCols .= "<td style='padding:3px;border:1px solid #e2e8f0;text-align:center;color:#dc2626;font-weight:bold;'>$amtStr</td>";
                            } else {
                                $mCols .= "<td style='padding:3px;border:1px solid #e2e8f0;text-align:center;color:#94a3b8;'>-</td>";
                            }
                        }

                        $rowsHtml .= "<tr>
                            <td style='padding:4px;border:1px solid #e2e8f0;text-align:center;'>$no</td>
                            <td style='padding:4px;border:1px solid #e2e8f0;font-weight:bold;'>$sName</td>
                            <td style='padding:4px;border:1px solid #e2e8f0;text-align:center;'>$cName</td>
                            $mCols
                            <td style='padding:4px;border:1px solid #e2e8f0;text-align:right;color:#16a34a;font-weight:bold;'>$totRowPaid</td>
                            <td style='padding:4px;border:1px solid #e2e8f0;text-align:right;font-weight:bold;$pendingColor'>$totRowPending</td>
                        </tr>";
                    } elseif ($isDues) {
                        $sName = htmlspecialchars($it['student_name'] ?? 'Siswa');
                        $cName = htmlspecialchars($it['class_name'] ?? '-');
                        $sProg = htmlspecialchars($it['title'] ?? 'Iuran Kas');
                        $sDue = !empty($it['due_date']) ? date('d/m/Y', strtotime($it['due_date'])) : '-';
                        $sAmt = 'Rp ' . number_format($it['amount'] ?? 0, 0, ',', '.');
                        $isPaid = ($it['status'] ?? '') === 'PAID';
                        $statusBadge = $isPaid ? "<span style='background:#dcfce7;color:#166534;padding:3px 8px;border-radius:4px;font-weight:bold;font-size:9px;'>LUNAS</span>" : "<span style='background:#fee2e2;color:#991b1b;padding:3px 8px;border-radius:4px;font-weight:bold;font-size:9px;'>BELUM LUNAS</span>";
                        $rowsHtml .= "<tr>
                            <td style='padding:6px;border:1px solid #e2e8f0;text-align:center;'>$no</td>
                            <td style='padding:6px;border:1px solid #e2e8f0;font-weight:bold;'>$sName</td>
                            <td style='padding:6px;border:1px solid #e2e8f0;text-align:center;'>$cName</td>
                            <td style='padding:6px;border:1px solid #e2e8f0;'>$sProg</td>
                            <td style='padding:6px;border:1px solid #e2e8f0;text-align:center;'>$sDue</td>
                            <td style='padding:6px;border:1px solid #e2e8f0;text-align:right;'><b>$sAmt</b></td>
                            <td style='padding:6px;border:1px solid #e2e8f0;text-align:center;'>$statusBadge</td>
                        </tr>";
                    } else {
                        $tDate = !empty($it['date']) ? date('d/m/Y', strtotime($it['date'])) : (!empty($it['due_date']) ? date('d/m/Y', strtotime($it['due_date'])) : '-');
                        $tType = ($it['type'] ?? '') === 'INCOME' ? "<span style='background:#dcfce7;color:#166534;padding:2px 6px;border-radius:4px;font-weight:bold;font-size:9px;'>MASUK</span>" : "<span style='background:#fee2e2;color:#991b1b;padding:2px 6px;border-radius:4px;font-weight:bold;font-size:9px;'>KELUAR</span>";
                        $tCat = htmlspecialchars($it['category'] ?? '-');
                        $tDesc = htmlspecialchars($it['description'] ?? '-');
                        $tAmt = 'Rp ' . number_format($it['amount'] ?? 0, 0, ',', '.');
                        $rowsHtml .= "<tr>
                            <td style='padding:6px;border:1px solid #e2e8f0;text-align:center;'>$no</td>
                            <td style='padding:6px;border:1px solid #e2e8f0;text-align:center;'>$tDate</td>
                            <td style='padding:6px;border:1px solid #e2e8f0;text-align:center;'>$tType</td>
                            <td style='padding:6px;border:1px solid #e2e8f0;'>$tCat</td>
                            <td style='padding:6px;border:1px solid #e2e8f0;'>$tDesc</td>
                            <td style='padding:6px;border:1px solid #e2e8f0;text-align:right;'><b>$tAmt</b></td>
                        </tr>";
                    }
                }
            } else {
                $colspan = $isClassMatrix ? 17 : ($isDues ? 7 : 6);
                $rowsHtml = "<tr><td colspan='{$colspan}' style='padding:15px;text-align:center;color:#64748b;'>Tidak ada data transaksi atau iuran untuk dicetak.</td></tr>";
            }

            if ($isClassMatrix) {
                $tableHeaders = "
                    <th width='3%' style='background:#4f46e5;color:white;padding:6px 2px;border:1px solid #4338ca;'>No</th>
                    <th width='15%' style='background:#4f46e5;color:white;padding:6px 4px;border:1px solid #4338ca;'>Nama Siswa</th>
                    <th width='8%' style='background:#4f46e5;color:white;padding:6px 2px;border:1px solid #4338ca;'>Kelas</th>
                    <th width='5%' style='background:#4f46e5;color:white;padding:6px 1px;border:1px solid #4338ca;'>Jan</th>
                    <th width='5%' style='background:#4f46e5;color:white;padding:6px 1px;border:1px solid #4338ca;'>Feb</th>
                    <th width='5%' style='background:#4f46e5;color:white;padding:6px 1px;border:1px solid #4338ca;'>Mar</th>
                    <th width='5%' style='background:#4f46e5;color:white;padding:6px 1px;border:1px solid #4338ca;'>Apr</th>
                    <th width='5%' style='background:#4f46e5;color:white;padding:6px 1px;border:1px solid #4338ca;'>Mei</th>
                    <th width='5%' style='background:#4f46e5;color:white;padding:6px 1px;border:1px solid #4338ca;'>Jun</th>
                    <th width='5%' style='background:#4f46e5;color:white;padding:6px 1px;border:1px solid #4338ca;'>Jul</th>
                    <th width='5%' style='background:#4f46e5;color:white;padding:6px 1px;border:1px solid #4338ca;'>Ags</th>
                    <th width='5%' style='background:#4f46e5;color:white;padding:6px 1px;border:1px solid #4338ca;'>Sep</th>
                    <th width='5%' style='background:#4f46e5;color:white;padding:6px 1px;border:1px solid #4338ca;'>Okt</th>
                    <th width='5%' style='background:#4f46e5;color:white;padding:6px 1px;border:1px solid #4338ca;'>Nov</th>
                    <th width='5%' style='background:#4f46e5;color:white;padding:6px 1px;border:1px solid #4338ca;'>Des</th>
                    <th width='8%' style='background:#4f46e5;color:white;padding:6px 2px;border:1px solid #4338ca;'>Terbayar</th>
                    <th width='8%' style='background:#4f46e5;color:white;padding:6px 2px;border:1px solid #4338ca;'>Tunggakan</th>";
            } elseif ($isDues) {
                $tableHeaders = "
                    <th width='5%' style='background:#4f46e5;color:white;padding:8px 6px;border:1px solid #4338ca;'>No</th>
                    <th width='24%' style='background:#4f46e5;color:white;padding:8px 6px;border:1px solid #4338ca;'>Nama Siswa</th>
                    <th width='14%' style='background:#4f46e5;color:white;padding:8px 6px;border:1px solid #4338ca;'>Kelas</th>
                    <th width='22%' style='background:#4f46e5;color:white;padding:8px 6px;border:1px solid #4338ca;'>Program Tagihan</th>
                    <th width='12%' style='background:#4f46e5;color:white;padding:8px 6px;border:1px solid #4338ca;'>Jatuh Tempo</th>
                    <th width='12%' style='background:#4f46e5;color:white;padding:8px 6px;border:1px solid #4338ca;'>Nominal</th>
                    <th width='11%' style='background:#4f46e5;color:white;padding:8px 6px;border:1px solid #4338ca;'>Status</th>";
            } else {
                $tableHeaders = "
                    <th width='5%' style='background:#4f46e5;color:white;padding:8px 6px;border:1px solid #4338ca;'>No</th>
                    <th width='15%' style='background:#4f46e5;color:white;padding:8px 6px;border:1px solid #4338ca;'>Tanggal</th>
                    <th width='12%' style='background:#4f46e5;color:white;padding:8px 6px;border:1px solid #4338ca;'>Tipe</th>
                    <th width='18%' style='background:#4f46e5;color:white;padding:8px 6px;border:1px solid #4338ca;'>Kategori</th>
                    <th width='35%' style='background:#4f46e5;color:white;padding:8px 6px;border:1px solid #4338ca;'>Keterangan</th>
                    <th width='15%' style='background:#4f46e5;color:white;padding:8px 6px;border:1px solid #4338ca;'>Jumlah</th>";
            }

            $bodyFontSize = $isClassMatrix ? '9px' : '11px';
            $pdfHtml = "
            <!DOCTYPE html>
            <html>
            <head>
            <meta charset='utf-8'>
            <title>$pdfTitle</title>
            <style>
                @page { size: " . ($isClassMatrix ? 'A4 landscape' : 'A4 portrait') . "; margin: 10mm; }
                body { font-family: sans-serif; font-size: $bodyFontSize; color: #1e293b; margin: 0; padding: 6px; }
                .header { text-align: center; border-bottom: 2px solid #0f172a; padding-bottom: 6px; margin-bottom: 8px; }
                .school-name { font-size: 14px; font-weight: bold; text-transform: uppercase; color: #0f172a; }
                .report-title { font-size: 11px; font-weight: bold; margin-top: 3px; color: #4338ca; }
                .meta { font-size: 9px; color: #64748b; margin-top: 2px; }
                table.data { width: 100%; border-collapse: collapse; margin-top: 6px; }
                table.data tr:nth-child(even) { background: #fbfbfe; }
                .signatures { margin-top: 20px; width: 100%; }
                .sig-col { width: 50%; text-align: center; font-size: 10px; }
            </style>
            </head>
            <body>
                <div class='header'>
                    <div class='school-name'>$schoolName</div>
                    <div class='report-title'>$pdfTitle</div>
                    <div class='meta'>Dicetak pada: $datePrint | Dokumen Resmi Sistem Kas Sekolah Terpadu</div>
                </div>
                $summaryHtml
                <table class='data'>
                    <thead><tr>$tableHeaders</tr></thead>
                    <tbody>$rowsHtml</tbody>
                </table>
                <table class='signatures'>
                    <tr>
                        <td class='sig-col'>
                            Mengetahui,<br><b>Kepala Sekolah</b><br><br><br>
                            _______________________<br>
                            NIP. .........................
                        </td>
                        <td class='sig-col'>
                            Diverifikasi Oleh,<br><b>Bendahara Sekolah</b><br><br><br>
                            _______________________<br>
                            NIP. .........................
                        </td>
                    </tr>
                </table>
            </body>
            </html>";

            // Render PDF in memory (DomPDF with PurePdfReportService fallback)
            if (class_exists('DOMImplementation')) {
                $paperOrientation = $isClassMatrix ? 'landscape' : 'portrait';
                $pdf = Pdf::loadHTML($pdfHtml)->setPaper('a4', $paperOrientation);
                $pdfBytes = $pdf->output();
            } else {
                if ($isClassMatrix) {
                    $pdfBytes = \App\Services\PurePdfReportService::generateClassMatrixReport($schoolName, $className, $year, $summary, $items);
                } else {
                    $pdfBytes = \App\Services\PurePdfReportService::generateReport($type, $schoolName, $summary, $items);
                }
            }

            // Send Email with PDF Attachment
            Mail::send([], [], function ($message) use ($toEmail, $title, $type, $dateStr, $pdfBytes, $pdfFilename) {
                $message->to($toEmail)
                    ->subject($title)
                    ->attachData($pdfBytes, $pdfFilename, [
                        'mime' => 'application/pdf',
                    ])
                    ->html("
                        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; padding: 24px; border: 1px solid #e2e8f0; border-radius: 16px; background: #ffffff;'>
                            <div style='border-bottom: 2px solid #4f46e5; padding-bottom: 12px; margin-bottom: 16px;'>
                                <h2 style='color: #4f46e5; margin: 0; font-size: 20px;'>$title</h2>
                                <p style='color: #64748b; font-size: 13px; margin: 4px 0 0 0;'>Sistem Kas Sekolah Terpadu</p>
                            </div>
                            <p style='color: #334155; font-size: 14px; line-height: 1.5;'>Halo,</p>
                            <p style='color: #334155; font-size: 14px; line-height: 1.5;'>Berikut adalah lampiran dokumen PDF resmi laporan keuangan kas sekolah yang Anda minta:</p>
                            <div style='background-color: #f8fafc; border-left: 4px solid #4f46e5; padding: 14px 16px; margin: 20px 0; border-radius: 6px;'>
                                <p style='margin: 0; font-size: 13px; color: #1e293b;'><strong>Jenis Laporan:</strong> $type</p>
                                <p style='margin: 6px 0 0 0; font-size: 13px; color: #1e293b;'><strong>Tanggal Cetak:</strong> $dateStr WIB</p>
                                <p style='margin: 6px 0 0 0; font-size: 13px; color: #1e293b;'><strong>Lampiran File:</strong> 📎 <code>$pdfFilename</code></p>
                            </div>
                            <p style='color: #475569; font-size: 13px; line-height: 1.6;'>
                                📄 <strong>File PDF telah dilampirkan pada email ini.</strong> Anda dapat langsung mengunduh dan mencetak file laporan tersebut tanpa harus login ke aplikasi.
                            </p>
                            <hr style='border: none; border-top: 1px solid #f1f5f9; margin: 20px 0;'>
                            <p style='color: #94a3b8; font-size: 11px; text-align: center; margin: 0;'>Email otomatis dari Sistem Kas Sekolah &copy; " . date('Y') . " Arsys Caturangga</p>
                        </div>
                    ");
            });

            return response()->json([
                'status' => 'SUCCESS',
                'message' => "Laporan berhasil dikirim ke email {$toEmail} dengan lampiran PDF!"
            ]);
        } catch (\Throwable $e) {
            $msg = $e->getMessage();
            if (str_contains($msg, 'Connection could not be established') || str_contains($msg, 'Failed to authenticate on SMTP server') || str_contains($msg, 'Connection refused')) {
                $msg .= " (Saran: Periksa konfigurasi SMTP pada file .env cPanel. Pastikan MAIL_HOST=smtp.gmail.com, MAIL_PORT=587 atau 465, MAIL_USERNAME alamat gmail Anda, dan MAIL_PASSWORD menggunakan 16-karakter App Password Google).";
            }
            return response()->json([
                'status' => 'ERROR',
                'message' => 'Gagal mengirim email: ' . $msg
            ], 500);
        }
    });
};

// Available under /api/v1/... (identical to NestJS)
Route::prefix('v1')->group($registerRoutes);

// Also available directly under /api/... for flexibility
$registerRoutes();
