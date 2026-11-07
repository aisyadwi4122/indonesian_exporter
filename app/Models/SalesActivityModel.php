<?php

namespace App\Models;

use CodeIgniter\Database\ConnectionInterface;

class SalesActivityModel
{
    protected ConnectionInterface $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    /**
     * Hitung jumlah baris tabel tertentu dalam rentang created_at
     */
    public function getCountBetween(string $table, string $startDateTime, string $endDateTime): int
    {
        if (!$this->db->tableExists($table)) {
            return 0;
        }

        return (int) $this->db->table($table)
            ->where('created_at >=', $startDateTime)
            ->where('created_at <=', $endDateTime)
            ->countAllResults();
    }

    /**
     * Hitung tren persentase antara periode sekarang dan periode sebelumnya
     */
    public function calculateTrend(int $currentCount, int $prevCount): array
    {
        if ($prevCount <= 0) {
            return [
                'label'     => '-',
                'direction' => 'none',
                'percent'   => 0,
            ];
        }

        $diff = $currentCount - $prevCount;
        $percent = (int) round(($diff / $prevCount) * 100);

        if ($percent > 0) {
            return [
                'label'     => '↑ ' . $percent . '%',
                'direction' => 'up',
                'percent'   => $percent,
            ];
        } elseif ($percent < 0) {
            return [
                'label'     => '↓ ' . abs($percent) . '%',
                'direction' => 'down',
                'percent'   => abs($percent),
            ];
        } else {
            return [
                'label'     => '0%',
                'direction' => 'neutral',
                'percent'   => 0,
            ];
        }
    }

