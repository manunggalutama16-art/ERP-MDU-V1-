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
    $paramIndex = 1;
    
    if (!empty($search)) {
        $where = "WHERE (po.po_number ILIKE ${$paramIndex} OR v.name ILIKE ${$paramIndex} OR p.name ILIKE ${$paramIndex})";
        $params[] = "%{$search}%";
        $paramIndex++;
    }
    
    if (!empty($status)) {
        $where .= empty($where) ? "WHERE po.status = ${$paramIndex}" : " AND po.status = ${$paramIndex}";
        $params[] = $status;
        $paramIndex++;
    }
    
    // Get total count
    $countQuery = "SELECT COUNT(*) as total FROM purchase_orders po 
                   LEFT JOIN vendors v ON po.vendor_id = v.id 
                   LEFT JOIN projects p ON po.project_id = p.id {$where}";
    $result = pg_query_params($conn, $countQuery, $params);
    $total = pg_fetch_assoc($result)['total'];
    
    // Get data
    $query = "SELECT po.*, v.name as vendor_name, v.contact_person, v.phone, v.email as vendor_email,
                     p.name as project_name, p.code as project_code, p.location as project_location,
                     u.name as created_by_name
              FROM purchase_orders po 
              LEFT JOIN vendors v ON po.vendor_id = v.id 
              LEFT JOIN projects p ON po.project_id = p.id 
              LEFT JOIN users u ON po.created_by = u.id
              {$where} 
              ORDER BY po.id DESC LIMIT ${$paramIndex} OFFSET ${$paramIndex}";
    
    $params[] = $limit;
    $params[] = $offset;
    
    $result = pg_query_params($conn, $query, $params);
    $pos = pg_fetch_all_assoc($result);
    
    jsonResponse(true, 'Purchase Orders retrieved', [
        'pos' => $pos,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => (int)$total,
            'total_pages' => ceil($total / $limit)
        ]
    ]);
}

