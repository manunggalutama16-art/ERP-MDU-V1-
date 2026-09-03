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
    $types = '';
    
    if (!empty($search)) {
        $where = "WHERE code LIKE ? OR name LIKE ? OR client LIKE ? OR pic LIKE ?";
        $searchTerm = "%{$search}%";
        $params = [$searchTerm, $searchTerm, $searchTerm, $searchTerm];
        $types = 'ssss';
    }
    
    if (!empty($status)) {
        $where .= empty($where) ? 'WHERE status = ?' : ' AND status = ?';
        $params[] = $status;
        $types .= 's';
    }
    
    $countQuery = "SELECT COUNT(*) as total FROM projects {$where}";
    $stmt = $conn->prepare($countQuery);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $total = $stmt->get_result()->fetch_assoc()['total'];
    
    $query = "SELECT * FROM projects {$where} ORDER BY id DESC LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($query);
    
    if (!empty($params)) {
        $params[] = $limit;
        $params[] = $offset;
        $types .= 'ii';
        $stmt->bind_param($types, ...$params);
    } else {
        $stmt->bind_param('ii', $limit, $offset);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $projects = [];
    
    while ($row = $result->fetch_assoc()) {
        $projects[] = $row;
    }
    
    jsonResponse(true, 'Projects retrieved', [
        'projects' => $projects,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'total_pages' => ceil($total / $limit)
        ]
    ]);
}

function getProject($conn, $id) {
    $stmt = $conn->prepare("SELECT * FROM projects WHERE id = ? LIMIT 1");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        jsonResponse(false, 'Project not found', null, 404);
    }
    
    $project = $result->fetch_assoc();
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
    
    $stmt = $conn->prepare("INSERT INTO projects (code, name, location, client, pic, value_before_ppn, value_inc_ppn, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('sssssdds', $code, $name, $location, $client, $pic, $value_before_ppn, $value_inc_ppn, $status);
    
    if ($stmt->execute()) {
        jsonResponse(true, 'Project created successfully', ['id' => $stmt->insert_id]);
    } else {
        jsonResponse(false, 'Failed to create project: ' . $conn->error);
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
    
    $stmt = $conn->prepare("UPDATE projects SET code=?, name=?, location=?, client=?, pic=?, value_before_ppn=?, value_inc_ppn=?, status=? WHERE id=?");
    $stmt->bind_param('sssssddsi', $code, $name, $location, $client, $pic, $value_before_ppn, $value_inc_ppn, $status, $id);
    
    if ($stmt->execute()) {
        jsonResponse(true, 'Project updated successfully');
    } else {
        jsonResponse(false, 'Failed to update project: ' . $conn->error);
    }
}

function deleteProject($conn) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['id'])) {
        jsonResponse(false, 'Project ID is required');
    }
    
    $id = (int)$input['id'];
    
    $stmt = $conn->prepare("DELETE FROM projects WHERE id = ?");
    $stmt->bind_param('i', $id);
    
    if ($stmt->execute()) {
        jsonResponse(true, 'Project deleted successfully');
    } else {
        jsonResponse(false, 'Failed to delete project: ' . $conn->error);
    }
}
