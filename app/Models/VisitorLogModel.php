<?php

namespace App\Models;

use CodeIgniter\Model;

class VisitorLogModel extends Model
{
    protected $table            = 'tb_visitor_log';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'visit_date',
        'visitor_hash',
        'path',
        'created_at',
    ];

    protected $useTimestamps = false;

    /**
     * Helper untuk mengambil objek DateTime saat ini di Timezone Asia/Jakarta
     */
    protected function getNowJakarta(): \DateTime
    {
        return new \DateTime('now', new \DateTimeZone('Asia/Jakarta'));
    }

    /**
     * Mengambil ringkasan statistik kunjungan dan pengunjung unik:
     * - Hari ini
     * - 7 hari terakhir
     * - 30 hari terakhir
     * - Total keseluruhan
     */
    public function getSummaryStats(): array
    {
        $now = $this->getNowJakarta();
        $today = $now->format('Y-m-d');

        $d7 = clone $now;
        $d7->modify('-6 days');
        $date7Ago = $d7->format('Y-m-d');

        $d30 = clone $now;
        $d30->modify('-29 days');
        $date30Ago = $d30->format('Y-m-d');

        // 1. Hari Ini
        $todayRow = $this->db->table($this->table)
            ->select('COUNT(*) AS visits, COUNT(DISTINCT visitor_hash) AS uniques')
            ->where('visit_date', $today)
            ->get()
            ->getRowArray();

        // 2. 7 Hari Terakhir
        $days7Row = $this->db->table($this->table)
            ->select('COUNT(*) AS visits, COUNT(DISTINCT visitor_hash) AS uniques')
            ->where('visit_date >=', $date7Ago)
            ->where('visit_date <=', $today)
            ->get()
            ->getRowArray();

        // 3. 30 Hari Terakhir
        $days30Row = $this->db->table($this->table)
            ->select('COUNT(*) AS visits, COUNT(DISTINCT visitor_hash) AS uniques')
            ->where('visit_date >=', $date30Ago)
            ->where('visit_date <=', $today)
            ->get()
            ->getRowArray();

        // 4. Total Keseluruhan
        $totalRow = $this->db->table($this->table)
            ->select('COUNT(*) AS visits, COUNT(DISTINCT visitor_hash) AS uniques')
            ->get()
            ->getRowArray();

        return [
            'today_visits'   => (int) ($todayRow['visits'] ?? 0),
            'today_uniques'  => (int) ($todayRow['uniques'] ?? 0),
            'days7_visits'   => (int) ($days7Row['visits'] ?? 0),
            'days7_uniques'  => (int) ($days7Row['uniques'] ?? 0),
            'days30_visits'  => (int) ($days30Row['visits'] ?? 0),
            'days30_uniques' => (int) ($days30Row['uniques'] ?? 0),
            'total_visits'   => (int) ($totalRow['visits'] ?? 0),
            'total_uniques'  => (int) ($totalRow['uniques'] ?? 0),
        ];
    }

    /**
     * Mengambil data harian 14 hari terakhir sampai hari ini.
     * Hari tanpa data tetap terisi dengan 0 kunjungan dan 0 pengunjung unik.
     */
    public function getDailyStats14Days(): array
    {
        $now = $this->getNowJakarta();
        $today = $now->format('Y-m-d');

        $d14 = clone $now;
        $d14->modify('-13 days');
        $date14Ago = $d14->format('Y-m-d');

        $rows = $this->db->table($this->table)
            ->select('visit_date, COUNT(*) AS visits, COUNT(DISTINCT visitor_hash) AS uniques')
            ->where('visit_date >=', $date14Ago)
            ->where('visit_date <=', $today)
            ->groupBy('visit_date')
            ->orderBy('visit_date', 'ASC')
            ->get()
            ->getResultArray();

        $map = [];
        foreach ($rows as $r) {
            $map[$r['visit_date']] = [
                'visits'  => (int) $r['visits'],
                'uniques' => (int) $r['uniques'],
            ];
        }

        $dailyStats = [];
        for ($i = 13; $i >= 0; $i--) {
            $dt = clone $now;
            $dt->modify("-$i days");
            $dateStr = $dt->format('Y-m-d');
            $label = $dt->format('d M');

            $dailyStats[] = [
                'date'    => $dateStr,
                'label'   => $label,
                'visits'  => $map[$dateStr]['visits'] ?? 0,
                'uniques' => $map[$dateStr]['uniques'] ?? 0,
            ];
        }

        return $dailyStats;
    }

    /**
     * Mengambil 5 path terbanyak dikunjungi dalam 30 hari terakhir
     */
    public function getTopPaths30Days(int $limit = 5): array
    {
        $now = $this->getNowJakarta();
        $today = $now->format('Y-m-d');

        $d30 = clone $now;
        $d30->modify('-29 days');
        $date30Ago = $d30->format('Y-m-d');

        return $this->db->table($this->table)
            ->select('path, COUNT(*) AS visits, COUNT(DISTINCT visitor_hash) AS uniques')
            ->where('visit_date >=', $date30Ago)
            ->where('visit_date <=', $today)
            ->groupBy('path')
            ->orderBy('visits', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }

    /**
     * Menghitung total kunjungan pada satu tanggal tertentu (visit_date)
     */
    public function getVisitsByDate(string $date): int
    {
        return (int) $this->db->table($this->table)
            ->where('visit_date', $date)
            ->countAllResults();
    }

    /**
     * Menghitung total kunjungan pada rentang tanggal tertentu (visit_date)
     * Memanfaatkan index idx_visit_date
     */
    public function totalByRange(string $startDate, string $endDate): int
    {
        return (int) $this->db->table($this->table)
            ->where('visit_date >=', $startDate)
            ->where('visit_date <=', $endDate)
            ->countAllResults();
    }
}