function getPO($conn, $id) {
    $result = pg_query_params($conn,
        "SELECT po.*, v.name as vendor_name, v.address as vendor_address, v.npwp as vendor_npwp, v.contact_person, v.phone, v.email as vendor_email,
                                   p.name as project_name, p.code as project_code, p.location as project_location, p.client as project_client,
                                   u.name as created_by_name
                            FROM purchase_orders po 
                            LEFT JOIN vendors v ON po.vendor_id = v.id 
                            LEFT JOIN projects p ON po.project_id = p.id 
                            LEFT JOIN users u ON po.created_by = u.id
                            WHERE po.id = $1 LIMIT 1",
        [$id]
    );
    
    if (pg_num_rows($result) === 0) {
        jsonResponse(false, 'Purchase Order not found', null, 404);
    }
    
    $po = pg_fetch_assoc($result);
    
    // Get items
    $itemsResult = pg_query_params($conn, "SELECT * FROM po_items WHERE po_id = $1 ORDER BY sort_order ASC, id ASC", [$id]);
    $po['items'] = pg_fetch_all_assoc($itemsResult);
    
    // Get attachments
    $attResult = pg_query_params($conn, "SELECT * FROM po_attachments WHERE po_id = $1", [$id]);
    $po['attachments'] = pg_fetch_all_assoc($attResult);
    
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
    $quotation_attached = isset($input['quotation_attached']) ? ($input['quotation_attached'] ? true : false) : false;
    $approved = isset($input['approved']) ? ($input['approved'] ? true : false) : false;
    $notes = isset($input['notes']) ? sanitize($input['notes']) : '';
    $items = isset($input['items']) ? $input['items'] : [];
    $created_by = $_SESSION['user_id'];
    $ppn_type = isset($input['ppn_type']) ? sanitize($input['ppn_type']) : 'ppn';
    
    pg_query($conn, 'BEGIN');
    
    try {
        // Calculate totals
        $subtotal = 0;
        foreach ($items as $item) {
            $subtotal += ((float)$item['quantity'] * (float)$item['price']);
        }
        
        $ppn_percent = ($ppn_type === 'non') ? 0.00 : 11.00;
        $ppn_amount = $subtotal * ($ppn_percent / 100);
        $grand_total = $subtotal + $ppn_amount;
        
        $result = pg_query_params($conn,
            "INSERT INTO purchase_orders (po_number, vendor_id, project_id, top, delivery_location, status, quotation_attached, approved, subtotal, ppn_percent, ppn_amount, grand_total, notes, created_by) 
             VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12, $13, $14) RETURNING id",
            [$po_number, $vendor_id, $project_id, $top, $delivery_location, $status, $quotation_attached, $approved, $subtotal, $ppn_percent, $ppn_amount, $grand_total, $notes, $created_by]
        );
        
        if (!$result) {
            throw new Exception('Failed to create PO');
        }
        
        $po_id = pg_fetch_assoc($result)['id'];
        
        // Insert items
        $sort_order = 1;
        foreach ($items as $item) {
            $item_name = sanitize($item['item_name']);
            $quantity = (float)$item['quantity'];
            $unit = isset($item['unit']) ? sanitize($item['unit']) : 'Pcs';
            $price = (float)$item['price'];
            
            $itemResult = pg_query_params($conn,
                "INSERT INTO po_items (po_id, item_name, quantity, unit, price, sort_order) VALUES ($1, $2, $3, $4, $5, $6)",
                [$po_id, $item_name, $quantity, $unit, $price, $sort_order]
            );
            
            if (!$itemResult) {
                throw new Exception('Failed to create PO item');
            }
            
            $sort_order++;
        }
        
        pg_query($conn, 'COMMIT');
        
        logPoActivity($conn, $po_id, 'created', 'PO ' . $po_number . ' dibuat');
        
        jsonResponse(true, 'Purchase Order created successfully', [
            'id' => $po_id,
            'po_number' => $po_number
        ]);
        
    } catch (Exception $e) {
        pg_query($conn, 'ROLLBACK');
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
    $checkResult = pg_query_params($conn, "SELECT id FROM purchase_orders WHERE id = $1 LIMIT 1", [$id]);
    if (pg_num_rows($checkResult) === 0) {
        jsonResponse(false, 'Purchase Order not found', null, 404);
    }
    
    $vendor_id = isset($input['vendor_id']) ? (int)$input['vendor_id'] : null;
    $project_id = isset($input['project_id']) ? (int)$input['project_id'] : null;
    $top = isset($input['top']) ? sanitize($input['top']) : '';
    $delivery_location = isset($input['delivery_location']) ? sanitize($input['delivery_location']) : '';
    $status = isset($input['status']) ? sanitize($input['status']) : 'Draft';
    $quotation_attached = isset($input['quotation_attached']) ? ($input['quotation_attached'] ? true : false) : false;
    $approved = isset($input['approved']) ? ($input['approved'] ? true : false) : false;
    $notes = isset($input['notes']) ? sanitize($input['notes']) : '';
    $items = isset($input['items']) ? $input['items'] : [];
    $ppn_type = isset($input['ppn_type']) ? sanitize($input['ppn_type']) : 'ppn';
    
    pg_query($conn, 'BEGIN');
    
    try {
        // Calculate totals
        $subtotal = 0;
        foreach ($items as $item) {
            $subtotal += ((float)$item['quantity'] * (float)$item['price']);
        }
        
        $ppn_percent = ($ppn_type === 'non') ? 0.00 : 11.00;
        $ppn_amount = $subtotal * ($ppn_percent / 100);
        $grand_total = $subtotal + $ppn_amount;
        
        $result = pg_query_params($conn,
            "UPDATE purchase_orders SET vendor_id=$1, project_id=$2, top=$3, delivery_location=$4, status=$5, quotation_attached=$6, approved=$7, subtotal=$8, ppn_percent=$9, ppn_amount=$10, grand_total=$11, notes=$12 WHERE id=$13",
            [$vendor_id, $project_id, $top, $delivery_location, $status, $quotation_attached, $approved, $subtotal, $ppn_percent, $ppn_amount, $grand_total, $notes, $id]
        );
        
        if (!$result) {
            throw new Exception('Failed to update PO');
        }
        
        // Delete old items
        pg_query_params($conn, "DELETE FROM po_items WHERE po_id = $1", [$id]);
        
        // Insert new items
        $sort_order = 1;
        foreach ($items as $item) {
            $item_name = sanitize($item['item_name']);
            $quantity = (float)$item['quantity'];
            $unit = isset($item['unit']) ? sanitize($item['unit']) : 'Pcs';
            $price = (float)$item['price'];
            
            $itemResult = pg_query_params($conn,
                "INSERT INTO po_items (po_id, item_name, quantity, unit, price, sort_order) VALUES ($1, $2, $3, $4, $5, $6)",
                [$id, $item_name, $quantity, $unit, $price, $sort_order]
            );
            
            if (!$itemResult) {
                throw new Exception('Failed to update PO item');
            }
            
            $sort_order++;
        }
        
        pg_query($conn, 'COMMIT');
        
        logPoActivity($conn, $id, 'updated', 'Detail PO diperbarui');
        
        jsonResponse(true, 'Purchase Order updated successfully');
        
    } catch (Exception $e) {
        pg_query($conn, 'ROLLBACK');
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

    $result = pg_query_params($conn, "SELECT status FROM purchase_orders WHERE id = $1 LIMIT 1", [$id]);

    if (pg_num_rows($result) === 0) {
        jsonResponse(false, 'Purchase Order not found', null, 404);
    }

    $oldStatus = pg_fetch_assoc($result)['status'];

    if ($oldStatus === $status) {
        jsonResponse(true, 'Status tidak berubah', [
            'id' => $id,
            'status' => $status
        ]);
    }

    $updateResult = pg_query_params($conn, "UPDATE purchase_orders SET status = $1 WHERE id = $2", [$status, $id]);

    if (!$updateResult) {
        jsonResponse(false, 'Failed to update status');
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
    
    $result = pg_query_params($conn, "DELETE FROM purchase_orders WHERE id = $1", [$id]);
    
    if ($result) {
        jsonResponse(true, 'Purchase Order deleted successfully');
    } else {
        jsonResponse(false, 'Failed to delete PO');
    }
}
