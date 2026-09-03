<?php
require_once 'config.php';
requireAuth();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        getDashboardStats();
        break;
    default:
        jsonResponse(false, 'Method not allowed', null, 405);
}

function getDashboardStats() {
    $conn = getConnection();
    
    // Total Active POs
    $result = $conn->query("SELECT COUNT(*) as total FROM purchase_orders WHERE status != 'Completed'");
    $totalActivePO = $result->fetch_assoc()['total'];
    
    // Pending Signed POs
    $result = $conn->query("SELECT COUNT(*) as total FROM purchase_orders WHERE status = 'Signed'");
    $pendingSigned = $result->fetch_assoc()['total'];
    
    // Total Value
    $result = $conn->query("SELECT COALESCE(SUM(grand_total), 0) as total FROM purchase_orders WHERE status != 'Draft'");
    $totalValue = $result->fetch_assoc()['total'];
    
    // Active Vendors (vendors that have at least one PO)
    $result = $conn->query("SELECT COUNT(DISTINCT vendor_id) as total FROM purchase_orders WHERE vendor_id IS NOT NULL");
    $activeVendors = $result->fetch_assoc()['total'];
    
    // Total Vendors
    $result = $conn->query("SELECT COUNT(*) as total FROM vendors");
    $totalVendors = $result->fetch_assoc()['total'];
    
    // Recent Activity (last 5 PO activities)
    $result = $conn->query("
        SELECT a.*, u.name as user_name, po.po_number 
        FROM po_activity_log a 
        LEFT JOIN users u ON a.created_by = u.id 
        LEFT JOIN purchase_orders po ON a.po_id = po.id 
        ORDER BY a.created_at DESC 
        LIMIT 5
    ");
    $recentActivity = [];
    while ($row = $result->fetch_assoc()) {
        $recentActivity[] = $row;
    }
    
    // Monthly PO stats for last 6 months
    $result = $conn->query("
        SELECT 
            DATE_FORMAT(created_at, '%Y-%m') as month,
            COUNT(*) as count,
            COALESCE(SUM(grand_total), 0) as value
        FROM purchase_orders 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(created_at, '%Y-%m')
        ORDER BY month ASC
    ");
    $monthlyStats = [];
    while ($row = $result->fetch_assoc()) {
        $monthlyStats[] = $row;
    }
    
    jsonResponse(true, 'Dashboard stats retrieved', [
        'summary' => [
            'total_active_po' => (int)$totalActivePO,
            'pending_signed' => (int)$pendingSigned,
            'total_value' => (float)$totalValue,
            'active_vendors' => (int)$activeVendors,
            'total_vendors' => (int)$totalVendors
        ],
        'recent_activity' => $recentActivity,
        'monthly_stats' => $monthlyStats
    ]);
}
