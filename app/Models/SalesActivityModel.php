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
}
