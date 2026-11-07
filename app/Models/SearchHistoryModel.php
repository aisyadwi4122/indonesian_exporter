<?php

namespace App\Models;

use CodeIgniter\Model;

class SearchHistoryModel extends Model
{
    protected $table            = 'search_history';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id',
        'keyword',
        'result_count',
        'view_mode',
        'created_at',
    ];

    protected $useTimestamps = false;

    /**
     * Simpan riwayat pencarian
     *
     * @param int    $userId
     * @param string $keyword
     * @param int    $resultCount
     * @param string $viewMode
     * @return bool
     */
    public function recordSearch(int $userId, string $keyword, int $resultCount = 0, string $viewMode = 'internal'): bool
    {
        $keyword = strtolower(trim($keyword));
        if ($keyword === '') {
            return false;
        }

        $viewMode = (strtolower(trim($viewMode)) === 'eksternal') ? 'eksternal' : 'internal';

        return (bool) $this->insert([
            'user_id'      => $userId,
            'keyword'      => $keyword,
            'result_count' => $resultCount,
            'view_mode'    => $viewMode,
            'created_at'   => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Hitung total seluruh pencarian
     *
     * @return int
     */
    public function getTotalSearchCount(): int
    {
        return $this->countAll();
    }

    /**
     * Ambil kata kunci yang paling sering dicari (Top N)
     *
     * @param int $limit
     * @return array
     */
    public function getTopKeywords(int $limit = 10): array
    {
        return $this->select('keyword, COUNT(*) as total_search, MAX(created_at) as last_searched_at')
            ->groupBy('keyword')
            ->orderBy('total_search', 'DESC')
            ->orderBy('last_searched_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    /**
     * Ambil riwayat pencarian terbaru (Recent N) join ke user sales
     *
     * @param int $limit
     * @return array
     */
    public function getRecentSearches(int $limit = 10): array
    {
        return $this->select('search_history.*, COALESCE(tb_sales.nama_sales, tb_sales.username, tb_profil.username, "Sales") as nama_sales')
            ->join('tb_sales', 'tb_sales.id_sales = search_history.user_id', 'left')
            ->join('tb_profil', 'tb_profil.id_perusahaan = search_history.user_id', 'left')
            ->orderBy('search_history.created_at', 'DESC')
            ->orderBy('search_history.id', 'DESC')
            ->limit($limit)
            ->findAll();
    }
}

