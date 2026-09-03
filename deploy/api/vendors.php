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
    
    $where = '';
    $params = [];
    
    if (!empty($search)) {
        $where = "WHERE name ILIKE $1 OR npwp ILIKE $1 OR email ILIKE $1 OR contact_person ILIKE $1";
        $params[] = "%{$search}%";
    }
    
    // Get total count
    $countQuery = "SELECT COUNT(*) as total FROM vendors {$where}";
    $result = pg_query_params($conn, $countQuery, $params);
    $total = pg_fetch_assoc($result)['total'];
    
    // Get data
    $query = "SELECT * FROM vendors {$where} ORDER BY id DESC LIMIT $".(count($params)+1)." OFFSET $".(count($params)+2);
    $params[] = $limit;
    $params[] = $offset;
    
    $result = pg_query_params($conn, $query, $params);
    $vendors = pg_fetch_all_assoc($result);
    
    jsonResponse(true, 'Vendors retrieved', [
        'vendors' => $vendors,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => (int)$total,
            'total_pages' => ceil($total / $limit)
        ]
    ]);
}

function getVendor($conn, $id) {
    $result = pg_query_params($conn, "SELECT * FROM vendors WHERE id = $1 LIMIT 1", [$id]);
    
    if (pg_num_rows($result) === 0) {
        jsonResponse(false, 'Vendor not found', null, 404);
    }
    
    $vendor = pg_fetch_assoc($result);
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
    
    $result = pg_query_params($conn,
        "INSERT INTO vendors (name, address, npwp, phone, contact_person, email) VALUES ($1, $2, $3, $4, $5, $6) RETURNING id",
        [$name, $address, $npwp, $phone, $contact_person, $email]
    );
    
    if ($result) {
        $id = pg_fetch_assoc($result)['id'];
        jsonResponse(true, 'Vendor created successfully', ['id' => $id]);
    } else {
        jsonResponse(false, 'Failed to create vendor');
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
    
    $result = pg_query_params($conn,
        "UPDATE vendors SET name=$1, address=$2, npwp=$3, phone=$4, contact_person=$5, email=$6 WHERE id=$7",
        [$name, $address, $npwp, $phone, $contact_person, $email, $id]
    );
    
    if ($result) {
        jsonResponse(true, 'Vendor updated successfully');
    } else {
        jsonResponse(false, 'Failed to update vendor');
    }
}

function deleteVendor($conn) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['id'])) {
        jsonResponse(false, 'Vendor ID is required');
    }
    
    $id = (int)$input['id'];
    
    $result = pg_query_params($conn, "DELETE FROM vendors WHERE id = $1", [$id]);
    
    if ($result) {
        jsonResponse(true, 'Vendor deleted successfully');
    } else {
        jsonResponse(false, 'Failed to delete vendor');
    }
}
