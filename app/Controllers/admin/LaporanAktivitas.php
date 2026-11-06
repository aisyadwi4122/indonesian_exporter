<?php

namespace App\Controllers\admin;

use App\Models\SalesActivityModel;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Controller Admin untuk Laporan Aktivitas Sales
 */
class LaporanAktivitas extends AdminAuthBase
{
    protected SalesActivityModel $activityModel;
    protected int $perPage = 10;

    public function __construct()
    {
        $this->activityModel = new SalesActivityModel();
    }

    /**
     * Helper untuk mengambil rentang tanggal default (tanggal 1 s/d akhir bulan berjalan)
     */
    protected function getDateRange(): array
    {
        $startDate = (string) ($this->request->getGet('start_date') ?? '');
        $endDate   = (string) ($this->request->getGet('end_date') ?? '');

        if (empty($startDate) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate)) {
            $startDate = date('Y-m-01');
        }
        if (empty($endDate) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) {
            $endDate = date('Y-m-t');
        }

        if ($startDate > $endDate) {
            $temp = $startDate;
            $startDate = $endDate;
            $endDate = $temp;
        }

        return [$startDate, $endDate];
    }

    /**
     * Format tanggal Indonesia: "31 Okt 2026, 14:20"
     */
    public static function formatTglIndo(?string $datetime): string
    {
        if (empty($datetime)) {
            return '-';
        }
        $timestamp = strtotime($datetime);
        if (!$timestamp) {
            return '-';
        }
        $bulanIndo = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun',
            7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'
        ];
        $d = date('d', $timestamp);
        $m = (int) date('m', $timestamp);
        $y = date('Y', $timestamp);
        $hi = date('H:i', $timestamp);
        return "{$d} {$bulanIndo[$m]} {$y}, {$hi}";
    }

    /**
     * Helper untuk membuat struktur pagination bernomor dengan ellipsis (...)
     */
    protected function generatePagination(int $page, int $totalPages, array $queryParams): array
    {
        $items = [];
        if ($totalPages <= 1) {
            return [
                'totalPages' => $totalPages,
                'page'       => $page,
                'items'      => [],
            ];
        }

        // Logic window pagination
        $pages = [];
        if ($totalPages <= 7) {
            for ($i = 1; $i <= $totalPages; $i++) {
                $pages[] = $i;
            }
        } else {
            $pages[] = 1;
            if ($page > 3) {
                $pages[] = '...';
            }

            $start = max(2, $page - 1);
            $end   = min($totalPages - 1, $page + 1);

            for ($i = $start; $i <= $end; $i++) {
                $pages[] = $i;
            }

            if ($page < $totalPages - 2) {
                $pages[] = '...';
            }
            $pages[] = $totalPages;
        }

        return [
            'totalPages' => $totalPages,
            'page'       => $page,
            'items'      => $pages,
            'queryParams'=> $queryParams,
        ];
    }

    // =========================================================================
    // 1. HISTORY PENCARIAN
    // =========================================================================

    public function pencarian()
    {
        if ($redirect = $this->checkAuth()) {
            return $redirect;
        }

        [$startDate, $endDate] = $this->getDateRange();
        $keyword = trim((string) ($this->request->getGet('keyword') ?? ''));
        $userId  = (string) ($this->request->getGet('user_id') ?? '');
        $page    = max(1, (int) ($this->request->getGet('page') ?? 1));

        $filters = [
            'start_date' => $startDate,
            'end_date'   => $endDate,
            'keyword'    => $keyword,
            'user_id'    => $userId,
        ];

        $total = $this->activityModel->countPencarian($filters);
        $totalPages = (int) ceil($total / $this->perPage);
        if ($page > $totalPages && $totalPages > 0) {
            $page = $totalPages;
        }
        $offset = ($page - 1) * $this->perPage;

        $items = $this->activityModel->getPencarianList($filters, $this->perPage, $offset);

        $data = [
            'title'       => 'History Pencarian - Laporan Aktivitas',
            'activeMenu'  => 'laporan-aktivitas',
            'activeSub'   => 'pencarian',
            'items'       => $items,
            'total'       => $total,
            'page'        => $page,
            'perPage'     => $this->perPage,
            'totalPages'  => $totalPages,
            'from'        => ($total > 0) ? $offset + 1 : 0,
            'to'          => min($offset + $this->perPage, $total),
            'filters'     => $filters,
            'salesList'   => $this->activityModel->getSalesOptions(),
            'pagination'  => $this->generatePagination($page, $totalPages, $filters),
        ];

        return view('admin/laporan_aktivitas/pencarian', $data);
    }

    public function exportPencarian()
    {
        if ($redirect = $this->checkAuth()) {
            return $redirect;
        }

        [$startDate, $endDate] = $this->getDateRange();
        $keyword = trim((string) ($this->request->getGet('keyword') ?? ''));
        $userId  = (string) ($this->request->getGet('user_id') ?? '');

        $filters = [
            'start_date' => $startDate,
            'end_date'   => $endDate,
            'keyword'    => $keyword,
            'user_id'    => $userId,
        ];

        $rows = $this->activityModel->getPencarianList($filters, 0, 0);

        $filename = 'history_pencarian_' . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $out = fopen('php://output', 'w');
        // UTF-8 BOM untuk Excel
        fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Header kolom CSV
        fputcsv($out, ['No', 'Waktu', 'Nama Sales', 'Kata Kunci', 'Jumlah Hasil']);

        $no = 1;
        foreach ($rows as $row) {
            fputcsv($out, [
                $no++,
                self::formatTglIndo($row['created_at']),
                $row['nama_sales'] ?? '-',
                $row['keyword'] ?? '-',
                $row['result_count'] ?? 0,
            ]);
        }

        fclose($out);
        exit;
    }

    // =========================================================================
    // 2. HISTORY LOGIN SALES
    // =========================================================================

    public function loginSales()
    {
        if ($redirect = $this->checkAuth()) {
            return $redirect;
        }

        [$startDate, $endDate] = $this->getDateRange();
        $searchSales = trim((string) ($this->request->getGet('search_sales') ?? ''));
        $page        = max(1, (int) ($this->request->getGet('page') ?? 1));

        $filters = [
            'start_date'   => $startDate,
            'end_date'     => $endDate,
            'search_sales' => $searchSales,
        ];

        $total = $this->activityModel->countLoginSales($filters);
        $totalPages = (int) ceil($total / $this->perPage);
        if ($page > $totalPages && $totalPages > 0) {
            $page = $totalPages;
        }
        $offset = ($page - 1) * $this->perPage;

        $items = $this->activityModel->getLoginSalesList($filters, $this->perPage, $offset);

        $data = [
            'title'       => 'History Login Sales - Laporan Aktivitas',
            'activeMenu'  => 'laporan-aktivitas',
            'activeSub'   => 'login-sales',
            'items'       => $items,
            'total'       => $total,
            'page'        => $page,
            'perPage'     => $this->perPage,
            'totalPages'  => $totalPages,
            'from'        => ($total > 0) ? $offset + 1 : 0,
            'to'          => min($offset + $this->perPage, $total),
            'filters'     => $filters,
            'pagination'  => $this->generatePagination($page, $totalPages, $filters),
        ];

        return view('admin/laporan_aktivitas/login_sales', $data);
    }

    public function exportLoginSales()
    {
        if ($redirect = $this->checkAuth()) {
            return $redirect;
        }

        [$startDate, $endDate] = $this->getDateRange();
        $searchSales = trim((string) ($this->request->getGet('search_sales') ?? ''));

        $filters = [
            'start_date'   => $startDate,
            'end_date'     => $endDate,
            'search_sales' => $searchSales,
        ];

        $rows = $this->activityModel->getLoginSalesList($filters, 0, 0);

        $filename = 'history_login_sales_' . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $out = fopen('php://output', 'w');
        fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));

        fputcsv($out, ['No', 'Nama Sales', 'Email', 'Waktu Login', 'IP Address', 'Status']);

        $no = 1;
        foreach ($rows as $row) {
            fputcsv($out, [
                $no++,
                $row['nama_sales'] ?? '-',
                $row['sales_email'] ?? '-',
                self::formatTglIndo($row['created_at']),
                $row['ip_address'] ?? '-',
                'Berhasil',
            ]);
        }

        fclose($out);
        exit;
    }

    // =========================================================================
    // 3. HISTORY DETAIL
    // =========================================================================

    public function detail()
    {
        if ($redirect = $this->checkAuth()) {
            return $redirect;
        }

        [$startDate, $endDate] = $this->getDateRange();
        $searchProduct = trim((string) ($this->request->getGet('search_product') ?? ''));
        $userId        = (string) ($this->request->getGet('user_id') ?? '');
        $companyName   = trim((string) ($this->request->getGet('company_name') ?? ''));
        $page          = max(1, (int) ($this->request->getGet('page') ?? 1));

        $filters = [
            'start_date'     => $startDate,
            'end_date'       => $endDate,
            'search_product' => $searchProduct,
            'user_id'        => $userId,
            'company_name'   => $companyName,
        ];

        $total = $this->activityModel->countDetail($filters);
        $totalPages = (int) ceil($total / $this->perPage);
        if ($page > $totalPages && $totalPages > 0) {
            $page = $totalPages;
        }
        $offset = ($page - 1) * $this->perPage;

        $items = $this->activityModel->getDetailList($filters, $this->perPage, $offset);

        $data = [
            'title'        => 'History Detail - Laporan Aktivitas',
            'activeMenu'   => 'laporan-aktivitas',
            'activeSub'    => 'detail',
            'items'        => $items,
            'total'        => $total,
            'page'         => $page,
            'perPage'      => $this->perPage,
            'totalPages'   => $totalPages,
            'from'         => ($total > 0) ? $offset + 1 : 0,
            'to'           => min($offset + $this->perPage, $total),
            'filters'      => $filters,
            'salesList'    => $this->activityModel->getSalesOptions(),
            'companyList'  => $this->activityModel->getCompanyOptions(),
            'pagination'   => $this->generatePagination($page, $totalPages, $filters),
        ];

        return view('admin/laporan_aktivitas/detail', $data);
    }

    public function exportDetail()
    {
        if ($redirect = $this->checkAuth()) {
            return $redirect;
        }

        [$startDate, $endDate] = $this->getDateRange();
        $searchProduct = trim((string) ($this->request->getGet('search_product') ?? ''));
        $userId        = (string) ($this->request->getGet('user_id') ?? '');
        $companyName   = trim((string) ($this->request->getGet('company_name') ?? ''));

        $filters = [
            'start_date'     => $startDate,
            'end_date'       => $endDate,
            'search_product' => $searchProduct,
            'user_id'        => $userId,
            'company_name'   => $companyName,
        ];

        $rows = $this->activityModel->getDetailList($filters, 0, 0);

        $filename = 'history_detail_' . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $out = fopen('php://output', 'w');
        fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));

        fputcsv($out, ['No', 'Waktu', 'Nama Sales', 'Nama Produk', 'Perusahaan']);

        $no = 1;
        foreach ($rows as $row) {
            fputcsv($out, [
                $no++,
                self::formatTglIndo($row['created_at']),
                $row['nama_sales'] ?? '-',
                $row['product_name'] ?? '-',
                $row['nama_perusahaan'] ?? '-',
            ]);
        }

        fclose($out);
        exit;
    }

    // =========================================================================
    // 4. HISTORY INFO KOMISI
    // =========================================================================

    public function infoKomisi()
    {
        if ($redirect = $this->checkAuth()) {
            return $redirect;
        }

        [$startDate, $endDate] = $this->getDateRange();
        $keyword = trim((string) ($this->request->getGet('keyword') ?? ''));
        $page    = max(1, (int) ($this->request->getGet('page') ?? 1));

        $filters = [
            'start_date' => $startDate,
            'end_date'   => $endDate,
            'keyword'    => $keyword,
        ];

        $total = $this->activityModel->countInfoKomisi($filters);
        $totalPages = (int) ceil($total / $this->perPage);
        if ($page > $totalPages && $totalPages > 0) {
            $page = $totalPages;
        }
        $offset = ($page - 1) * $this->perPage;

        $items = $this->activityModel->getInfoKomisiList($filters, $this->perPage, $offset);

        $data = [
            'title'       => 'History Info Komisi - Laporan Aktivitas',
            'activeMenu'  => 'laporan-aktivitas',
            'activeSub'   => 'info-komisi',
            'items'       => $items,
            'total'       => $total,
            'page'        => $page,
            'perPage'     => $this->perPage,
            'totalPages'  => $totalPages,
            'from'        => ($total > 0) ? $offset + 1 : 0,
            'to'          => min($offset + $this->perPage, $total),
            'filters'     => $filters,
            'pagination'  => $this->generatePagination($page, $totalPages, $filters),
        ];

        return view('admin/laporan_aktivitas/info_komisi', $data);
    }

    public function exportInfoKomisi()
    {
        if ($redirect = $this->checkAuth()) {
            return $redirect;
        }

        [$startDate, $endDate] = $this->getDateRange();
        $keyword = trim((string) ($this->request->getGet('keyword') ?? ''));

        $filters = [
            'start_date' => $startDate,
            'end_date'   => $endDate,
            'keyword'    => $keyword,
        ];

        $rows = $this->activityModel->getInfoKomisiList($filters, 0, 0);

        $filename = 'history_info_komisi_' . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $out = fopen('php://output', 'w');
        fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));

        fputcsv($out, ['No', 'Waktu', 'Nama Sales', 'Produk', 'Nominal Komisi']);

        $no = 1;
        foreach ($rows as $row) {
            fputcsv($out, [
                $no++,
                self::formatTglIndo($row['created_at']),
                $row['nama_sales'] ?? '-',
                $row['product_name'] ?? '-',
                $row['nominal_komisi'] ?? '-',
            ]);
        }

        fclose($out);
        exit;
    }

    // =========================================================================
    // 5. HISTORY PDF
    // =========================================================================

    public function pdf()
    {
        if ($redirect = $this->checkAuth()) {
            return $redirect;
        }

        [$startDate, $endDate] = $this->getDateRange();
        $keyword = trim((string) ($this->request->getGet('keyword') ?? ''));
        $userId  = (string) ($this->request->getGet('user_id') ?? '');
        $pdfType = trim((string) ($this->request->getGet('pdf_type') ?? ''));
        $page    = max(1, (int) ($this->request->getGet('page') ?? 1));

        $filters = [
            'start_date' => $startDate,
            'end_date'   => $endDate,
            'keyword'    => $keyword,
            'user_id'    => $userId,
            'pdf_type'   => $pdfType,
        ];

        $total = $this->activityModel->countPdf($filters);
        $totalPages = (int) ceil($total / $this->perPage);
        if ($page > $totalPages && $totalPages > 0) {
            $page = $totalPages;
        }
        $offset = ($page - 1) * $this->perPage;

        $items = $this->activityModel->getPdfList($filters, $this->perPage, $offset);

        $data = [
            'title'        => 'History PDF - Laporan Aktivitas',
            'activeMenu'   => 'laporan-aktivitas',
            'activeSub'    => 'pdf',
            'items'        => $items,
            'total'        => $total,
            'page'         => $page,
            'perPage'      => $this->perPage,
            'totalPages'   => $totalPages,
            'from'         => ($total > 0) ? $offset + 1 : 0,
            'to'           => min($offset + $this->perPage, $total),
            'filters'      => $filters,
            'salesList'    => $this->activityModel->getSalesOptions(),
            'pdfTypeList'  => $this->activityModel->getPdfTypeOptions(),
            'pagination'   => $this->generatePagination($page, $totalPages, $filters),
        ];

        return view('admin/laporan_aktivitas/pdf', $data);
    }

    public function exportPdf()
    {
        if ($redirect = $this->checkAuth()) {
            return $redirect;
        }

        [$startDate, $endDate] = $this->getDateRange();
        $keyword = trim((string) ($this->request->getGet('keyword') ?? ''));
        $userId  = (string) ($this->request->getGet('user_id') ?? '');
        $pdfType = trim((string) ($this->request->getGet('pdf_type') ?? ''));

        $filters = [
            'start_date' => $startDate,
            'end_date'   => $endDate,
            'keyword'    => $keyword,
            'user_id'    => $userId,
            'pdf_type'   => $pdfType,
        ];

        $rows = $this->activityModel->getPdfList($filters, 0, 0);

        $filename = 'history_pdf_' . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $out = fopen('php://output', 'w');
        fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));

        fputcsv($out, ['No', 'Waktu', 'Nama Sales', 'Nama Produk', 'Perusahaan', 'Jenis']);

        $no = 1;
        foreach ($rows as $row) {
            fputcsv($out, [
                $no++,
                self::formatTglIndo($row['created_at']),
                $row['nama_sales'] ?? '-',
                $row['product_name'] ?? '-',
                $row['nama_perusahaan'] ?? '-',
                $row['pdf_type'] ?? '-',
            ]);
        }

        fclose($out);
        exit;
    }
}
