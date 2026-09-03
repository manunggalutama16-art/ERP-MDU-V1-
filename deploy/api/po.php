<?php
require_once 'config.php';
requireAuth();

$method = $_SERVER['REQUEST_METHOD'];
$conn = getConnection();

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            getPO($conn, (int)$_GET['id']);
        } else {
            getPOs($conn);
        }
        break;
        
    case 'POST':
        requireAdmin();
        createPO($conn);
        break;
        
    case 'PUT':
        requireAdmin();
        $putInput = json_decode(file_get_contents('php://input'), true);
        if (isset($putInput['action']) && $putInput['action'] === 'status') {
            updatePOStatus($conn, $putInput);
        } else {
            updatePO($conn, $putInput);
        }
        break;
        
    case 'DELETE':
        requireAdmin();
        deletePO($conn);
        break;
        
    default:
        jsonResponse(false, 'Method not allowed', null, 405);
}

function getPOs($conn) {
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $offset = ($page - 1) * $limit;
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $status = isset($_GET['status']) ? $_GET['status'] : '';
    
    $where = '';
    $params = [];
    $types = '';
    
    if (!empty($search)) {
        $where = "WHERE po.po_number LIKE ? OR v.name LIKE ? OR p.name LIKE ?";
        $searchTerm = "%{$search}%";
        $params = [$searchTerm, $searchTerm, $searchTerm];
        $types = 'sss';
    }
    
    if (!empty($status)) {
        $where .= empty($where) ? 'WHERE po.status = ?' : ' AND po.status = ?';
        $params[] = $status;
        $types .= 's';
    }
    
    $countQuery = "SELECT COUNT(*) as total FROM purchase_orders po 
                   LEFT JOIN vendors v ON po.vendor_id = v.id 
                   LEFT JOIN projects p ON po.project_id = p.id {$where}";
    $stmt = $conn->prepare($countQuery);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $total = $stmt->get_result()->fetch_assoc()['total'];
    
    $query = "SELECT po.*, v.name as vendor_name, v.contact_person, v.phone, v.email as vendor_email,
                     p.name as project_name, p.code as project_code, p.location as project_location,
                     u.name as created_by_name
              FROM purchase_orders po 
              LEFT JOIN vendors v ON po.vendor_id = v.id 
              LEFT JOIN projects p ON po.project_id = p.id 
              LEFT JOIN users u ON po.created_by = u.id
              {$where} 
              ORDER BY po.id DESC LIMIT ? OFFSET ?";
    
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
    $pos = [];
    
    while ($row = $result->fetch_assoc()) {
        $pos[] = $row;
    }
    
    jsonResponse(true, 'Purchase Orders retrieved', [
        'pos' => $pos,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'total_pages' => ceil($total / $limit)
        ]
    ]);
}

function getPO($conn, $id) {
    $stmt = $conn->prepare("SELECT po.*, v.name as vendor_name, v.address as vendor_address, v.npwp as vendor_npwp, v.contact_person, v.phone, v.email as vendor_email,
                                   p.name as project_name, p.code as project_code, p.location as project_location, p.client as project_client,
                                   u.name as created_by_name
                            FROM purchase_orders po 
                            LEFT JOIN vendors v ON po.vendor_id = v.id 
                            LEFT JOIN projects p ON po.project_id = p.id 
                            LEFT JOIN users u ON po.created_by = u.id
                            WHERE po.id = ? LIMIT 1");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        jsonResponse(false, 'Purchase Order not found', null, 404);
    }
    
    $po = $result->fetch_assoc();
    
    // Get items
    $itemsStmt = $conn->prepare("SELECT * FROM po_items WHERE po_id = ? ORDER BY sort_order ASC, id ASC");
    $itemsStmt->bind_param('i', $id);
    $itemsStmt->execute();
    $itemsResult = $itemsStmt->get_result();
    $po['items'] = [];
    
    while ($item = $itemsResult->fetch_assoc()) {
        $po['items'][] = $item;
    }
    
    // Get attachments
    $attStmt = $conn->prepare("SELECT * FROM po_attachments WHERE po_id = ?");
    $attStmt->bind_param('i', $id);
    $attStmt->execute();
    $attResult = $attStmt->get_result();
    $po['attachments'] = [];
    
    while ($att = $attResult->fetch_assoc()) {
        $po['attachments'][] = $att;
    }
    
    jsonResponse(true, 'Purchase Order retrieved', $po);
}

