<?php

namespace App\Controllers\admin;

use App\Libraries\ActivityFilter;
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

    // =========================================================================
    // 6. LAPORAN PRODUK TERLARIS
    // =========================================================================

    public function produkTerlaris()
    {
        if ($redirect = $this->checkAuth()) {
            return $redirect;
        }

        $filters = ActivityFilter::parse($this->request);
        $page    = $filters['page'];
        $offset  = ($page - 1) * $this->perPage;

        $items = $this->activityModel->getProdukTerlarisList($filters, $this->perPage, $offset);
        $total = $this->activityModel->countProdukTerlaris($filters);

        $totalPages = (int) ceil($total / $this->perPage);
        $salesList  = $this->activityModel->getSalesOptions();

        $pagination = $this->generatePagination($page, $totalPages, $filters);

        $data = [
            'title'        => 'Laporan Produk Terlaris - Indonesian Exporter',
            'pageTitle'    => 'Produk Terlaris',
            'pageSubtitle' => 'Peringkat produk yang paling banyak diminati sales',
            'activeMenu'   => 'laporan-produk-terlaris',
            'filters'      => $filters,
            'items'        => $items,
            'total'        => $total,
            'page'         => $page,
            'totalPages'   => $totalPages,
            'from'         => $total > 0 ? $offset + 1 : 0,
            'to'           => min($offset + $this->perPage, $total),
            'salesList'    => $salesList,
            'pagination'   => $pagination,
        ];

        return view('admin/laporan_aktivitas/produk_terlaris', $data);
    }

    public function exportProdukTerlaris()
    {
        if ($redirect = $this->checkAuth()) {
            return $redirect;
        }

        $filters = ActivityFilter::parse($this->request);
        $rows    = $this->activityModel->getProdukTerlarisList($filters, 0, 0);

        $filename = 'laporan_produk_terlaris_' . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $out = fopen('php://output', 'w');
        fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));

        fputcsv($out, ['Peringkat', 'Nama Produk', 'Perusahaan', 'Klik Detail', 'Klik Info Komisi', 'Cetak PDF', 'Total Minat', 'Jumlah Sales']);

        $rank = 1;
        foreach ($rows as $row) {
            fputcsv($out, [
                $rank++,
                $row['nama_produk'] ?? '-',
                $row['nama_perusahaan'] ?? '-',
                (int) ($row['click_detail'] ?? 0),
                (int) ($row['click_komisi'] ?? 0),
                (int) ($row['print_pdf'] ?? 0),
                (int) ($row['total_minat'] ?? 0),
                (int) ($row['total_sales'] ?? 0),
            ]);
        }

        fclose($out);
        exit;
    }

    // =========================================================================
    // 7. LAPORAN SALES TERAKTIF
    // =========================================================================

    public function salesTeraktif()
    {
        if ($redirect = $this->checkAuth()) {
            return $redirect;
        }

        $filters = ActivityFilter::parse($this->request);
        $page    = $filters['page'];
        $offset  = ($page - 1) * $this->perPage;

        $items = $this->activityModel->getSalesTeraktifList($filters, $this->perPage, $offset);
        $total = $this->activityModel->countSalesTeraktif($filters);

        $totalPages  = (int) ceil($total / $this->perPage);
        $productList = $this->activityModel->getProductOptions();

        $isModeActive    = ActivityFilter::isModeActive($filters);
        $isProductActive = ActivityFilter::isProductActive($filters);

        $pagination = $this->generatePagination($page, $totalPages, $filters);

        $data = [
            'title'           => 'Laporan Sales Teraktif - Indonesian Exporter',
            'pageTitle'       => 'Sales Teraktif',
            'pageSubtitle'    => 'Peringkat sales berdasarkan total aktivitas',
            'activeMenu'      => 'laporan-sales-teraktif',
            'filters'         => $filters,
            'items'           => $items,
            'total'           => $total,
            'page'            => $page,
            'totalPages'      => $totalPages,
            'from'            => $total > 0 ? $offset + 1 : 0,
            'to'              => min($offset + $this->perPage, $total),
            'productList'     => $productList,
            'isModeActive'    => $isModeActive,
            'isProductActive' => $isProductActive,
            'pagination'      => $pagination,
        ];

        return view('admin/laporan_aktivitas/sales_teraktif', $data);
    }

    public function exportSalesTeraktif()
    {
        if ($redirect = $this->checkAuth()) {
            return $redirect;
        }

        $filters = ActivityFilter::parse($this->request);
        $rows    = $this->activityModel->getSalesTeraktifList($filters, 0, 0);

        $isModeActive    = ActivityFilter::isModeActive($filters);
        $isProductActive = ActivityFilter::isProductActive($filters);

        $filename = 'laporan_sales_teraktif_' . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $out = fopen('php://output', 'w');
        fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));

        fputcsv($out, ['Peringkat', 'Nama Sales', 'Email', 'Login', 'Pencarian', 'Klik Detail', 'Klik Komisi', 'Cetak PDF', 'Total Aktivitas', 'Produk Paling Sering Dikunjungi', 'Terakhir Aktif']);

        $rank = 1;
        foreach ($rows as $row) {
            $loginVal  = ($isModeActive || $isProductActive) ? '-' : (int) ($row['count_login'] ?? 0);
            $searchVal = $isProductActive ? '-' : (int) ($row['count_search'] ?? 0);

            fputcsv($out, [
                $rank++,
                $row['nama_sales'] ?? '-',
                $row['sales_email'] ?? '-',
                $loginVal,
                $searchVal,
                (int) ($row['count_detail'] ?? 0),
                (int) ($row['count_comm'] ?? 0),
                (int) ($row['count_pdf'] ?? 0),
                (int) ($row['total_aktivitas'] ?? 0),
                $row['top_visited_product'] ?? '-',
                self::formatTglIndo($row['last_active'] ?? null),
            ]);
        }

        fclose($out);
        exit;
    }

    // =========================================================================
    // 8. LAPORAN AKTIVITAS SALES
    // =========================================================================

    public function aktivitasSales()
    {
        if ($redirect = $this->checkAuth()) {
            return $redirect;
        }

        $filters = ActivityFilter::parse($this->request);
        $page    = $filters['page'];
        $offset  = ($page - 1) * $this->perPage;

        $items = $this->activityModel->getAktivitasSalesList($filters, $this->perPage, $offset);
        $total = $this->activityModel->countAktivitasSales($filters);

        $totalPages  = (int) ceil($total / $this->perPage);
        $salesList   = $this->activityModel->getSalesOptions();
        $productList = $this->activityModel->getProductOptions();

        $isModeActive    = ActivityFilter::isModeActive($filters);
        $isProductActive = ActivityFilter::isProductActive($filters);

        $pagination = $this->generatePagination($page, $totalPages, $filters);

        $data = [
            'title'           => 'Laporan Aktivitas Sales - Indonesian Exporter',
            'pageTitle'       => 'Aktivitas Sales',
            'pageSubtitle'    => 'Daftar seluruh aktivitas sales dalam satu daftar',
            'activeMenu'      => 'laporan-aktivitas-sales',
            'filters'         => $filters,
            'items'           => $items,
            'total'           => $total,
            'page'            => $page,
            'totalPages'      => $totalPages,
            'from'            => $total > 0 ? $offset + 1 : 0,
            'to'              => min($offset + $this->perPage, $total),
            'salesList'       => $salesList,
            'productList'     => $productList,
            'isModeActive'    => $isModeActive,
            'isProductActive' => $isProductActive,
            'pagination'      => $pagination,
        ];

        return view('admin/laporan_aktivitas/aktivitas_sales', $data);
    }

    public function exportAktivitasSales()
    {
        if ($redirect = $this->checkAuth()) {
            return $redirect;
        }

        $filters = ActivityFilter::parse($this->request);
        $rows    = $this->activityModel->getAktivitasSalesList($filters, 0, 0);

        $filename = 'laporan_aktivitas_sales_' . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $out = fopen('php://output', 'w');
        fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));

        fputcsv($out, ['No', 'Waktu', 'Nama Sales', 'Mode', 'Aktivitas', 'Produk', 'Perusahaan', 'Keterangan']);

        $no = 1;
        foreach ($rows as $row) {
            $modeLabel = '-';
            if ($row['view_mode'] === 'internal') {
                $modeLabel = 'Internal';
            } elseif ($row['view_mode'] === 'eksternal') {
                $modeLabel = 'Eksternal';
            }

            fputcsv($out, [
                $no++,
                self::formatTglIndo($row['created_at']),
                $row['nama_sales'] ?? '-',
                $modeLabel,
                $row['activity_type'] ?? '-',
                $row['product_name'] ?? '-',
                $row['nama_perusahaan'] ?? '-',
                $row['keterangan'] ?? '-',
            ]);
        }

        fclose($out);
        exit;
    }
}

