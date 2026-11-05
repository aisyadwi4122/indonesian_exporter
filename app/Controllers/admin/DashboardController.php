<?php

namespace App\Controllers\admin;

use App\Controllers\BaseController;
use App\Models\ArtikelModel;
use App\Models\ProductModel;
use App\Models\SliderModel;
use App\Models\SalesActivityModel;
use CodeIgniter\HTTP\ResponseInterface;

class DashboardController extends BaseController
{
    public function index()
    {
        // Pengecekan apakah pengguna sudah login atau belum
        if (!session()->get('logged_in')) {
            return redirect()->to(base_url('login'));
        }

        // 1. Data 3 Kartu Lama (Tetap Dipertahankan)
        $produkModel  = new ProductModel();
        $sliderModel  = new SliderModel();
        $artikelModel = new ArtikelModel();

        $data['productCount'] = $produkModel->countAll();
        $data['sliderCount']  = $sliderModel->countAll();
        $data['artikelCount'] = $artikelModel->countAll();

        // 2. Filter Rentang Tanggal (Default: tanggal 1 sampai akhir bulan berjalan)
        $startDateStr = (string) $this->request->getGet('start_date');
        $endDateStr   = (string) $this->request->getGet('end_date');

        if (empty($startDateStr) || !strtotime($startDateStr)) {
            $startDate = date('Y-m-01');
        } else {
            $startDate = date('Y-m-d', strtotime($startDateStr));
        }

        if (empty($endDateStr) || !strtotime($endDateStr)) {
            $endDate = date('Y-m-t');
        } else {
            $endDate = date('Y-m-d', strtotime($endDateStr));
        }

        if ($startDate > $endDate) {
            $temp = $startDate;
            $startDate = $endDate;
            $endDate = $temp;
        }

        $startDateTime = $startDate . ' 00:00:00';
        $endDateTime   = $endDate . ' 23:59:59';

        // Hitung periode sebelumnya dengan panjang hari yang sama untuk badge tren
        $diffDays = (int) round((strtotime($endDate) - strtotime($startDate)) / 86400) + 1;
        $prevEndDate = date('Y-m-d', strtotime($startDate . ' -1 day'));
        $prevStartDate = date('Y-m-d', strtotime($prevEndDate . " -" . ($diffDays - 1) . " days"));
        $prevStartDateTime = $prevStartDate . ' 00:00:00';
        $prevEndDateTime   = $prevEndDate . ' 23:59:59';

        $activityModel = new SalesActivityModel();

        // 3. Hitung 5 Kartu Statistik & Tren
        // a. Total Pencarian
        $searchCountCurrent = $activityModel->getCountBetween('search_history', $startDateTime, $endDateTime);
        $searchCountPrev    = $activityModel->getCountBetween('search_history', $prevStartDateTime, $prevEndDateTime);
        $searchTrend        = $activityModel->calculateTrend($searchCountCurrent, $searchCountPrev);

        // b. Login Sales
        $loginCountCurrent = $activityModel->getCountBetween('login_history', $startDateTime, $endDateTime);
        $loginCountPrev    = $activityModel->getCountBetween('login_history', $prevStartDateTime, $prevEndDateTime);
        $loginTrend        = $activityModel->calculateTrend($loginCountCurrent, $loginCountPrev);

        // c. Klik Detail
        $detailCountCurrent = $activityModel->getCountBetween('detail_click_history', $startDateTime, $endDateTime);
        $detailCountPrev    = $activityModel->getCountBetween('detail_click_history', $prevStartDateTime, $prevEndDateTime);
        $detailTrend        = $activityModel->calculateTrend($detailCountCurrent, $detailCountPrev);

        // d. Klik Info Komisi
        $commCountCurrent = $activityModel->getCountBetween('commission_click_history', $startDateTime, $endDateTime);
        $commCountPrev    = $activityModel->getCountBetween('commission_click_history', $prevStartDateTime, $prevEndDateTime);
        $commTrend        = $activityModel->calculateTrend($commCountCurrent, $commCountPrev);

        // e. Cetak PDF
        $pdfCountCurrent = $activityModel->getCountBetween('pdf_history', $startDateTime, $endDateTime);
        $pdfCountPrev    = $activityModel->getCountBetween('pdf_history', $prevStartDateTime, $prevEndDateTime);
        $pdfTrend        = $activityModel->calculateTrend($pdfCountCurrent, $pdfCountPrev);

        $data['statCards'] = [
            'search' => [
                'title' => 'Total Pencarian',
                'count' => $searchCountCurrent,
                'trend' => $searchTrend,
            ],
            'login' => [
                'title' => 'Login Sales',
                'count' => $loginCountCurrent,
                'trend' => $loginTrend,
            ],
            'detail' => [
                'title' => 'Klik Detail',
                'count' => $detailCountCurrent,
                'trend' => $detailTrend,
            ],
            'commission' => [
                'title' => 'Klik Info Komisi',
                'count' => $commCountCurrent,
                'trend' => $commTrend,
            ],
            'pdf' => [
                'title' => 'Cetak PDF',
                'count' => $pdfCountCurrent,
                'trend' => $pdfTrend,
            ],
        ];

        // 4. Data Line Chart (Grafik Aktivitas Pengguna)
        $data['lineChartData'] = $activityModel->getDailySeries($startDate, $endDate);

        // 5. Data Donut Chart (Ringkasan Aktivitas)
        $totalActivities = $searchCountCurrent + $loginCountCurrent + $detailCountCurrent + $commCountCurrent + $pdfCountCurrent;
        $data['totalActivities'] = $totalActivities;
        $data['donutChartData'] = [
            'counts' => [
                $searchCountCurrent,
                $loginCountCurrent,
                $detailCountCurrent,
                $commCountCurrent,
                $pdfCountCurrent,
            ],
            'total'  => $totalActivities,
            'percentages' => [
                'search'     => $totalActivities > 0 ? round(($searchCountCurrent / $totalActivities) * 100, 1) : 0,
                'login'      => $totalActivities > 0 ? round(($loginCountCurrent / $totalActivities) * 100, 1) : 0,
                'detail'     => $totalActivities > 0 ? round(($detailCountCurrent / $totalActivities) * 100, 1) : 0,
                'commission' => $totalActivities > 0 ? round(($commCountCurrent / $totalActivities) * 100, 1) : 0,
                'pdf'        => $totalActivities > 0 ? round(($pdfCountCurrent / $totalActivities) * 100, 1) : 0,
            ],
        ];

        // 6. Data 5 Tabel Riwayat
        $data['searchHistory']          = $activityModel->getSearchHistory($startDateTime, $endDateTime, 5);
        $data['loginHistory']           = $activityModel->getLoginHistory($startDateTime, $endDateTime, 5);
        $data['detailClickHistory']     = $activityModel->getDetailClickHistory($startDateTime, $endDateTime, 5);
        $data['commissionClickHistory'] = $activityModel->getCommissionClickHistory($startDateTime, $endDateTime, 5);
        $data['pdfHistory']             = $activityModel->getPdfHistory($startDateTime, $endDateTime, 5);

        // Filter metadata
        $data['startDate'] = $startDate;
        $data['endDate']   = $endDate;

        return view('admin/dashboard/index', $data);
    }
}
