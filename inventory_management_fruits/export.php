<?php
// export.php
require_once 'config/database.php';
requireLogin();

$format = $_GET['format'] ?? 'csv';
$pdo = getDBConnection();

if ($format === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="fruits_inventory.csv"');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID', 'Name', 'Category', 'Origin', 'Unit', 'Price', 'Cost', 'Stock', 'Min Stock', 'Season']);
    $stmt = $pdo->query("SELECT * FROM products ORDER BY name");
    while ($row = $stmt->fetch()) {
        fputcsv($output, [$row['id'], $row['name'], $row['category'], $row['origin'], 
                          $row['unit'], $row['price'], $row['cost'], $row['stock'], 
                          $row['min_stock'], $row['season']]);
    }
    fclose($output);
    exit;
} elseif ($format === 'json') {
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="fruits_inventory.json"');
    $stmt = $pdo->query("SELECT * FROM products ORDER BY name");
    echo json_encode($stmt->fetchAll(), JSON_PRETTY_PRINT);
    exit;
}