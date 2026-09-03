<?php
require_once 'config.php';
requireAuth();

$conn = getConnection();

// Generate PO Number
$year = date('Y');
$month = date('m');
$prefix = "PO-{$year}{$month}-";

$result = pg_query_params($conn, 
    "SELECT po_number FROM purchase_orders WHERE po_number LIKE $1 ORDER BY id DESC LIMIT 1",
    [$prefix . '%']
);

if ($result && pg_num_rows($result) > 0) {
    $row = pg_fetch_assoc($result);
    $lastNumber = (int)str_replace($prefix, '', $row['po_number']);
    $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
} else {
    $newNumber = '001';
}

$po_number = $prefix . $newNumber;

jsonResponse(true, 'PO Number generated', ['po_number' => $po_number]);
