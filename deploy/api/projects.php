<?php
require_once 'config.php';
requireAuth();

$method = $_SERVER['REQUEST_METHOD'];
$conn = getConnection();

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            getProject($conn, (int)$_GET['id']);
        } else {
            getProjects($conn);
        }
        break;
        
    case 'POST':
        requireAdmin();
        createProject($conn);
        break;
        
    case 'PUT':
        requireAdmin();
        updateProject($conn);
        break;
        
    case 'DELETE':
        requireAdmin();
        deleteProject($conn);
        break;
        
    default:
        jsonResponse(false, 'Method not allowed', null, 405);
}

function getProjects($conn) {
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $offset = ($page - 1) * $limit;
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $status = isset($_GET['status']) ? $_GET['status'] : '';
    
    $where = '';
    $params = [];
    $paramIndex = 1;
    
    if (!empty($search)) {
        $where = "WHERE (code ILIKE ${$paramIndex} OR name ILIKE ${$paramIndex} OR client ILIKE ${$paramIndex} OR pic ILIKE ${$paramIndex})";
        $params[] = "%{$search}%";
        $paramIndex++;
    }
    
    if (!empty($status)) {
        $where .= empty($where) ? "WHERE status = ${$paramIndex}" : " AND status = ${$paramIndex}";
        $params[] = $status;
        $paramIndex++;
    }
    
    // Get total count
    $countQuery = "SELECT COUNT(*) as total FROM projects {$where}";
    $result = pg_query_params($conn, $countQuery, $params);
    $total = pg_fetch_assoc($result)['total'];
    
    // Get data
    $query = "SELECT * FROM projects {$where} ORDER BY id DESC LIMIT ${$paramIndex} OFFSET ${$paramIndex}";
    $params[] = $limit;
    $params[] = $offset;
    
    $result = pg_query_params($conn, $query, $params);
    $projects = pg_fetch_all_assoc($result);
    
    jsonResponse(true, 'Projects retrieved', [
        'projects' => $projects,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => (int)$total,
            'total_pages' => ceil($total / $limit)
        ]
    ]);
}

function getProject($conn, $id) {
    $result = pg_query_params($conn, "SELECT * FROM projects WHERE id = $1 LIMIT 1", [$id]);
    
    if (pg_num_rows($result) === 0) {
        jsonResponse(false, 'Project not found', null, 404);
    }
    
    $project = pg_fetch_assoc($result);
    jsonResponse(true, 'Project retrieved', $project);
}

function createProject($conn) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $code = isset($input['code']) ? sanitize($input['code']) : '';
    $name = isset($input['name']) ? sanitize($input['name']) : '';
    $location = isset($input['location']) ? sanitize($input['location']) : '';
    $client = isset($input['client']) ? sanitize($input['client']) : '';
    $pic = isset($input['pic']) ? sanitize($input['pic']) : '';
    $value_before_ppn = isset($input['value_before_ppn']) ? (float)$input['value_before_ppn'] : 0;
    $value_inc_ppn = isset($input['value_inc_ppn']) ? (float)$input['value_inc_ppn'] : 0;
    $status = isset($input['status']) ? sanitize($input['status']) : 'PENDING';
    
    if (empty($code) || empty($name)) {
        jsonResponse(false, 'Project code and name are required');
    }
    
    $result = pg_query_params($conn,
        "INSERT INTO projects (code, name, location, client, pic, value_before_ppn, value_inc_ppn, status) VALUES ($1, $2, $3, $4, $5, $6, $7, $8) RETURNING id",
        [$code, $name, $location, $client, $pic, $value_before_ppn, $value_inc_ppn, $status]
    );
    
    if ($result) {
        $id = pg_fetch_assoc($result)['id'];
        jsonResponse(true, 'Project created successfully', ['id' => $id]);
    } else {
        jsonResponse(false, 'Failed to create project');
    }
}

function updateProject($conn) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['id'])) {
        jsonResponse(false, 'Project ID is required');
    }
    
    $id = (int)$input['id'];
    $code = isset($input['code']) ? sanitize($input['code']) : '';
    $name = isset($input['name']) ? sanitize($input['name']) : '';
    $location = isset($input['location']) ? sanitize($input['location']) : '';
    $client = isset($input['client']) ? sanitize($input['client']) : '';
    $pic = isset($input['pic']) ? sanitize($input['pic']) : '';
    $value_before_ppn = isset($input['value_before_ppn']) ? (float)$input['value_before_ppn'] : 0;
    $value_inc_ppn = isset($input['value_inc_ppn']) ? (float)$input['value_inc_ppn'] : 0;
    $status = isset($input['status']) ? sanitize($input['status']) : 'PENDING';
    
    $result = pg_query_params($conn,
        "UPDATE projects SET code=$1, name=$2, location=$3, client=$4, pic=$5, value_before_ppn=$6, value_inc_ppn=$7, status=$8 WHERE id=$9",
        [$code, $name, $location, $client, $pic, $value_before_ppn, $value_inc_ppn, $status, $id]
    );
    
    if ($result) {
        jsonResponse(true, 'Project updated successfully');
    } else {
        jsonResponse(false, 'Failed to update project');
    }
}

function deleteProject($conn) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['id'])) {
        jsonResponse(false, 'Project ID is required');
    }
    
    $id = (int)$input['id'];
    
    $result = pg_query_params($conn, "DELETE FROM projects WHERE id = $1", [$id]);
    
    if ($result) {
        jsonResponse(true, 'Project deleted successfully');
    } else {
        jsonResponse(false, 'Failed to delete project');
    }
}
