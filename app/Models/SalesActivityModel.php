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
}
