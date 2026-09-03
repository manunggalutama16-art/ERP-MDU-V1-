<?php
require_once 'config.php';
requireAuth();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        getReportData();
        break;
        
    default:
        jsonResponse(false, 'Method not allowed', null, 405);
}

function getReportData() {
    $conn = getConnection();
    
    $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
    $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');
    $project_id = isset($_GET['project_id']) ? (int)$_GET['project_id'] : 0;
    $status = isset($_GET['status']) ? $_GET['status'] : '';
    
    $where = "WHERE po.created_at BETWEEN ? AND ?";
    $params = [$start_date . ' 00:00:00', $end_date . ' 23:59:59'];
    $types = 'ss';
    
    if ($project_id > 0) {
        $where .= " AND po.project_id = ?";
        $params[] = $project_id;
        $types .= 'i';
    }
    
    if (!empty($status)) {
        $where .= " AND po.status = ?";
        $params[] = $status;
        $types .= 's';
    }
    
    // Summary stats
    $summaryQuery = "SELECT 
                        COUNT(*) as total_po,
                        SUM(po.grand_total) as total_value,
                        SUM(CASE WHEN po.status = 'Signed' THEN 1 ELSE 0 END) as signed_count,
                        SUM(CASE WHEN po.status = 'Draft' THEN 1 ELSE 0 END) as draft_count,
                        COUNT(DISTINCT po.vendor_id) as active_vendors
                    FROM purchase_orders po {$where}";
    
    $stmt = $conn->prepare($summaryQuery);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $summary = $stmt->get_result()->fetch_assoc();
    
    // Detailed data
    $detailQuery = "SELECT po.*, v.name as vendor_name, p.name as project_name, p.code as project_code
                    FROM purchase_orders po
                    LEFT JOIN vendors v ON po.vendor_id = v.id
                    LEFT JOIN projects p ON po.project_id = p.id
                    {$where}
                    ORDER BY po.created_at DESC";
    
    $stmt = $conn->prepare($detailQuery);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    $details = [];
    
    while ($row = $result->fetch_assoc()) {
        $details[] = $row;
    }
    
    // Projects for filter
    $projectsStmt = $conn->query("SELECT id, code, name FROM projects ORDER BY name ASC");
    $projects = [];
    while ($row = $projectsStmt->fetch_assoc()) {
        $projects[] = $row;
    }
    
    jsonResponse(true, 'Report data retrieved', [
        'summary' => $summary,
        'details' => $details,
        'projects' => $projects,
        'filters' => [
            'start_date' => $start_date,
            'end_date' => $end_date,
            'project_id' => $project_id,
            'status' => $status
        ]
    ]);
}