function createPO($conn) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $po_number = generatePONumber($conn);
    $vendor_id = isset($input['vendor_id']) ? (int)$input['vendor_id'] : null;
    $project_id = isset($input['project_id']) ? (int)$input['project_id'] : null;
    $top = isset($input['top']) ? sanitize($input['top']) : '';
    $delivery_location = isset($input['delivery_location']) ? sanitize($input['delivery_location']) : '';
    $status = isset($input['status']) ? sanitize($input['status']) : 'Draft';
    $quotation_attached = isset($input['quotation_attached']) ? ($input['quotation_attached'] ? 1 : 0) : 0;
    $approved = isset($input['approved']) ? ($input['approved'] ? 1 : 0) : 0;
    $notes = isset($input['notes']) ? sanitize($input['notes']) : '';
    $items = isset($input['items']) ? $input['items'] : [];
    $created_by = $_SESSION['user_id'];
    $ppn_type = isset($input['ppn_type']) ? sanitize($input['ppn_type']) : 'ppn';
    
    $conn->begin_transaction();
    
    try {
        // Calculate totals
        $subtotal = 0;
        foreach ($items as $item) {
            $subtotal += ((float)$item['quantity'] * (float)$item['price']);
        }
        
        // PPN is optional; the form sends ppn_type = 'ppn' | 'non'
        $ppn_percent = ($ppn_type === 'non') ? 0.00 : 11.00;
        $ppn_amount = $subtotal * ($ppn_percent / 100);
        $grand_total = $subtotal + $ppn_amount;
        
        $stmt = $conn->prepare("INSERT INTO purchase_orders (po_number, vendor_id, project_id, top, delivery_location, status, quotation_attached, approved, subtotal, ppn_percent, ppn_amount, grand_total, notes, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('siisssiiddddsi', $po_number, $vendor_id, $project_id, $top, $delivery_location, $status, $quotation_attached, $approved, $subtotal, $ppn_percent, $ppn_amount, $grand_total, $notes, $created_by);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to create PO: ' . $conn->error);
        }
        
        $po_id = $stmt->insert_id;
        
        // Insert items
        $sort_order = 1;
        foreach ($items as $item) {
            $item_name = sanitize($item['item_name']);
            $quantity = (float)$item['quantity'];
            $unit = isset($item['unit']) ? sanitize($item['unit']) : 'Pcs';
            $price = (float)$item['price'];
            
            $itemStmt = $conn->prepare("INSERT INTO po_items (po_id, item_name, quantity, unit, price, sort_order) VALUES (?, ?, ?, ?, ?, ?)");
            $itemStmt->bind_param('isdsdi', $po_id, $item_name, $quantity, $unit, $price, $sort_order);
            
            if (!$itemStmt->execute()) {
                throw new Exception('Failed to create PO item: ' . $conn->error);
            }
            
            $sort_order++;
        }
        
        $conn->commit();
        
        logPoActivity($conn, $po_id, 'created', 'PO ' . $po_number . ' dibuat');
        
        jsonResponse(true, 'Purchase Order created successfully', [
            'id' => $po_id,
            'po_number' => $po_number
        ]);
        
    } catch (Exception $e) {
        $conn->rollback();
        jsonResponse(false, 'Failed to create PO: ' . $e->getMessage());
    }
}

