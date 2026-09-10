<?php

namespace App\Services;

use FPDF;

class PurePdfReportService extends FPDF
{
    public string $schoolName = 'SISTEM KAS SEKOLAH';
    public string $reportTitle = 'LAPORAN KEUANGAN KAS';

    public function Header(): void
    {
        $isLandscape = ($this->DefOrientation === 'L' || $this->CurOrientation === 'L');
        $lineWidth = $isLandscape ? 277 : 190;

        $this->SetFont('Arial', 'B', 14);
        $this->SetTextColor(15, 23, 42);
        $this->Cell(0, 7, strtoupper($this->schoolName), 0, 1, 'C');

        $this->SetFont('Arial', 'B', 11);
        $this->SetTextColor(79, 70, 229);
        $this->Cell(0, 6, $this->reportTitle, 0, 1, 'C');

        $this->SetFont('Arial', '', 8);
        $this->SetTextColor(100, 116, 139);
        $this->Cell(0, 5, 'Dicetak pada: ' . date('d F Y, H:i') . ' WIB | Dokumen Resmi Sistem Kas Sekolah Terpadu', 0, 1, 'C');

        $this->SetDrawColor(15, 23, 42);
        $this->SetLineWidth(0.4);
        $this->Line(10, $this->GetY() + 2, 10 + $lineWidth, $this->GetY() + 2);
        $this->Ln(5);
    }

