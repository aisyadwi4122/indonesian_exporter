<?php
require 'vendor/autoload.php';

try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=k4991560_indonesian-exporter;charset=utf8mb4', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $tables = ['tb_supplier', 'tb_sales', 'tb_produk_supplier', 'tb_history_export_supplier', 'tb_ecp_ekspor', 'tb_supplier_produk'];
    foreach ($tables as $t) {
        $check = $pdo->query("SHOW TABLES LIKE '$t'")->fetch();
        if (!$check) {
            echo "Table $t DOES NOT EXIST.\n";
            continue;
        }
        $count = $pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
        echo "=== Table $t (Rows: $count) ===\n";
        $cols = $pdo->query("SHOW COLUMNS FROM `$t`")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($cols as $c) {
            echo "  {$c['Field']} | {$c['Type']} | Null: {$c['Null']} | Key: {$c['Key']} | Default: {$c['Default']}\n";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

