<?php

namespace App\Libraries;

use CodeIgniter\HTTP\IncomingRequest;

/**
 * Library ActivityFilter
 * Bertanggung jawab memvalidasi dan memproses parameter filter laporan aktivitas:
 * - mode: 'semua', 'internal', 'eksternal'
 * - sales_id: int > 0 atau null
 * - product_id: int > 0 atau null
 * - start_date: YYYY-MM-DD (default: awal bulan)
 * - end_date: YYYY-MM-DD (default: akhir bulan)
 * - jenis: 'semua', 'login', 'pencarian', 'detail', 'komisi', 'pdf'
 * - page: int >= 1
 */
class ActivityFilter
{
    /**
     * Parse and sanitize request parameters
     *
     * @param IncomingRequest $request
     * @return array
     */
    public static function parse(IncomingRequest $request): array
    {
        $startDate = (string) ($request->getGet('start_date') ?? '');
        $endDate   = (string) ($request->getGet('end_date') ?? '');

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

        $rawMode = strtolower(trim((string) ($request->getGet('mode') ?? '')));
        $mode = in_array($rawMode, ['internal', 'eksternal'], true) ? $rawMode : '';

        $rawSalesId = $request->getGet('sales_id');
        $salesId = null;
        if ($rawSalesId !== null && $rawSalesId !== '' && $rawSalesId !== 'semua' && is_numeric($rawSalesId) && (int) $rawSalesId > 0) {
            $salesId = (int) $rawSalesId;
        }

        $rawProductId = $request->getGet('product_id');
        $productId = null;
        if ($rawProductId !== null && $rawProductId !== '' && $rawProductId !== 'semua' && is_numeric($rawProductId) && (int) $rawProductId > 0) {
            $productId = (int) $rawProductId;
        }

        $rawJenis = strtolower(trim((string) ($request->getGet('jenis') ?? '')));
        $validJenis = ['login', 'pencarian', 'detail', 'komisi', 'pdf'];
        $jenis = in_array($rawJenis, $validJenis, true) ? $rawJenis : '';

        $page = max(1, (int) ($request->getGet('page') ?? 1));

        return [
            'start_date' => $startDate,
            'end_date'   => $endDate,
            'mode'       => $mode,
            'sales_id'   => $salesId,
            'product_id' => $productId,
            'jenis'      => $jenis,
            'page'       => $page,
        ];
    }

    /**
     * Cek apakah filter Mode sedang aktif (bukan 'semua' / bukan kosong)
     */
    public static function isModeActive(array $filters): bool
    {
        return !empty($filters['mode']) && in_array($filters['mode'], ['internal', 'eksternal'], true);
    }

    /**
     * Cek apakah filter Produk sedang aktif
     */
    public static function isProductActive(array $filters): bool
    {
        return !empty($filters['product_id']) && (int) $filters['product_id'] > 0;
    }

    /**
     * Cek apakah filter Sales sedang aktif
     */
    public static function isSalesActive(array $filters): bool
    {
        return !empty($filters['sales_id']) && (int) $filters['sales_id'] > 0;
    }

    /**
     * Cek apakah filter Jenis Aktivitas sedang aktif
     */
    public static function isJenisActive(array $filters): bool
    {
        return !empty($filters['jenis']) && in_array($filters['jenis'], ['login', 'pencarian', 'detail', 'komisi', 'pdf'], true);
    }
}
