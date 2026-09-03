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
    
    $where = "WHERE po.created_at >= $1 AND po.created_at <= $2";
    $params = [$start_date . ' 00:00:00', $end_date . ' 23:59:59'];
    $paramIndex = 3;
    
    if ($project_id > 0) {
        $where .= " AND po.project_id = ${$paramIndex}";
        $params[] = $project_id;
        $paramIndex++;
    }
    
    if (!empty($status)) {
        $where .= " AND po.status = ${$paramIndex}";
        $params[] = $status;
        $paramIndex++;
    }
    
    // Summary stats
    $summaryQuery = "SELECT 
                        COUNT(*) as total_po,
                        COALESCE(SUM(po.grand_total), 0) as total_value,
                        SUM(CASE WHEN po.status = 'Signed' THEN 1 ELSE 0 END) as signed_count,
                        SUM(CASE WHEN po.status = 'Draft' THEN 1 ELSE 0 END) as draft_count,
                        COUNT(DISTINCT po.vendor_id) as active_vendors
                    FROM purchase_orders po {$where}";
    
    $result = pg_query_params($conn, $summaryQuery, $params);
    $summary = pg_fetch_assoc($result);
    
    // Detailed data
    $detailQuery = "SELECT po.*, v.name as vendor_name, p.name as project_name, p.code as project_code
                    FROM purchase_orders po
                    LEFT JOIN vendors v ON po.vendor_id = v.id
                    LEFT JOIN projects p ON po.project_id = p.id
                    {$where}
                    ORDER BY po.created_at DESC";
    
    $result = pg_query_params($conn, $detailQuery, $params);
    $details = pg_fetch_all_assoc($result);
    
    // Projects for filter
    $projectsResult = pg_query($conn, "SELECT id, code, name FROM projects ORDER BY name ASC");
    $projects = pg_fetch_all_assoc($projectsResult);
    
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
