<?php
require_once 'config.php';
requireAuth();

$conn = getConnection();

// Generate PO Number
$year = date('Y');
$month = date('m');
$prefix = "PO-{$year}{$month}-";

$result = $conn->query("SELECT po_number FROM purchase_orders WHERE po_number LIKE '{$prefix}%' ORDER BY id DESC LIMIT 1");
if ($result && $row = $result->fetch_assoc()) {
    $lastNumber = (int)str_replace($prefix, '', $row['po_number']);
    $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
} else {
    $newNumber = '001';
}

$po_number = $prefix . $newNumber;

jsonResponse(true, 'PO Number generated', ['po_number' => $po_number]);
