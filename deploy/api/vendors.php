<?php
require_once 'config.php';
requireAuth();

$method = $_SERVER['REQUEST_METHOD'];
$conn = getConnection();

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            getVendor($conn, (int)$_GET['id']);
        } else {
            getVendors($conn);
        }
        break;
        
    case 'POST':
        requireAdmin();
        createVendor($conn);
        break;
        
    case 'PUT':
        requireAdmin();
        updateVendor($conn);
        break;
        
    case 'DELETE':
        requireAdmin();
        deleteVendor($conn);
        break;
        
    default:
        jsonResponse(false, 'Method not allowed', null, 405);
}

function getVendors($conn) {
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $offset = ($page - 1) * $limit;
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    
    // Build query
    $where = '';
    $params = [];
    $types = '';
    
    if (!empty($search)) {
        $where = "WHERE name LIKE ? OR npwp LIKE ? OR email LIKE ? OR contact_person LIKE ?";
        $searchTerm = "%{$search}%";
        $params = [$searchTerm, $searchTerm, $searchTerm, $searchTerm];
        $types = 'ssss';
    }
    
    // Get total count
    $countQuery = "SELECT COUNT(*) as total FROM vendors {$where}";
    $stmt = $conn->prepare($countQuery);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $total = $stmt->get_result()->fetch_assoc()['total'];
    
    // Get data
    $query = "SELECT * FROM vendors {$where} ORDER BY id DESC LIMIT ? OFFSET ?";
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
    $vendors = [];
    
    while ($row = $result->fetch_assoc()) {
        $vendors[] = $row;
    }
    
    jsonResponse(true, 'Vendors retrieved', [
        'vendors' => $vendors,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'total_pages' => ceil($total / $limit)
        ]
    ]);
}

function getVendor($conn, $id) {
    $stmt = $conn->prepare("SELECT * FROM vendors WHERE id = ? LIMIT 1");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        jsonResponse(false, 'Vendor not found', null, 404);
    }
    
    $vendor = $result->fetch_assoc();
    jsonResponse(true, 'Vendor retrieved', $vendor);
}

function createVendor($conn) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $name = isset($input['name']) ? sanitize($input['name']) : '';
    $address = isset($input['address']) ? sanitize($input['address']) : '';
    $npwp = isset($input['npwp']) ? sanitize($input['npwp']) : '';
    $phone = isset($input['phone']) ? sanitize($input['phone']) : '';
    $contact_person = isset($input['contact_person']) ? sanitize($input['contact_person']) : '';
    $email = isset($input['email']) ? sanitize($input['email']) : '';
    
    if (empty($name)) {
        jsonResponse(false, 'Vendor name is required');
    }
    
    $stmt = $conn->prepare("INSERT INTO vendors (name, address, npwp, phone, contact_person, email) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('ssssss', $name, $address, $npwp, $phone, $contact_person, $email);
    
    if ($stmt->execute()) {
        jsonResponse(true, 'Vendor created successfully', ['id' => $stmt->insert_id]);
    } else {
        jsonResponse(false, 'Failed to create vendor: ' . $conn->error);
    }
}

function updateVendor($conn) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['id'])) {
        jsonResponse(false, 'Vendor ID is required');
    }
    
    $id = (int)$input['id'];
    $name = isset($input['name']) ? sanitize($input['name']) : '';
    $address = isset($input['address']) ? sanitize($input['address']) : '';
    $npwp = isset($input['npwp']) ? sanitize($input['npwp']) : '';
    $phone = isset($input['phone']) ? sanitize($input['phone']) : '';
    $contact_person = isset($input['contact_person']) ? sanitize($input['contact_person']) : '';
    $email = isset($input['email']) ? sanitize($input['email']) : '';
    
    if (empty($name)) {
        jsonResponse(false, 'Vendor name is required');
    }
    
    $stmt = $conn->prepare("UPDATE vendors SET name=?, address=?, npwp=?, phone=?, contact_person=?, email=? WHERE id=?");
    $stmt->bind_param('ssssssi', $name, $address, $npwp, $phone, $contact_person, $email, $id);
    
    if ($stmt->execute()) {
        jsonResponse(true, 'Vendor updated successfully');
    } else {
        jsonResponse(false, 'Failed to update vendor: ' . $conn->error);
    }
}

function deleteVendor($conn) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['id'])) {
        jsonResponse(false, 'Vendor ID is required');
    }
    
    $id = (int)$input['id'];
    
    $stmt = $conn->prepare("DELETE FROM vendors WHERE id = ?");
    $stmt->bind_param('i', $id);
    
    if ($stmt->execute()) {
        jsonResponse(true, 'Vendor deleted successfully');
    } else {
        jsonResponse(false, 'Failed to delete vendor: ' . $conn->error);
    }
}
