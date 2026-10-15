<?php

namespace App\Controllers\Sales;

use App\Controllers\BaseController;
use App\Models\VisitorLogModel;

class Dashboard extends BaseController
{
    public function index()
    {
        $visitorLogModel = new VisitorLogModel();

        // 1. Timezone Asia/Jakarta untuk menentukan hari ini, bulan ini, dan tahun ini
        $tz = new \DateTimeZone('Asia/Jakarta');
        $now = new \DateTime('now', $tz);
        $todayStr = $now->format('Y-m-d');
        $currentYear = (int) $now->format('Y');
        $currentYearMonth = $now->format('Y-m');

        // Pemetaan nama bulan dalam bahasa Indonesia
        $monthsShort = [
            1 => 'JAN', 2 => 'FEB', 3 => 'MAR', 4 => 'APR',
            5 => 'MEI', 6 => 'JUN', 7 => 'JUL', 8 => 'AGU',
            9 => 'SEP', 10 => 'OKT', 11 => 'NOV', 12 => 'DES'
        ];

        $monthsFull = [
            1 => 'JANUARI', 2 => 'FEBRUARI', 3 => 'MARET', 4 => 'APRIL',
            5 => 'MEI', 6 => 'JUNI', 7 => 'JULI', 8 => 'AGUSTUS',
            9 => 'SEPTEMBER', 10 => 'OKTOBER', 11 => 'NOVEMBER', 12 => 'DESEMBER'
        ];

        // 2. Baca parameter GET
        $modeParam = $this->request->getGet('mode');
        $tanggalParam = $this->request->getGet('tanggal');
        $bulanParam = $this->request->getGet('bulan');
        $tahunParam = $this->request->getGet('tahun');

        $hasError = false;
        $errorMessage = null;

        // Default jika form tidak disubmit (akses pertama / reset)
        if ($modeParam === null || $modeParam === '') {
            $activeMode = 'hari';
            $activeTanggal = $todayStr;
            $activeBulan = $currentYearMonth;
            $activeTahun = (string) $currentYear;
            $cardTitle = 'HARI INI (TOTAL)';
            $startDate = $todayStr;
            $endDate = $todayStr;
        } else {
            $mode = strtolower(trim((string) $modeParam));

            if ($mode === 'hari') {
                $activeMode = 'hari';
                $activeBulan = $currentYearMonth;
                $activeTahun = (string) $currentYear;

                $rawTanggal = is_string($tanggalParam) ? trim($tanggalParam) : '';
                $isValid = false;

                if ($rawTanggal !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $rawTanggal)) {
                    $parts = explode('-', $rawTanggal);
                    $y = (int) $parts[0];
                    $m = (int) $parts[1];
                    $d = (int) $parts[2];

                    // Validasi: tahun minimal 2020, tanggal valid, dan tidak di masa depan
                    if ($y >= 2020 && checkdate($m, $d, $y) && $rawTanggal <= $todayStr) {
                        $isValid = true;
                    }
                }

                if ($isValid) {
                    $activeTanggal = $rawTanggal;
                    $startDate = $rawTanggal;
                    $endDate = $rawTanggal;

                    $dtSel = \DateTime::createFromFormat('Y-m-d', $rawTanggal, $tz);
                    $dayStr = $dtSel ? $dtSel->format('d') : str_pad((string)$d, 2, '0', STR_PAD_LEFT);
                    $mNum = (int) ($dtSel ? $dtSel->format('n') : $m);
                    $mShort = $monthsShort[$mNum] ?? 'OKT';
                    $yStr = $dtSel ? $dtSel->format('Y') : (string)$y;
                    $cardTitle = strtoupper("{$dayStr} {$mShort} {$yStr} (TOTAL)");
                } else {
                    $hasError = true;
                    $errorMessage = 'Input tidak valid, menampilkan hari ini';
                    $activeTanggal = $todayStr;
                    $startDate = $todayStr;
                    $endDate = $todayStr;
                    $cardTitle = 'HARI INI (TOTAL)';
                }
            } elseif ($mode === 'bulan') {
                $activeMode = 'bulan';
                $activeTanggal = $todayStr;
                $activeTahun = (string) $currentYear;

                $rawBulan = is_string($bulanParam) ? trim($bulanParam) : '';
                $isValid = false;

                if ($rawBulan !== '' && preg_match('/^\d{4}-\d{2}$/', $rawBulan)) {
                    $parts = explode('-', $rawBulan);
                    $y = (int) $parts[0];
                    $m = (int) $parts[1];

                    // Validasi: tahun minimal 2020, bulan 1-12, dan tidak di masa depan
                    if ($y >= 2020 && $m >= 1 && $m <= 12 && $rawBulan <= $currentYearMonth) {
                        $isValid = true;
                    }
                }

                if ($isValid) {
                    $activeBulan = $rawBulan;
                    $startDate = $rawBulan . '-01';
                    $endDate = date('Y-m-t', strtotime($startDate));

                    $mFull = $monthsFull[$m] ?? 'OKTOBER';
                    $cardTitle = strtoupper("{$mFull} {$y} (TOTAL)");
                } else {
                    $hasError = true;
                    $errorMessage = 'Input tidak valid, menampilkan hari ini';
                    $activeBulan = $currentYearMonth;
                    $startDate = $todayStr;
                    $endDate = $todayStr;
                    $cardTitle = 'HARI INI (TOTAL)';
                }
            } elseif ($mode === 'tahun') {
                $activeMode = 'tahun';
                $activeTanggal = $todayStr;
                $activeBulan = $currentYearMonth;

                $rawTahun = is_string($tahunParam) ? trim($tahunParam) : '';
                $isValid = false;

                if ($rawTahun !== '' && preg_match('/^\d{4}$/', $rawTahun)) {
                    $y = (int) $rawTahun;

                    // Validasi: tahun minimal 2020 dan tidak di masa depan
                    if ($y >= 2020 && $y <= $currentYear) {
                        $isValid = true;
                    }
                }

                if ($isValid) {
                    $activeTahun = (string) $y;
                    $startDate = "{$y}-01-01";
                    $endDate = "{$y}-12-31";
                    $cardTitle = "TAHUN {$y} (TOTAL)";
                } else {
                    $hasError = true;
                    $errorMessage = 'Input tidak valid, menampilkan hari ini';
                    $activeTahun = (string) $currentYear;
                    $startDate = $todayStr;
                    $endDate = $todayStr;
                    $cardTitle = 'HARI INI (TOTAL)';
                }
            } else {
                // Mode tidak dikenali
                $hasError = true;
                $errorMessage = 'Input tidak valid, menampilkan hari ini';
                $activeMode = 'hari';
                $activeTanggal = $todayStr;
                $activeBulan = $currentYearMonth;
                $activeTahun = (string) $currentYear;
                $startDate = $todayStr;
                $endDate = $todayStr;
                $cardTitle = 'HARI INI (TOTAL)';
            }
        }

        // 3. Hitung total kunjungan berdasarkan rentang tanggal menggunakan Query Builder (idx_visit_date)
        $totalVisits = $visitorLogModel->totalByRange($startDate, $endDate);

        $data = [
            'title'            => 'Dashboard Sales - Indonesian Exporter',
            'page_title'       => 'Dashboard',
            'activeMenu'       => 'dashboard',
            'cardTitle'        => $cardTitle,
            'totalVisits'      => $totalVisits,
            'activeMode'       => $activeMode,
            'activeTanggal'    => $activeTanggal,
            'activeBulan'      => $activeBulan,
            'activeTahun'      => $activeTahun,
            'todayStr'         => $todayStr,
            'currentYearMonth' => $currentYearMonth,
            'currentYear'      => $currentYear,
            'hasError'         => $hasError,
            'errorMessage'     => $errorMessage,
            'username'         => session()->get('username') ?? 'Sales',
        ];

        return view('sales/dashboard', $data);
    }
}