    /**
     * Data per hari untuk line chart (5 seri)
     */
    public function getDailySeries(string $startDate, string $endDate): array
    {
        $start = new \DateTime($startDate);
        $end   = new \DateTime($endDate);
        $end->modify('+1 day'); // include end date

        $interval = new \DateInterval('P1D');
        $dateRange = new \DatePeriod($start, $interval, $end);

        $labels = [];
        $map = [
            'search'     => [],
            'login'      => [],
            'detail'     => [],
            'commission' => [],
            'pdf'        => [],
        ];

        foreach ($dateRange as $dt) {
            $ymd = $dt->format('Y-m-d');
            $labels[] = $dt->format('d M');
            $map['search'][$ymd]     = 0;
            $map['login'][$ymd]      = 0;
            $map['detail'][$ymd]     = 0;
            $map['commission'][$ymd] = 0;
            $map['pdf'][$ymd]        = 0;
        }

        $startDateTime = $startDate . ' 00:00:00';
        $endDateTime   = $endDate . ' 23:59:59';

        $tables = [
            'search'     => 'search_history',
            'login'      => 'login_history',
            'detail'     => 'detail_click_history',
            'commission' => 'commission_click_history',
            'pdf'        => 'pdf_history',
        ];

        foreach ($tables as $key => $table) {
            if (!$this->db->tableExists($table)) {
                continue;
            }

            $query = $this->db->query("
                SELECT DATE(created_at) as tgl, COUNT(*) as total 
                FROM {$table} 
                WHERE created_at >= ? AND created_at <= ? 
                GROUP BY DATE(created_at)
            ", [$startDateTime, $endDateTime]);

            $rows = $query->getResultArray();
            foreach ($rows as $r) {
                $tgl = $r['tgl'];
                if (isset($map[$key][$tgl])) {
                    $map[$key][$tgl] = (int) $r['total'];
                }
            }
        }

        return [
            'labels'     => $labels,
            'search'     => array_values($map['search']),
            'login'      => array_values($map['login']),
            'detail'     => array_values($map['detail']),
            'commission' => array_values($map['commission']),
            'pdf'        => array_values($map['pdf']),
        ];
    }

    /**
     * History Pencarian (Top 5 dikelompokkan per kata kunci)
     */
    public function getSearchHistory(string $startDateTime, string $endDateTime, int $limit = 5): array
    {
        if (!$this->db->tableExists('search_history')) {
            return [];
        }

        $query = $this->db->query("
            SELECT keyword, COUNT(*) as total_count, MAX(created_at) as last_created_at
            FROM search_history
            WHERE created_at >= ? AND created_at <= ?
            GROUP BY keyword
            ORDER BY last_created_at DESC, total_count DESC
            LIMIT ?
        ", [$startDateTime, $endDateTime, $limit]);

        return $query->getResultArray();
    }

    /**
     * History Login dari Sales (5 terbaru join tb_sales)
     */
    public function getLoginHistory(string $startDateTime, string $endDateTime, int $limit = 5): array
    {
        if (!$this->db->tableExists('login_history')) {
            return [];
        }

        $query = $this->db->query("
            SELECT login_history.*,
                   COALESCE(tb_sales.nama_sales, tb_sales.username, tb_profil.username, 'Sales') as nama_sales,
                   COALESCE(tb_sales.email, tb_profil.username, '-') as sales_email
            FROM login_history
            LEFT JOIN tb_sales ON tb_sales.id_sales = login_history.user_id
            LEFT JOIN tb_profil ON tb_profil.id_perusahaan = login_history.user_id
            WHERE login_history.created_at >= ? AND login_history.created_at <= ?
            ORDER BY login_history.created_at DESC, login_history.id DESC
            LIMIT ?
        ", [$startDateTime, $endDateTime, $limit]);

        return $query->getResultArray();
    }

    /**
     * History Klik Detail (5 terbaru)
     */
    public function getDetailClickHistory(string $startDateTime, string $endDateTime, int $limit = 5): array
    {
        if (!$this->db->tableExists('detail_click_history')) {
            return [];
        }

        $query = $this->db->query("
            SELECT * FROM detail_click_history
            WHERE created_at >= ? AND created_at <= ?
            ORDER BY created_at DESC, id DESC
            LIMIT ?
        ", [$startDateTime, $endDateTime, $limit]);

        return $query->getResultArray();
    }

    /**
     * History Klik Info Komisi (5 terbaru)
     */
    public function getCommissionClickHistory(string $startDateTime, string $endDateTime, int $limit = 5): array
    {
        if (!$this->db->tableExists('commission_click_history')) {
            return [];
        }

        $query = $this->db->query("
            SELECT * FROM commission_click_history
            WHERE created_at >= ? AND created_at <= ?
            ORDER BY created_at DESC, id DESC
            LIMIT ?
        ", [$startDateTime, $endDateTime, $limit]);

        return $query->getResultArray();
    }

    /**
     * History PDF (5 terbaru dikelompokkan per jenis PDF)
     */
    public function getPdfHistory(string $startDateTime, string $endDateTime, int $limit = 5): array
    {
        if (!$this->db->tableExists('pdf_history')) {
            return [];
        }

        $query = $this->db->query("
            SELECT pdf_type, COUNT(*) as total_count, MAX(created_at) as last_created_at
            FROM pdf_history
            WHERE created_at >= ? AND created_at <= ?
            GROUP BY pdf_type
            ORDER BY last_created_at DESC, total_count DESC
            LIMIT ?
        ", [$startDateTime, $endDateTime, $limit]);

        return $query->getResultArray();
    }

    // =========================================================================
    // 5 LAPORAN AKTIVITAS ADMIN (LIST & EXPORT)
    // =========================================================================

    /**
     * Helper: Mendapatkan daftar seluruh Sales untuk filter dropdown
     */
    public function getSalesOptions(): array
    {
        if (!$this->db->tableExists('tb_sales')) {
            return [];
        }

        $query = $this->db->query("
            SELECT id_sales as user_id, COALESCE(nama_sales, username, 'Sales') as nama_sales
            FROM tb_sales
            ORDER BY nama_sales ASC
        ");

        return $query->getResultArray();
    }

    /**
     * Helper: Mendapatkan daftar nama perusahaan untuk filter dropdown
     */
    public function getCompanyOptions(): array
    {
        $companies = [];

        if ($this->db->tableExists('tb_supplier')) {
            $q1 = $this->db->query("
                SELECT DISTINCT nama_perusahaan 
                FROM tb_supplier 
                WHERE nama_perusahaan IS NOT NULL AND nama_perusahaan != ''
                ORDER BY nama_perusahaan ASC
            ");
            foreach ($q1->getResultArray() as $row) {
                if (!empty($row['nama_perusahaan'])) {
                    $companies[$row['nama_perusahaan']] = true;
                }
            }
        }

        if ($this->db->tableExists('tb_ecp_ekspor')) {
            $q2 = $this->db->query("
                SELECT DISTINCT nama_perusahaan 
                FROM tb_ecp_ekspor 
                WHERE nama_perusahaan IS NOT NULL AND nama_perusahaan != ''
                ORDER BY nama_perusahaan ASC
            ");
            foreach ($q2->getResultArray() as $row) {
                if (!empty($row['nama_perusahaan'])) {
                    $companies[$row['nama_perusahaan']] = true;
                }
            }
        }

        $list = array_keys($companies);
        sort($list);
        return $list;
    }

    /**
     * Helper: Mendapatkan daftar tipe PDF untuk filter dropdown
     */
    public function getPdfTypeOptions(): array
    {
        if (!$this->db->tableExists('pdf_history')) {
            return [];
        }

        $query = $this->db->query("
            SELECT DISTINCT pdf_type 
            FROM pdf_history 
            WHERE pdf_type IS NOT NULL AND pdf_type != ''
            ORDER BY pdf_type ASC
        ");

        $result = [];
        foreach ($query->getResultArray() as $row) {
            $result[] = $row['pdf_type'];
        }
        return $result;
    }

    /**
     * 1. History Pencarian (List)
     */
    public function getPencarianList(array $filters, int $limit = 10, int $offset = 0): array
    {
        if (!$this->db->tableExists('search_history')) {
            return [];
        }

        $where = ["sh.created_at >= ?", "sh.created_at <= ?"];
        $params = [$filters['start_date'] . ' 00:00:00', $filters['end_date'] . ' 23:59:59'];

        if (!empty($filters['keyword'])) {
            $where[] = "sh.keyword LIKE ?";
            $params[] = '%' . $filters['keyword'] . '%';
        }

        if (!empty($filters['user_id'])) {
            $where[] = "sh.user_id = ?";
            $params[] = (int) $filters['user_id'];
        }

        $whereSql = implode(' AND ', $where);
        $sql = "
            SELECT sh.*,
                   COALESCE(s.nama_sales, s.username, p.username, 'Sales') as nama_sales
            FROM search_history sh
            LEFT JOIN tb_sales s ON s.id_sales = sh.user_id
            LEFT JOIN tb_profil p ON p.id_perusahaan = sh.user_id
            WHERE {$whereSql}
            ORDER BY sh.created_at DESC, sh.id DESC
        ";

        if ($limit > 0) {
            $sql .= " LIMIT " . (int) $limit . " OFFSET " . (int) $offset;
        }

        return $this->db->query($sql, $params)->getResultArray();
    }

    /**
     * 1. History Pencarian (Count)
     */
    public function countPencarian(array $filters): int
    {
        if (!$this->db->tableExists('search_history')) {
            return 0;
        }

        $where = ["sh.created_at >= ?", "sh.created_at <= ?"];
        $params = [$filters['start_date'] . ' 00:00:00', $filters['end_date'] . ' 23:59:59'];

        if (!empty($filters['keyword'])) {
            $where[] = "sh.keyword LIKE ?";
            $params[] = '%' . $filters['keyword'] . '%';
        }

        if (!empty($filters['user_id'])) {
            $where[] = "sh.user_id = ?";
            $params[] = (int) $filters['user_id'];
        }

        $whereSql = implode(' AND ', $where);
        $sql = "SELECT COUNT(*) as total FROM search_history sh WHERE {$whereSql}";
        $row = $this->db->query($sql, $params)->getRowArray();
        return (int) ($row['total'] ?? 0);
    }

    /**
     * 2. History Login Sales (List)
     */
    public function getLoginSalesList(array $filters, int $limit = 10, int $offset = 0): array
    {
        if (!$this->db->tableExists('login_history')) {
            return [];
        }

        $where = ["lh.created_at >= ?", "lh.created_at <= ?"];
        $params = [$filters['start_date'] . ' 00:00:00', $filters['end_date'] . ' 23:59:59'];

        if (!empty($filters['search_sales'])) {
            $where[] = "(s.nama_sales LIKE ? OR s.username LIKE ? OR s.email LIKE ? OR p.username LIKE ?)";
            $term = '%' . $filters['search_sales'] . '%';
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        $whereSql = implode(' AND ', $where);
        $sql = "
            SELECT lh.*,
                   COALESCE(s.nama_sales, s.username, p.username, 'Sales') as nama_sales,
                   COALESCE(s.email, p.username, '-') as sales_email
            FROM login_history lh
            LEFT JOIN tb_sales s ON s.id_sales = lh.user_id
            LEFT JOIN tb_profil p ON p.id_perusahaan = lh.user_id
            WHERE {$whereSql}
            ORDER BY lh.created_at DESC, lh.id DESC
        ";

        if ($limit > 0) {
            $sql .= " LIMIT " . (int) $limit . " OFFSET " . (int) $offset;
        }

        return $this->db->query($sql, $params)->getResultArray();
    }

    /**
     * 2. History Login Sales (Count)
     */
    public function countLoginSales(array $filters): int
    {
        if (!$this->db->tableExists('login_history')) {
            return 0;
        }

        $where = ["lh.created_at >= ?", "lh.created_at <= ?"];
        $params = [$filters['start_date'] . ' 00:00:00', $filters['end_date'] . ' 23:59:59'];

        if (!empty($filters['search_sales'])) {
            $where[] = "(s.nama_sales LIKE ? OR s.username LIKE ? OR s.email LIKE ? OR p.username LIKE ?)";
            $term = '%' . $filters['search_sales'] . '%';
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        $whereSql = implode(' AND ', $where);
        $sql = "
            SELECT COUNT(*) as total 
            FROM login_history lh
            LEFT JOIN tb_sales s ON s.id_sales = lh.user_id
            LEFT JOIN tb_profil p ON p.id_perusahaan = lh.user_id
            WHERE {$whereSql}
        ";
        $row = $this->db->query($sql, $params)->getRowArray();
        return (int) ($row['total'] ?? 0);
    }

    /**
     * 3. History Detail (List)
     */
    public function getDetailList(array $filters, int $limit = 10, int $offset = 0): array
    {
        if (!$this->db->tableExists('detail_click_history')) {
            return [];
        }

        $where = ["d.created_at >= ?", "d.created_at <= ?"];
        $params = [$filters['start_date'] . ' 00:00:00', $filters['end_date'] . ' 23:59:59'];

        if (!empty($filters['search_product'])) {
            $where[] = "d.product_name LIKE ?";
            $params[] = '%' . $filters['search_product'] . '%';
        }

        if (!empty($filters['user_id'])) {
            $where[] = "d.user_id = ?";
            $params[] = (int) $filters['user_id'];
        }

        if (!empty($filters['company_name'])) {
            $where[] = "(sup.nama_perusahaan = ? OR ecp.nama_perusahaan = ?)";
            $params[] = $filters['company_name'];
            $params[] = $filters['company_name'];
        }

        $whereSql = implode(' AND ', $where);
        $sql = "
            SELECT d.*,
                   COALESCE(s.nama_sales, s.username, p.username, 'Sales') as nama_sales,
                   COALESCE(sup.nama_perusahaan, ecp.nama_perusahaan, '-') as nama_perusahaan
            FROM detail_click_history d
            LEFT JOIN tb_sales s ON s.id_sales = d.user_id
            LEFT JOIN tb_profil p ON p.id_perusahaan = d.user_id
            LEFT JOIN tb_produk_supplier ps ON ps.id_produk_supplier = d.product_id
            LEFT JOIN tb_supplier sup ON sup.id_supplier = ps.id_supplier
            LEFT JOIN tb_ecp_ekspor ecp ON (d.product_id > 10000 AND ecp.id_ecp = (CAST(d.product_id AS SIGNED) - 10000))
            WHERE {$whereSql}
            ORDER BY d.created_at DESC, d.id DESC
        ";

        if ($limit > 0) {
            $sql .= " LIMIT " . (int) $limit . " OFFSET " . (int) $offset;
        }

        return $this->db->query($sql, $params)->getResultArray();
    }

    /**
     * 3. History Detail (Count)
     */
    public function countDetail(array $filters): int
    {
        if (!$this->db->tableExists('detail_click_history')) {
            return 0;
        }

        $where = ["d.created_at >= ?", "d.created_at <= ?"];
        $params = [$filters['start_date'] . ' 00:00:00', $filters['end_date'] . ' 23:59:59'];

        if (!empty($filters['search_product'])) {
            $where[] = "d.product_name LIKE ?";
            $params[] = '%' . $filters['search_product'] . '%';
        }

        if (!empty($filters['user_id'])) {
            $where[] = "d.user_id = ?";
            $params[] = (int) $filters['user_id'];
        }

        if (!empty($filters['company_name'])) {
            $where[] = "(sup.nama_perusahaan = ? OR ecp.nama_perusahaan = ?)";
            $params[] = $filters['company_name'];
            $params[] = $filters['company_name'];
        }

        $whereSql = implode(' AND ', $where);
        $sql = "
            SELECT COUNT(*) as total 
            FROM detail_click_history d
            LEFT JOIN tb_produk_supplier ps ON ps.id_produk_supplier = d.product_id
            LEFT JOIN tb_supplier sup ON sup.id_supplier = ps.id_supplier
            LEFT JOIN tb_ecp_ekspor ecp ON (d.product_id > 10000 AND ecp.id_ecp = (CAST(d.product_id AS SIGNED) - 10000))
            WHERE {$whereSql}
        ";
        $row = $this->db->query($sql, $params)->getRowArray();
        return (int) ($row['total'] ?? 0);
    }

    /**
     * 4. History Info Komisi (List)
     */
    public function getInfoKomisiList(array $filters, int $limit = 10, int $offset = 0): array
    {
        if (!$this->db->tableExists('commission_click_history')) {
            return [];
        }

        $where = ["c.created_at >= ?", "c.created_at <= ?"];
        $params = [$filters['start_date'] . ' 00:00:00', $filters['end_date'] . ' 23:59:59'];

        if (!empty($filters['keyword'])) {
            $where[] = "(c.product_name LIKE ? OR s.nama_sales LIKE ? OR s.username LIKE ? OR p.username LIKE ?)";
            $term = '%' . $filters['keyword'] . '%';
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        $whereSql = implode(' AND ', $where);
        $sql = "
            SELECT c.*,
                   COALESCE(s.nama_sales, s.username, p.username, 'Sales') as nama_sales,
                   COALESCE(ps.komisi, '-') as nominal_komisi
            FROM commission_click_history c
            LEFT JOIN tb_sales s ON s.id_sales = c.user_id
            LEFT JOIN tb_profil p ON p.id_perusahaan = c.user_id
            LEFT JOIN tb_produk_supplier ps ON ps.id_produk_supplier = c.product_id
            WHERE {$whereSql}
            ORDER BY c.created_at DESC, c.id DESC
        ";

        if ($limit > 0) {
            $sql .= " LIMIT " . (int) $limit . " OFFSET " . (int) $offset;
        }

        return $this->db->query($sql, $params)->getResultArray();
    }

    /**
     * 4. History Info Komisi (Count)
     */
    public function countInfoKomisi(array $filters): int
    {
        if (!$this->db->tableExists('commission_click_history')) {
            return 0;
        }

        $where = ["c.created_at >= ?", "c.created_at <= ?"];
        $params = [$filters['start_date'] . ' 00:00:00', $filters['end_date'] . ' 23:59:59'];

        if (!empty($filters['keyword'])) {
            $where[] = "(c.product_name LIKE ? OR s.nama_sales LIKE ? OR s.username LIKE ? OR p.username LIKE ?)";
            $term = '%' . $filters['keyword'] . '%';
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        $whereSql = implode(' AND ', $where);
        $sql = "
            SELECT COUNT(*) as total 
            FROM commission_click_history c
            LEFT JOIN tb_sales s ON s.id_sales = c.user_id
            LEFT JOIN tb_profil p ON p.id_perusahaan = c.user_id
            WHERE {$whereSql}
        ";
        $row = $this->db->query($sql, $params)->getRowArray();
        return (int) ($row['total'] ?? 0);
    }

    /**
     * 5. History PDF (List)
     */
    public function getPdfList(array $filters, int $limit = 10, int $offset = 0): array
    {
        if (!$this->db->tableExists('pdf_history')) {
            return [];
        }

        $where = ["pdf.created_at >= ?", "pdf.created_at <= ?"];
        $params = [$filters['start_date'] . ' 00:00:00', $filters['end_date'] . ' 23:59:59'];

        if (!empty($filters['keyword'])) {
            $where[] = "(pdf.product_name LIKE ? OR sup.nama_perusahaan LIKE ? OR ecp.nama_perusahaan LIKE ?)";
            $term = '%' . $filters['keyword'] . '%';
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        if (!empty($filters['user_id'])) {
            $where[] = "pdf.user_id = ?";
            $params[] = (int) $filters['user_id'];
        }

        if (!empty($filters['pdf_type'])) {
            $where[] = "pdf.pdf_type = ?";
            $params[] = $filters['pdf_type'];
        }

        $whereSql = implode(' AND ', $where);
        $sql = "
            SELECT pdf.*,
                   COALESCE(s.nama_sales, s.username, p.username, 'Sales') as nama_sales,
                   COALESCE(sup.nama_perusahaan, ecp.nama_perusahaan, '-') as nama_perusahaan
            FROM pdf_history pdf
            LEFT JOIN tb_sales s ON s.id_sales = pdf.user_id
            LEFT JOIN tb_profil p ON p.id_perusahaan = pdf.user_id
            LEFT JOIN tb_produk_supplier ps ON ps.id_produk_supplier = pdf.product_id
            LEFT JOIN tb_supplier sup ON sup.id_supplier = ps.id_supplier
            LEFT JOIN tb_ecp_ekspor ecp ON (pdf.product_id > 10000 AND ecp.id_ecp = (CAST(pdf.product_id AS SIGNED) - 10000))
            WHERE {$whereSql}
            ORDER BY pdf.created_at DESC, pdf.id DESC
        ";

        if ($limit > 0) {
            $sql .= " LIMIT " . (int) $limit . " OFFSET " . (int) $offset;
        }

        return $this->db->query($sql, $params)->getResultArray();
    }

    /**
     * 5. History PDF (Count)
     */
    public function countPdf(array $filters): int
    {
        if (!$this->db->tableExists('pdf_history')) {
            return 0;
        }

        $where = ["pdf.created_at >= ?", "pdf.created_at <= ?"];
        $params = [$filters['start_date'] . ' 00:00:00', $filters['end_date'] . ' 23:59:59'];

        if (!empty($filters['keyword'])) {
            $where[] = "(pdf.product_name LIKE ? OR sup.nama_perusahaan LIKE ? OR ecp.nama_perusahaan LIKE ?)";
            $term = '%' . $filters['keyword'] . '%';
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        if (!empty($filters['user_id'])) {
            $where[] = "pdf.user_id = ?";
            $params[] = (int) $filters['user_id'];
        }

        if (!empty($filters['pdf_type'])) {
            $where[] = "pdf.pdf_type = ?";
            $params[] = $filters['pdf_type'];
        }

        $whereSql = implode(' AND ', $where);
        $sql = "
            SELECT COUNT(*) as total 
            FROM pdf_history pdf
            LEFT JOIN tb_produk_supplier ps ON ps.id_produk_supplier = pdf.product_id
            LEFT JOIN tb_supplier sup ON sup.id_supplier = ps.id_supplier
            LEFT JOIN tb_ecp_ekspor ecp ON (pdf.product_id > 10000 AND ecp.id_ecp = (CAST(pdf.product_id AS SIGNED) - 10000))
            WHERE {$whereSql}
        ";
        $row = $this->db->query($sql, $params)->getRowArray();
        return (int) ($row['total'] ?? 0);
    }

    /**
     * Helper privat untuk menyusun klausa WHERE dan binding parameters berdasarkan array $filters
     *
     * @param array $filters [start_date, end_date, mode, sales_id, product_id, jenis]
     * @param string $alias Prefix nama/alias tabel (misal: 'd', 'sh', dll.)
     * @param array $options Konfigurasi kolom:
     *                       - 'has_mode' (bool, default true): tabel punya kolom view_mode
     *                       - 'has_sales' (bool, default true): tabel punya kolom user_id
     *                       - 'has_product' (bool, default true): tabel punya kolom product_id
     *                       - 'date_col' (string, default 'created_at'): nama kolom datetime
     * @return array ['where' => string[], 'params' => array, 'whereSql' => string]
     */
    private function applyFilters(array $filters, string $alias = '', array $options = []): array
    {
        $hasMode    = $options['has_mode'] ?? true;
        $hasSales   = $options['has_sales'] ?? true;
        $hasProduct = $options['has_product'] ?? true;
        $dateCol    = $options['date_col'] ?? 'created_at';
        $prefix     = ($alias !== '') ? "{$alias}." : "";

        $where  = [];
        $params = [];

        $startDate = !empty($filters['start_date']) ? $filters['start_date'] : date('Y-m-01');
        $endDate   = !empty($filters['end_date']) ? $filters['end_date'] : date('Y-m-t');

        $where[]  = "{$prefix}{$dateCol} >= ?";
        $params[] = $startDate . ' 00:00:00';

        $where[]  = "{$prefix}{$dateCol} <= ?";
        $params[] = $endDate . ' 23:59:59';

        if ($hasMode && !empty($filters['mode']) && in_array($filters['mode'], ['internal', 'eksternal'], true)) {
            $where[]  = "{$prefix}view_mode = ?";
            $params[] = $filters['mode'];
        }

        if ($hasSales && !empty($filters['sales_id']) && (int) $filters['sales_id'] > 0) {
            $where[]  = "{$prefix}user_id = ?";
            $params[] = (int) $filters['sales_id'];
        }

        if ($hasProduct && !empty($filters['product_id']) && (int) $filters['product_id'] > 0) {
            $where[]  = "{$prefix}product_id = ?";
            $params[] = (int) $filters['product_id'];
        }

        return [
            'where'    => $where,
            'params'   => $params,
            'whereSql' => implode(' AND ', $where),
        ];
    }

    /**
     * Ambil daftar opsi produk unik untuk dropdown filter
     */
    public function getProductOptions(): array
    {
        $sql = "
            SELECT DISTINCT p.product_id, p.nama_produk
            FROM (
                SELECT product_id, product_name as nama_produk FROM detail_click_history WHERE product_id > 0
                UNION
                SELECT product_id, product_name as nama_produk FROM commission_click_history WHERE product_id > 0
                UNION
                SELECT product_id, product_name as nama_produk FROM pdf_history WHERE product_id IS NOT NULL AND product_id > 0
                UNION
                SELECT id_produk_supplier as product_id, nama_produk FROM tb_produk_supplier
            ) p
            WHERE p.product_id > 0 AND p.nama_produk IS NOT NULL AND p.nama_produk != ''
            GROUP BY p.product_id
            ORDER BY p.nama_produk ASC
        ";
        return $this->db->query($sql)->getResultArray();
    }

    /**
     * 6. Laporan Produk Terlaris (List)
     * Peringkat produk yang paling banyak diminati sales (Klik Detail + Klik Komisi + Cetak PDF)
     */
    public function getProdukTerlarisList(array $filters, int $limit = 10, int $offset = 0): array
    {
        $fDetail = $this->applyFilters($filters, '', ['has_mode' => true, 'has_sales' => true, 'has_product' => false]);
        $fComm   = $this->applyFilters($filters, '', ['has_mode' => true, 'has_sales' => true, 'has_product' => false]);
        $fPdf    = $this->applyFilters($filters, '', ['has_mode' => true, 'has_sales' => true, 'has_product' => false]);

        $unionParams = array_merge($fDetail['params'], $fComm['params'], $fPdf['params']);

        $sql = "
            SELECT 
                combined.product_id,
                COALESCE(ps.nama_produk, MAX(combined.product_name), CONCAT('Produk #', combined.product_id)) as nama_produk,
                COALESCE(sup.nama_perusahaan, ecp.nama_perusahaan, '-') as nama_perusahaan,
                CAST(SUM(combined.is_detail) AS SIGNED) as click_detail,
                CAST(SUM(combined.is_comm) AS SIGNED) as click_komisi,
                CAST(SUM(combined.is_pdf) AS SIGNED) as print_pdf,
                CAST((SUM(combined.is_detail) + SUM(combined.is_comm) + SUM(combined.is_pdf)) AS SIGNED) as total_minat,
                COUNT(DISTINCT combined.user_id) as total_sales
            FROM (
                SELECT product_id, user_id, product_name, 1 as is_detail, 0 as is_comm, 0 as is_pdf, created_at 
                FROM detail_click_history 
                WHERE {$fDetail['whereSql']} AND product_id > 0

                UNION ALL

                SELECT product_id, user_id, product_name, 0 as is_detail, 1 as is_comm, 0 as is_pdf, created_at 
                FROM commission_click_history 
                WHERE {$fComm['whereSql']} AND product_id > 0

                UNION ALL

                SELECT product_id, user_id, product_name, 0 as is_detail, 0 as is_comm, 1 as is_pdf, created_at 
                FROM pdf_history 
                WHERE {$fPdf['whereSql']} AND product_id IS NOT NULL AND product_id > 0
            ) combined
            LEFT JOIN tb_produk_supplier ps ON ps.id_produk_supplier = combined.product_id
            LEFT JOIN tb_supplier sup ON sup.id_supplier = ps.id_supplier
            LEFT JOIN tb_ecp_ekspor ecp ON (combined.product_id > 10000 AND ecp.id_ecp = (CAST(combined.product_id AS SIGNED) - 10000))
            GROUP BY combined.product_id, ps.nama_produk, sup.nama_perusahaan, ecp.nama_perusahaan
            ORDER BY total_minat DESC, combined.product_id DESC
        ";

        if ($limit > 0) {
            $sql .= " LIMIT " . (int) $limit . " OFFSET " . (int) $offset;
        }

        return $this->db->query($sql, $unionParams)->getResultArray();
    }

    /**
     * 6. Laporan Produk Terlaris (Count)
     */
    public function countProdukTerlaris(array $filters): int
    {
        $fDetail = $this->applyFilters($filters, '', ['has_mode' => true, 'has_sales' => true, 'has_product' => false]);
        $fComm   = $this->applyFilters($filters, '', ['has_mode' => true, 'has_sales' => true, 'has_product' => false]);
        $fPdf    = $this->applyFilters($filters, '', ['has_mode' => true, 'has_sales' => true, 'has_product' => false]);

        $unionParams = array_merge($fDetail['params'], $fComm['params'], $fPdf['params']);

        $sql = "
            SELECT COUNT(DISTINCT combined.product_id) as total
            FROM (
                SELECT product_id, user_id, created_at FROM detail_click_history WHERE {$fDetail['whereSql']} AND product_id > 0
                UNION ALL
                SELECT product_id, user_id, created_at FROM commission_click_history WHERE {$fComm['whereSql']} AND product_id > 0
                UNION ALL
                SELECT product_id, user_id, created_at FROM pdf_history WHERE {$fPdf['whereSql']} AND product_id IS NOT NULL AND product_id > 0
            ) combined
        ";

        $row = $this->db->query($sql, $unionParams)->getRowArray();
        return (int) ($row['total'] ?? 0);
    }

    /**
     * 7. Laporan Sales Teraktif (List)
     * Peringkat sales berdasarkan total aktivitas dengan aturan pengecualian filter
     */
    public function getSalesTeraktifList(array $filters, int $limit = 10, int $offset = 0): array
    {
        $modeActive    = !empty($filters['mode']) && in_array($filters['mode'], ['internal', 'eksternal'], true);
        $productActive = !empty($filters['product_id']) && (int) $filters['product_id'] > 0;

        $includeLogin  = (!$modeActive && !$productActive);
        $includeSearch = (!$productActive);

        $unions = [];
        $unionParams = [];

        if ($includeLogin) {
            $fLogin = $this->applyFilters($filters, '', ['has_mode' => false, 'has_sales' => false, 'has_product' => false]);
            $unions[] = "SELECT user_id, 1 as is_login, 0 as is_search, 0 as is_detail, 0 as is_comm, 0 as is_pdf, created_at FROM login_history WHERE {$fLogin['whereSql']}";
            $unionParams = array_merge($unionParams, $fLogin['params']);
        }

        if ($includeSearch) {
            $fSearch = $this->applyFilters($filters, '', ['has_mode' => true, 'has_sales' => false, 'has_product' => false]);
            $unions[] = "SELECT user_id, 0 as is_login, 1 as is_search, 0 as is_detail, 0 as is_comm, 0 as is_pdf, created_at FROM search_history WHERE {$fSearch['whereSql']}";
            $unionParams = array_merge($unionParams, $fSearch['params']);
        }

        $fDetail = $this->applyFilters($filters, '', ['has_mode' => true, 'has_sales' => false, 'has_product' => true]);
        $unions[] = "SELECT user_id, 0 as is_login, 0 as is_search, 1 as is_detail, 0 as is_comm, 0 as is_pdf, created_at FROM detail_click_history WHERE {$fDetail['whereSql']}";
        $unionParams = array_merge($unionParams, $fDetail['params']);

        $fComm = $this->applyFilters($filters, '', ['has_mode' => true, 'has_sales' => false, 'has_product' => true]);
        $unions[] = "SELECT user_id, 0 as is_login, 0 as is_search, 0 as is_detail, 1 as is_comm, 0 as is_pdf, created_at FROM commission_click_history WHERE {$fComm['whereSql']}";
        $unionParams = array_merge($unionParams, $fComm['params']);

        $fPdf = $this->applyFilters($filters, '', ['has_mode' => true, 'has_sales' => false, 'has_product' => true]);
        $unions[] = "SELECT user_id, 0 as is_login, 0 as is_search, 0 as is_detail, 0 as is_comm, 1 as is_pdf, created_at FROM pdf_history WHERE {$fPdf['whereSql']}";
        $unionParams = array_merge($unionParams, $fPdf['params']);

        $unionSql = implode("\n UNION ALL \n", $unions);

        // Subquery untuk menghitung "Produk Paling Sering Dikunjungi" (mengikuti filter mode dan tanggal)
        $fTopDetail = $this->applyFilters($filters, '', ['has_mode' => true, 'has_sales' => false, 'has_product' => false]);
        $topDetailParams = $fTopDetail['params'];

        $sql = "
            SELECT 
                combined.user_id,
                COALESCE(s.nama_sales, s.username, p.username, 'Sales') as nama_sales,
                COALESCE(s.email, p.username, '-') as sales_email,
                " . ($includeLogin ? "CAST(SUM(combined.is_login) AS SIGNED)" : "NULL") . " as count_login,
                " . ($includeSearch ? "CAST(SUM(combined.is_search) AS SIGNED)" : "NULL") . " as count_search,
                CAST(SUM(combined.is_detail) AS SIGNED) as count_detail,
                CAST(SUM(combined.is_comm) AS SIGNED) as count_comm,
                CAST(SUM(combined.is_pdf) AS SIGNED) as count_pdf,
                CAST((
                    " . ($includeLogin ? "SUM(combined.is_login)" : "0") . " +
                    " . ($includeSearch ? "SUM(combined.is_search)" : "0") . " +
                    SUM(combined.is_detail) +
                    SUM(combined.is_comm) +
                    SUM(combined.is_pdf)
                ) AS SIGNED) as total_aktivitas,
                top_p.top_visited_product,
                MAX(combined.created_at) as last_active
            FROM (
                {$unionSql}
            ) combined
            LEFT JOIN tb_sales s ON s.id_sales = combined.user_id
            LEFT JOIN tb_profil p ON p.id_perusahaan = combined.user_id
            LEFT JOIN (
                SELECT 
                    p_agg.user_id,
                    SUBSTRING_INDEX(
                        GROUP_CONCAT(
                            CONCAT(p_agg.product_name, ' (', p_agg.cnt, ' klik)') 
                            ORDER BY p_agg.cnt DESC, p_agg.last_at DESC 
                            SEPARATOR '|||'
                        ), '|||', 1
                    ) as top_visited_product
                FROM (
                    SELECT user_id, product_name, COUNT(*) as cnt, MAX(created_at) as last_at
                    FROM detail_click_history
                    WHERE {$fTopDetail['whereSql']} AND product_id > 0
                    GROUP BY user_id, product_name
                ) p_agg
                GROUP BY p_agg.user_id
            ) top_p ON top_p.user_id = combined.user_id
            GROUP BY combined.user_id, s.nama_sales, s.username, s.email, p.username, top_p.top_visited_product
            ORDER BY total_aktivitas DESC, last_active DESC, combined.user_id DESC
        ";

        if ($limit > 0) {
            $sql .= " LIMIT " . (int) $limit . " OFFSET " . (int) $offset;
        }

        $allParams = array_merge($unionParams, $topDetailParams);
        return $this->db->query($sql, $allParams)->getResultArray();
    }

    /**
     * 7. Laporan Sales Teraktif (Count)
     */
    public function countSalesTeraktif(array $filters): int
    {
        $modeActive    = !empty($filters['mode']) && in_array($filters['mode'], ['internal', 'eksternal'], true);
        $productActive = !empty($filters['product_id']) && (int) $filters['product_id'] > 0;

        $includeLogin  = (!$modeActive && !$productActive);
        $includeSearch = (!$productActive);

        $unions = [];
        $unionParams = [];

        if ($includeLogin) {
            $fLogin = $this->applyFilters($filters, '', ['has_mode' => false, 'has_sales' => false, 'has_product' => false]);
            $unions[] = "SELECT user_id FROM login_history WHERE {$fLogin['whereSql']}";
            $unionParams = array_merge($unionParams, $fLogin['params']);
        }

        if ($includeSearch) {
            $fSearch = $this->applyFilters($filters, '', ['has_mode' => true, 'has_sales' => false, 'has_product' => false]);
            $unions[] = "SELECT user_id FROM search_history WHERE {$fSearch['whereSql']}";
            $unionParams = array_merge($unionParams, $fSearch['params']);
        }

        $fDetail = $this->applyFilters($filters, '', ['has_mode' => true, 'has_sales' => false, 'has_product' => true]);
        $unions[] = "SELECT user_id FROM detail_click_history WHERE {$fDetail['whereSql']}";
        $unionParams = array_merge($unionParams, $fDetail['params']);

        $fComm = $this->applyFilters($filters, '', ['has_mode' => true, 'has_sales' => false, 'has_product' => true]);
        $unions[] = "SELECT user_id FROM commission_click_history WHERE {$fComm['whereSql']}";
        $unionParams = array_merge($unionParams, $fComm['params']);

        $fPdf = $this->applyFilters($filters, '', ['has_mode' => true, 'has_sales' => false, 'has_product' => true]);
        $unions[] = "SELECT user_id FROM pdf_history WHERE {$fPdf['whereSql']}";
        $unionParams = array_merge($unionParams, $fPdf['params']);

        $unionSql = implode("\n UNION ALL \n", $unions);
        $sql = "SELECT COUNT(DISTINCT combined.user_id) as total FROM ({$unionSql}) combined";

        $row = $this->db->query($sql, $unionParams)->getRowArray();
        return (int) ($row['total'] ?? 0);
    }

    /**
     * 8. Laporan Aktivitas Sales (List)
     * Seluruh aktivitas sales dalam satu daftar kronologis
     */
    public function getAktivitasSalesList(array $filters, int $limit = 10, int $offset = 0): array
    {
        $modeActive    = !empty($filters['mode']) && in_array($filters['mode'], ['internal', 'eksternal'], true);
        $productActive = !empty($filters['product_id']) && (int) $filters['product_id'] > 0;
        $jenisFilter   = !empty($filters['jenis']) ? $filters['jenis'] : '';

        $unions = [];
        $unionParams = [];

        // 1. Login
        if ((empty($jenisFilter) || $jenisFilter === 'login') && !$modeActive && !$productActive) {
            $fLogin = $this->applyFilters($filters, '', ['has_mode' => false, 'has_sales' => true, 'has_product' => false]);
            $unions[] = "
                SELECT 
                    user_id,
                    '-' as view_mode,
                    'Login' as activity_type,
                    NULL as product_id,
                    '-' as product_name,
                    CONCAT('IP: ', ip_address) as keterangan,
                    created_at
                FROM login_history
                WHERE {$fLogin['whereSql']}
            ";
            $unionParams = array_merge($unionParams, $fLogin['params']);
        }

        // 2. Pencarian
        if ((empty($jenisFilter) || $jenisFilter === 'pencarian') && !$productActive) {
            $fSearch = $this->applyFilters($filters, '', ['has_mode' => true, 'has_sales' => true, 'has_product' => false]);
            $unions[] = "
                SELECT 
                    user_id,
                    view_mode,
                    'Pencarian' as activity_type,
                    NULL as product_id,
                    '-' as product_name,
                    CONCAT('Kata kunci: \"', keyword, '\" (', result_count, ' hasil)') as keterangan,
                    created_at
                FROM search_history
                WHERE {$fSearch['whereSql']}
            ";
            $unionParams = array_merge($unionParams, $fSearch['params']);
        }

        // 3. Detail Click
        if (empty($jenisFilter) || $jenisFilter === 'detail') {
            $fDetail = $this->applyFilters($filters, '', ['has_mode' => true, 'has_sales' => true, 'has_product' => true]);
            $unions[] = "
                SELECT 
                    user_id,
                    view_mode,
                    'Klik Detail' as activity_type,
                    product_id,
                    product_name,
                    '-' as keterangan,
                    created_at
                FROM detail_click_history
                WHERE {$fDetail['whereSql']}
            ";
            $unionParams = array_merge($unionParams, $fDetail['params']);
        }

        // 4. Commission Click
        if (empty($jenisFilter) || $jenisFilter === 'komisi') {
            $fComm = $this->applyFilters($filters, '', ['has_mode' => true, 'has_sales' => true, 'has_product' => true]);
            $unions[] = "
                SELECT 
                    user_id,
                    view_mode,
                    'Klik Info Komisi' as activity_type,
                    product_id,
                    product_name,
                    '-' as keterangan,
                    created_at
                FROM commission_click_history
                WHERE {$fComm['whereSql']}
            ";
            $unionParams = array_merge($unionParams, $fComm['params']);
        }

        // 5. PDF
        if (empty($jenisFilter) || $jenisFilter === 'pdf') {
            $fPdf = $this->applyFilters($filters, '', ['has_mode' => true, 'has_sales' => true, 'has_product' => true]);
            $unions[] = "
                SELECT 
                    user_id,
                    view_mode,
                    'Cetak PDF' as activity_type,
                    product_id,
                    COALESCE(product_name, '-') as product_name,
                    pdf_type as keterangan,
                    created_at
                FROM pdf_history
                WHERE {$fPdf['whereSql']}
            ";
            $unionParams = array_merge($unionParams, $fPdf['params']);
        }

        if (empty($unions)) {
            return [];
        }

        $unionSql = implode("\n UNION ALL \n", $unions);

        $sql = "
            SELECT 
                act.*,
                COALESCE(s.nama_sales, s.username, p.username, 'Sales') as nama_sales,
                COALESCE(sup.nama_perusahaan, ecp.nama_perusahaan, '-') as nama_perusahaan
            FROM (
                {$unionSql}
            ) act
            LEFT JOIN tb_sales s ON s.id_sales = act.user_id
            LEFT JOIN tb_profil p ON p.id_perusahaan = act.user_id
            LEFT JOIN tb_produk_supplier ps ON ps.id_produk_supplier = act.product_id
            LEFT JOIN tb_supplier sup ON sup.id_supplier = ps.id_supplier
            LEFT JOIN tb_ecp_ekspor ecp ON (act.product_id > 10000 AND ecp.id_ecp = (CAST(act.product_id AS SIGNED) - 10000))
            ORDER BY act.created_at DESC
        ";

        if ($limit > 0) {
            $sql .= " LIMIT " . (int) $limit . " OFFSET " . (int) $offset;
        }

        return $this->db->query($sql, $unionParams)->getResultArray();
    }

    /**
     * 8. Laporan Aktivitas Sales (Count)
     */
    public function countAktivitasSales(array $filters): int
    {
        $modeActive    = !empty($filters['mode']) && in_array($filters['mode'], ['internal', 'eksternal'], true);
        $productActive = !empty($filters['product_id']) && (int) $filters['product_id'] > 0;
        $jenisFilter   = !empty($filters['jenis']) ? $filters['jenis'] : '';

        $unions = [];
        $unionParams = [];

        if ((empty($jenisFilter) || $jenisFilter === 'login') && !$modeActive && !$productActive) {
            $fLogin = $this->applyFilters($filters, '', ['has_mode' => false, 'has_sales' => true, 'has_product' => false]);
            $unions[] = "SELECT 1 FROM login_history WHERE {$fLogin['whereSql']}";
            $unionParams = array_merge($unionParams, $fLogin['params']);
        }

        if ((empty($jenisFilter) || $jenisFilter === 'pencarian') && !$productActive) {
            $fSearch = $this->applyFilters($filters, '', ['has_mode' => true, 'has_sales' => true, 'has_product' => false]);
            $unions[] = "SELECT 1 FROM search_history WHERE {$fSearch['whereSql']}";
            $unionParams = array_merge($unionParams, $fSearch['params']);
        }

        if (empty($jenisFilter) || $jenisFilter === 'detail') {
            $fDetail = $this->applyFilters($filters, '', ['has_mode' => true, 'has_sales' => true, 'has_product' => true]);
            $unions[] = "SELECT 1 FROM detail_click_history WHERE {$fDetail['whereSql']}";
            $unionParams = array_merge($unionParams, $fDetail['params']);
        }

        if (empty($jenisFilter) || $jenisFilter === 'komisi') {
            $fComm = $this->applyFilters($filters, '', ['has_mode' => true, 'has_sales' => true, 'has_product' => true]);
            $unions[] = "SELECT 1 FROM commission_click_history WHERE {$fComm['whereSql']}";
            $unionParams = array_merge($unionParams, $fComm['params']);
        }

        if (empty($jenisFilter) || $jenisFilter === 'pdf') {
            $fPdf = $this->applyFilters($filters, '', ['has_mode' => true, 'has_sales' => true, 'has_product' => true]);
            $unions[] = "SELECT 1 FROM pdf_history WHERE {$fPdf['whereSql']}";
            $unionParams = array_merge($unionParams, $fPdf['params']);
        }

        if (empty($unions)) {
            return 0;
        }

        $unionSql = implode("\n UNION ALL \n", $unions);
        $sql = "SELECT COUNT(*) as total FROM ({$unionSql}) act";

        $row = $this->db->query($sql, $unionParams)->getRowArray();
        return (int) ($row['total'] ?? 0);
    }
}