    public function Footer(): void
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(148, 163, 184);
        $this->Cell(0, 10, 'Halaman ' . $this->PageNo() . ' dari {nb} - Sistem Kas Sekolah', 0, 0, 'C');
    }

    public static function generateReport(string $type, string $schoolName, array $summary, array $items): string
    {
        $isDues = (strtoupper($type) === 'DUES');
        $pdf = new self('P', 'mm', 'A4');
        $pdf->schoolName = $schoolName ?: 'SISTEM KAS SEKOLAH';
        $pdf->reportTitle = $isDues ? 'LAPORAN REKAPITULASI IURAN SISWA' : 'LAPORAN BUKU BESAR MUTASI KAS';
        $pdf->AliasNbPages();
        $pdf->AddPage();

        // 1. Executive Summary Cards
        $pdf->SetFillColor(248, 250, 252);
        $pdf->SetDrawColor(226, 232, 240);
        $pdf->SetLineWidth(0.2);

        $cardW = 60;
        $cardH = 16;
        $startX = 15;

        if ($isDues) {
            $cards = [
                ['label' => 'TOTAL TAGIHAN', 'val' => 'Rp ' . number_format($summary['totalExpected'] ?? 0, 0, ',', '.'), 'color' => [15, 23, 42]],
                ['label' => 'SUDAH TERKUMPUL', 'val' => 'Rp ' . number_format($summary['totalCollected'] ?? 0, 0, ',', '.'), 'color' => [22, 163, 74]],
                ['label' => 'BELUM LUNAS', 'val' => 'Rp ' . number_format($summary['totalPending'] ?? 0, 0, ',', '.'), 'color' => [220, 38, 38]],
            ];
        } else {
            $cards = [
                ['label' => 'TOTAL PEMASUKAN', 'val' => 'Rp ' . number_format($summary['totalIncome'] ?? 0, 0, ',', '.'), 'color' => [22, 163, 74]],
                ['label' => 'TOTAL PENGELUARAN', 'val' => 'Rp ' . number_format($summary['totalExpense'] ?? 0, 0, ',', '.'), 'color' => [220, 38, 38]],
                ['label' => 'SISA SALDO KAS', 'val' => 'Rp ' . number_format($summary['balance'] ?? 0, 0, ',', '.'), 'color' => [79, 70, 229]],
            ];
        }

        $y = $pdf->GetY();
        foreach ($cards as $i => $c) {
            $x = $startX + ($i * 62);
            $pdf->Rect($x, $y, $cardW, $cardH, 'DF');

            $pdf->SetXY($x, $y + 2);
            $pdf->SetFont('Arial', 'B', 7);
            $pdf->SetTextColor(100, 116, 139);
            $pdf->Cell($cardW, 4, $c['label'], 0, 1, 'C');

            $pdf->SetXY($x, $y + 7);
            $pdf->SetFont('Arial', 'B', 11);
            $pdf->SetTextColor($c['color'][0], $c['color'][1], $c['color'][2]);
            $pdf->Cell($cardW, 7, $c['val'], 0, 1, 'C');
        }

        $pdf->SetY($y + $cardH + 6);

        // 2. Data Table
        $pdf->SetFillColor(79, 70, 229);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetDrawColor(67, 56, 202);
        $pdf->SetFont('Arial', 'B', 8);

        if ($isDues) {
            $cols = [
                ['w' => 10, 'title' => 'NO', 'align' => 'C'],
                ['w' => 50, 'title' => 'NAMA SISWA', 'align' => 'L'],
                ['w' => 45, 'title' => 'PROGRAM IURAN', 'align' => 'L'],
                ['w' => 25, 'title' => 'JATUH TEMPO', 'align' => 'C'],
                ['w' => 30, 'title' => 'NOMINAL', 'align' => 'R'],
                ['w' => 30, 'title' => 'STATUS', 'align' => 'C'],
            ];
        } else {
            $cols = [
                ['w' => 10, 'title' => 'NO', 'align' => 'C'],
                ['w' => 25, 'title' => 'TANGGAL', 'align' => 'C'],
                ['w' => 20, 'title' => 'TIPE', 'align' => 'C'],
                ['w' => 35, 'title' => 'KATEGORI', 'align' => 'L'],
                ['w' => 70, 'title' => 'KETERANGAN', 'align' => 'L'],
                ['w' => 30, 'title' => 'JUMLAH', 'align' => 'R'],
            ];
        }

        foreach ($cols as $col) {
            $pdf->Cell($col['w'], 7, $col['title'], 1, 0, 'C', true);
        }
        $pdf->Ln();

        // Data Rows
        $pdf->SetFont('Arial', '', 8);
        $pdf->SetTextColor(30, 41, 59);
        $pdf->SetDrawColor(226, 232, 240);

        if (!empty($items)) {
            $fill = false;
            foreach ($items as $idx => $it) {
                if ($pdf->GetY() > 250) {
                    $pdf->AddPage();
                    // Redraw header
                    $pdf->SetFont('Arial', 'B', 8);
                    $pdf->SetFillColor(79, 70, 229);
                    $pdf->SetTextColor(255, 255, 255);
                    foreach ($cols as $col) {
                        $pdf->Cell($col['w'], 7, $col['title'], 1, 0, 'C', true);
                    }
                    $pdf->Ln();
                    $pdf->SetFont('Arial', '', 8);
                    $pdf->SetTextColor(30, 41, 59);
                }

                $pdf->SetFillColor($fill ? 250 : 255, $fill ? 250 : 255, $fill ? 252 : 255);
                $no = $idx + 1;

                if ($isDues) {
                    $sName = substr($it['student_name'] ?? 'Siswa', 0, 25);
                    $sTitle = substr($it['title'] ?? 'Iuran Kas', 0, 22);
                    $sDue = $it['due_date'] ? date('d/m/Y', strtotime($it['due_date'])) : '-';
                    $sAmt = 'Rp ' . number_format($it['amount'] ?? 0, 0, ',', '.');
                    $isPaid = ($it['status'] ?? '') === 'PAID';
                    $sStatus = $isPaid ? 'LUNAS' : 'BELUM LUNAS';

                    $pdf->Cell(10, 6, $no, 1, 0, 'C', true);
                    $pdf->Cell(50, 6, $sName, 1, 0, 'L', true);
                    $pdf->Cell(45, 6, $sTitle, 1, 0, 'L', true);
                    $pdf->Cell(25, 6, $sDue, 1, 0, 'C', true);
                    $pdf->Cell(30, 6, $sAmt, 1, 0, 'R', true);

                    if ($isPaid) {
                        $pdf->SetTextColor(22, 163, 74);
                    } else {
                        $pdf->SetTextColor(220, 38, 38);
                    }
                    $pdf->SetFont('Arial', 'B', 7);
                    $pdf->Cell(30, 6, $sStatus, 1, 0, 'C', true);
                    $pdf->SetFont('Arial', '', 8);
                    $pdf->SetTextColor(30, 41, 59);
                } else {
                    $tDate = $it['date'] ? date('d/m/Y', strtotime($it['date'])) : '-';
                    $isInc = ($it['type'] ?? '') === 'INCOME';
                    $tType = $isInc ? 'MASUK' : 'KELUAR';
                    $tCat = substr($it['category'] ?? '-', 0, 18);
                    $tDesc = substr($it['description'] ?? '-', 0, 36);
                    $tAmt = 'Rp ' . number_format($it['amount'] ?? 0, 0, ',', '.');

                    $pdf->Cell(10, 6, $no, 1, 0, 'C', true);
                    $pdf->Cell(25, 6, $tDate, 1, 0, 'C', true);

                    if ($isInc) {
                        $pdf->SetTextColor(22, 163, 74);
                    } else {
                        $pdf->SetTextColor(220, 38, 38);
                    }
                    $pdf->SetFont('Arial', 'B', 7);
                    $pdf->Cell(20, 6, $tType, 1, 0, 'C', true);
                    $pdf->SetFont('Arial', '', 8);
                    $pdf->SetTextColor(30, 41, 59);

                    $pdf->Cell(35, 6, $tCat, 1, 0, 'L', true);
                    $pdf->Cell(70, 6, $tDesc, 1, 0, 'L', true);
                    $pdf->Cell(30, 6, $tAmt, 1, 0, 'R', true);
                }

                $pdf->Ln();
                $fill = !$fill;
            }
        } else {
            $pdf->Cell(190, 10, 'Tidak ada transaksi atau data iuran untuk dicetak.', 1, 1, 'C');
        }

        // 3. Signatures Section
        if ($pdf->GetY() > 230) {
            $pdf->AddPage();
        }
        $pdf->Ln(8);
        $sigY = $pdf->GetY();
        $pdf->SetFont('Arial', '', 9);
        $pdf->SetTextColor(30, 41, 59);

        // Left signature
        $pdf->SetXY(20, $sigY);
        $pdf->Cell(70, 4, 'Mengetahui,', 0, 1, 'C');
        $pdf->SetX(20);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(70, 4, 'Kepala Sekolah', 0, 1, 'C');
        $pdf->SetXY(20, $sigY + 22);
        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell(70, 4, '________________________', 0, 1, 'C');
        $pdf->SetX(20);
        $pdf->Cell(70, 4, 'NIP. .....................................', 0, 1, 'C');

        // Right signature
        $pdf->SetXY(120, $sigY);
        $pdf->Cell(70, 4, 'Diverifikasi Oleh,', 0, 1, 'C');
        $pdf->SetX(120);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(70, 4, 'Bendahara Sekolah', 0, 1, 'C');
        $pdf->SetXY(120, $sigY + 22);
        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell(70, 4, '________________________', 0, 1, 'C');
        $pdf->SetX(120);
        $pdf->Cell(70, 4, 'NIP. .....................................', 0, 1, 'C');

        return $pdf->Output('S');
    }

    public static function generateClassMatrixReport(string $schoolName, string $className, int $year, array $summary, array $matrixData): string
    {
        $pdf = new self('L', 'mm', 'A4'); // 297mm x 210mm
        $pdf->schoolName = $schoolName ?: 'SISTEM KAS SEKOLAH';
        $pdf->reportTitle = "LAPORAN REKAPITULASI IURAN SISWA ({$className}) - TAHUN {$year}";
        $pdf->AliasNbPages();
        $pdf->AddPage();

        // 1. Executive Summary Cards (Landscape: 3 cards across 277mm)
        $pdf->SetFillColor(248, 250, 252);
        $pdf->SetDrawColor(226, 232, 240);
        $pdf->SetLineWidth(0.2);

        $cardW = 89;
        $cardH = 15;
        $startX = 10;

        $cards = [
            ['label' => 'TOTAL SISWA TERDAFTAR', 'val' => (string)($summary['totalStudents'] ?? count($matrixData)) . ' Siswa', 'color' => [15, 23, 42]],
            ['label' => 'TOTAL IURAN TERBAYAR (LUNAS)', 'val' => 'Rp ' . number_format($summary['totalPaid'] ?? 0, 0, ',', '.'), 'color' => [22, 163, 74]],
            ['label' => 'TOTAL SISA TUNGGAKAN', 'val' => 'Rp ' . number_format($summary['totalPending'] ?? 0, 0, ',', '.'), 'color' => [220, 38, 38]],
        ];

        $y = $pdf->GetY();
        foreach ($cards as $i => $c) {
            $x = $startX + ($i * 94);
            $pdf->Rect($x, $y, $cardW, $cardH, 'DF');

            $pdf->SetXY($x, $y + 2);
            $pdf->SetFont('Arial', 'B', 7);
            $pdf->SetTextColor(100, 116, 139);
            $pdf->Cell($cardW, 4, $c['label'], 0, 1, 'C');

            $pdf->SetXY($x, $y + 6);
            $pdf->SetFont('Arial', 'B', 11);
            $pdf->SetTextColor($c['color'][0], $c['color'][1], $c['color'][2]);
            $pdf->Cell($cardW, 7, $c['val'], 0, 1, 'C');
        }

        $pdf->SetY($y + $cardH + 5);

        // 2. Table Header
        $pdf->SetFillColor(79, 70, 229);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetDrawColor(67, 56, 202);
        $pdf->SetFont('Arial', 'B', 7);

        // Column widths total: 8 + 38 + 19 + (12 * 14 = 168) + 23 + 21 = 277mm
        $cols = [
            ['w' => 8, 'title' => 'NO'],
            ['w' => 38, 'title' => 'NAMA SISWA'],
            ['w' => 19, 'title' => 'KELAS'],
            ['w' => 14, 'title' => 'JAN'],
            ['w' => 14, 'title' => 'FEB'],
            ['w' => 14, 'title' => 'MAR'],
            ['w' => 14, 'title' => 'APR'],
            ['w' => 14, 'title' => 'MEI'],
            ['w' => 14, 'title' => 'JUN'],
            ['w' => 14, 'title' => 'JUL'],
            ['w' => 14, 'title' => 'AGS'],
            ['w' => 14, 'title' => 'SEP'],
            ['w' => 14, 'title' => 'OKT'],
            ['w' => 14, 'title' => 'NOV'],
            ['w' => 14, 'title' => 'DES'],
            ['w' => 23, 'title' => 'TOTAL BAYAR'],
            ['w' => 21, 'title' => 'TUNGGAKAN'],
        ];

        foreach ($cols as $col) {
            $pdf->Cell($col['w'], 7, $col['title'], 1, 0, 'C', true);
        }
        $pdf->Ln();

        // 3. Table Rows
        $pdf->SetFont('Arial', '', 6.5);
        $pdf->SetTextColor(30, 41, 59);
        $pdf->SetDrawColor(226, 232, 240);

        if (!empty($matrixData)) {
            $fill = false;
            foreach ($matrixData as $idx => $row) {
                if ($pdf->GetY() > 175) {
                    $pdf->AddPage();
                    $pdf->SetFont('Arial', 'B', 7);
                    $pdf->SetFillColor(79, 70, 229);
                    $pdf->SetTextColor(255, 255, 255);
                    foreach ($cols as $col) {
                        $pdf->Cell($col['w'], 7, $col['title'], 1, 0, 'C', true);
                    }
                    $pdf->Ln();
                    $pdf->SetFont('Arial', '', 6.5);
                    $pdf->SetTextColor(30, 41, 59);
                }

                $pdf->SetFillColor($fill ? 250 : 255, $fill ? 250 : 255, $fill ? 252 : 255);
                $no = $idx + 1;
                $sName = substr($row['student_name'] ?? 'Siswa', 0, 24);
                $cName = substr($row['class_name'] ?? 'Kelas', 0, 12);

                $pdf->Cell(8, 6, $no, 1, 0, 'C', true);
                $pdf->Cell(38, 6, $sName, 1, 0, 'L', true);
                $pdf->Cell(19, 6, $cName, 1, 0, 'C', true);

                // 12 Months
                $months = $row['months'] ?? [];
                for ($m = 1; $m <= 12; $m++) {
                    $mCell = $months[$m] ?? ['status' => 'NONE', 'paid' => 0, 'due' => 0];
                    $st = $mCell['status'] ?? 'NONE';

                    if ($st === 'PAID') {
                        $pdf->SetTextColor(22, 163, 74);
                        $amtText = number_format(($mCell['paid'] ?? 0) / 1000, 0) . 'k';
                        $pdf->Cell(14, 6, $amtText, 1, 0, 'C', true);
                    } elseif ($st === 'UNPAID') {
                        $pdf->SetTextColor(220, 38, 38);
                        $amtPending = ($mCell['due'] ?? 0) - ($mCell['paid'] ?? 0);
                        $amtText = '-' . number_format($amtPending / 1000, 0) . 'k';
                        $pdf->Cell(14, 6, $amtText, 1, 0, 'C', true);
                    } else {
                        $pdf->SetTextColor(148, 163, 184);
                        $pdf->Cell(14, 6, '-', 1, 0, 'C', true);
                    }
                    $pdf->SetTextColor(30, 41, 59);
                }

                // Total Paid & Total Pending
                $totPaid = number_format($row['total_paid'] ?? 0, 0, ',', '.');
                $totPending = number_format($row['total_pending'] ?? 0, 0, ',', '.');

                $pdf->SetFont('Arial', 'B', 6.5);
                $pdf->SetTextColor(22, 163, 74);
                $pdf->Cell(23, 6, $totPaid, 1, 0, 'R', true);

                $pdf->SetTextColor($row['total_pending'] > 0 ? 220 : 100, $row['total_pending'] > 0 ? 38 : 116, $row['total_pending'] > 0 ? 38 : 139);
                $pdf->Cell(21, 6, $row['total_pending'] > 0 ? '-' . $totPending : '0', 1, 0, 'R', true);

                $pdf->SetFont('Arial', '', 6.5);
                $pdf->SetTextColor(30, 41, 59);
                $pdf->Ln();
                $fill = !$fill;
            }
        } else {
            $pdf->Cell(277, 10, 'Tidak ada data siswa untuk dicetak.', 1, 1, 'C');
        }

        // 4. Signatures (Landscape)
        if ($pdf->GetY() > 165) {
            $pdf->AddPage();
        }
        $pdf->Ln(6);
        $sigY = $pdf->GetY();
        $pdf->SetFont('Arial', '', 9);
        $pdf->SetTextColor(30, 41, 59);

        // Left signature
        $pdf->SetXY(35, $sigY);
        $pdf->Cell(70, 4, 'Mengetahui,', 0, 1, 'C');
        $pdf->SetX(35);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(70, 4, 'Kepala Sekolah', 0, 1, 'C');
        $pdf->SetXY(35, $sigY + 20);
        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell(70, 4, '________________________', 0, 1, 'C');
        $pdf->SetX(35);
        $pdf->Cell(70, 4, 'NIP. .....................................', 0, 1, 'C');

        // Right signature
        $pdf->SetXY(180, $sigY);
        $pdf->Cell(70, 4, 'Diverifikasi Oleh,', 0, 1, 'C');
        $pdf->SetX(180);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(70, 4, 'Bendahara Sekolah', 0, 1, 'C');
        $pdf->SetXY(180, $sigY + 20);
        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell(70, 4, '________________________', 0, 1, 'C');
        $pdf->SetX(180);
        $pdf->Cell(70, 4, 'NIP. .....................................', 0, 1, 'C');

        return $pdf->Output('S');
    }
}
