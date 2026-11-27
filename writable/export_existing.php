<?php
require 'vendor/autoload.php';

try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=k4991560_indonesian-exporter;charset=utf8mb4', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->query("SELECT id_supplier, nama_perusahaan, nama_kunci, email, no_hp FROM tb_supplier ORDER BY id_supplier ASC");
    $suppliers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    file_put_contents('writable/existing_suppliers.json', json_encode($suppliers, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    echo "Saved " . count($suppliers) . " existing suppliers to writable/existing_suppliers.json\n";
    
    // Also check tb_sales
    $stmtSales = $pdo->query("SELECT * FROM tb_sales");
    $sales = $stmtSales->fetchAll(PDO::FETCH_ASSOC);
    file_put_contents('writable/existing_sales.json', json_encode($sales, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    echo "Saved " . count($sales) . " existing sales to writable/existing_sales.json\n";
    
    // Also check tb_produk_supplier
    $stmtProd = $pdo->query("SELECT * FROM tb_produk_supplier");
    $prods = $stmtProd->fetchAll(PDO::FETCH_ASSOC);
    file_put_contents('writable/existing_produk.json', json_encode($prods, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    echo "Saved " . count($prods) . " existing produk to writable/existing_produk.json\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