function updatePO($conn, $input = null) {
    if ($input === null) {
        $input = json_decode(file_get_contents('php://input'), true);
    }
    
    if (!isset($input['id'])) {
        jsonResponse(false, 'PO ID is required');
    }
    
    $id = (int)$input['id'];
    
    // Check if PO exists
    $checkStmt = $conn->prepare("SELECT id FROM purchase_orders WHERE id = ? LIMIT 1");
    $checkStmt->bind_param('i', $id);
    $checkStmt->execute();
    if ($checkStmt->get_result()->num_rows === 0) {
        jsonResponse(false, 'Purchase Order not found', null, 404);
    }
    
    $vendor_id = isset($input['vendor_id']) ? (int)$input['vendor_id'] : null;
    $project_id = isset($input['project_id']) ? (int)$input['project_id'] : null;
    $top = isset($input['top']) ? sanitize($input['top']) : '';
    $delivery_location = isset($input['delivery_location']) ? sanitize($input['delivery_location']) : '';
    $status = isset($input['status']) ? sanitize($input['status']) : 'Draft';
    $quotation_attached = isset($input['quotation_attached']) ? ($input['quotation_attached'] ? 1 : 0) : 0;
    $approved = isset($input['approved']) ? ($input['approved'] ? 1 : 0) : 0;
    $notes = isset($input['notes']) ? sanitize($input['notes']) : '';
    $items = isset($input['items']) ? $input['items'] : [];
    $ppn_type = isset($input['ppn_type']) ? sanitize($input['ppn_type']) : 'ppn';
    
    $conn->begin_transaction();
    
    try {
        // Calculate totals
        $subtotal = 0;
        foreach ($items as $item) {
            $subtotal += ((float)$item['quantity'] * (float)$item['price']);
        }
        
        // PPN is optional; the form sends ppn_type = 'ppn' | 'non'
        $ppn_percent = ($ppn_type === 'non') ? 0.00 : 11.00;
        $ppn_amount = $subtotal * ($ppn_percent / 100);
        $grand_total = $subtotal + $ppn_amount;
        
        $stmt = $conn->prepare("UPDATE purchase_orders SET vendor_id=?, project_id=?, top=?, delivery_location=?, status=?, quotation_attached=?, approved=?, subtotal=?, ppn_percent=?, ppn_amount=?, grand_total=?, notes=? WHERE id=?");
        $stmt->bind_param('iisssiiddddsi', $vendor_id, $project_id, $top, $delivery_location, $status, $quotation_attached, $approved, $subtotal, $ppn_percent, $ppn_amount, $grand_total, $notes, $id);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to update PO: ' . $conn->error);
        }
        
        // Delete old items
        $delStmt = $conn->prepare("DELETE FROM po_items WHERE po_id = ?");
        $delStmt->bind_param('i', $id);
        $delStmt->execute();
        
        // Insert new items
        $sort_order = 1;
        foreach ($items as $item) {
            $item_name = sanitize($item['item_name']);
            $quantity = (float)$item['quantity'];
            $unit = isset($item['unit']) ? sanitize($item['unit']) : 'Pcs';
            $price = (float)$item['price'];
            
            $itemStmt = $conn->prepare("INSERT INTO po_items (po_id, item_name, quantity, unit, price, sort_order) VALUES (?, ?, ?, ?, ?, ?)");
            $itemStmt->bind_param('isdsdi', $id, $item_name, $quantity, $unit, $price, $sort_order);
            
            if (!$itemStmt->execute()) {
                throw new Exception('Failed to update PO item: ' . $conn->error);
            }
            
            $sort_order++;
        }
        
        $conn->commit();
        
        logPoActivity($conn, $id, 'updated', 'Detail PO diperbarui');
        
        jsonResponse(true, 'Purchase Order updated successfully');
        
    } catch (Exception $e) {
        $conn->rollback();
        jsonResponse(false, 'Failed to update PO: ' . $e->getMessage());
    }
}

function updatePOStatus($conn, $input) {
    if (!isset($input['id']) || !isset($input['status'])) {
        jsonResponse(false, 'PO ID and status are required');
    }

    $id = (int)$input['id'];
    $status = sanitize($input['status']);
    $allowedStatuses = ['Draft', 'Printed', 'Signed', 'Invoiced', 'Completed'];

    if (!in_array($status, $allowedStatuses)) {
        jsonResponse(false, 'Invalid status');
    }

    $stmt = $conn->prepare("SELECT status FROM purchase_orders WHERE id = ? LIMIT 1");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        jsonResponse(false, 'Purchase Order not found', null, 404);
    }

    $oldStatus = $result->fetch_assoc()['status'];

    if ($oldStatus === $status) {
        jsonResponse(true, 'Status tidak berubah', [
            'id' => $id,
            'status' => $status
        ]);
    }

    $updateStmt = $conn->prepare("UPDATE purchase_orders SET status = ? WHERE id = ?");
    $updateStmt->bind_param('si', $status, $id);

    if (!$updateStmt->execute()) {
        jsonResponse(false, 'Failed to update status: ' . $conn->error);
    }

    logPoActivity($conn, $id, 'status_changed', 'Status PO berubah: ' . $oldStatus . ' → ' . $status);

    jsonResponse(true, 'PO status updated successfully', [
        'id' => $id,
        'status' => $status,
        'previous_status' => $oldStatus
    ]);
}

function deletePO($conn) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['id'])) {
        jsonResponse(false, 'PO ID is required');
    }
    
    $id = (int)$input['id'];
    
    $stmt = $conn->prepare("DELETE FROM purchase_orders WHERE id = ?");
    $stmt->bind_param('i', $id);
    
    if ($stmt->execute()) {
        jsonResponse(true, 'Purchase Order deleted successfully');
    } else {
        jsonResponse(false, 'Failed to delete PO: ' . $conn->error);
    }
}
