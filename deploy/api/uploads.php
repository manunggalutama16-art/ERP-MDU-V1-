<?php
require_once 'config.php';
requireAuth();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
        uploadFileAPI();
        break;
        
    case 'DELETE':
        deleteFileAPI();
        break;
        
    default:
        jsonResponse(false, 'Method not allowed', null, 405);
}

function uploadFileAPI() {
    // Type may arrive as a multipart field (po_detail.php) or as a query param (settings.php)
    $type = isset($_POST['type']) ? sanitize($_POST['type']) : (isset($_GET['type']) ? sanitize($_GET['type']) : '');

    // Map each upload type to its storage directory and, for PO attachments,
    // the correct po_attachments.type ENUM value ('invoice_supplier', 'quotation',
    // 'wet_signature', 'supporting'). 'npwp', 'signatures' and 'logo' are special
    // types that update a record instead of creating a PO attachment.
    $typeMap = [
        'invoice_supplier' => ['dir' => 'invoices',   'attachment' => 'invoice_supplier'],
        'invoices'         => ['dir' => 'invoices',   'attachment' => 'invoice_supplier'],
        'quotation'        => ['dir' => 'quotations', 'attachment' => 'quotation'],
        'quotations'       => ['dir' => 'quotations', 'attachment' => 'quotation'],
        'wet_signature'    => ['dir' => 'signatures', 'attachment' => 'wet_signature'],
        'supporting'       => ['dir' => 'supporting', 'attachment' => 'supporting'],
        'npwp'             => ['dir' => 'npwp',       'attachment' => null],
        'signatures'       => ['dir' => 'signatures', 'attachment' => null],
        'logo'             => ['dir' => 'logo',       'attachment' => null]
    ];

    if (!isset($typeMap[$type])) {
        jsonResponse(false, 'Invalid upload type');
    }

    if (!isset($_FILES['file'])) {
        jsonResponse(false, 'No file uploaded');
    }

    $dir = $typeMap[$type]['dir'];
    $attachmentType = $typeMap[$type]['attachment'];

    $result = uploadFile($_FILES['file'], $dir);

    if (!$result['success']) {
        jsonResponse(false, $result['message']);
    }

    $conn = getConnection();

    if ($attachmentType) {
        // PO attachment - po_id is required
        if (!isset($_POST['po_id'])) {
            jsonResponse(false, 'PO ID is required');
        }
        $po_id = (int)$_POST['po_id'];

        $stmt = $conn->prepare("INSERT INTO po_attachments (po_id, type, file_name, file_path, file_size, uploaded_by) VALUES (?, ?, ?, ?, ?, ?)");
        $file_size = $_FILES['file']['size'];
        $uploaded_by = $_SESSION['user_id'];
        $stmt->bind_param('isssii', $po_id, $attachmentType, $result['file_name'], $result['file_path'], $file_size, $uploaded_by);
        $stmt->execute();

        logPoActivity($conn, $po_id, 'attachment_uploaded', 'File dilampirkan: ' . $result['file_name']);
    } else if ($type === 'npwp') {
        // Update vendor NPWP file
        $vendor_id = isset($_POST['vendor_id']) ? (int)$_POST['vendor_id'] : 0;
        if ($vendor_id > 0) {
            $stmt = $conn->prepare("UPDATE vendors SET npwp_file = ? WHERE id = ?");
            $stmt->bind_param('si', $result['file_path'], $vendor_id);
            $stmt->execute();
        }
    } else if ($type === 'signatures') {
        // Update digital signature setting (row may not exist yet)
        $stmt = $conn->prepare("INSERT INTO system_settings (setting_key, setting_value) VALUES ('signature_file', ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        $stmt->bind_param('s', $result['file_path']);
        $stmt->execute();
    } else if ($type === 'logo') {
        // Update company logo setting (row may not exist yet)
        $stmt = $conn->prepare("INSERT INTO system_settings (setting_key, setting_value) VALUES ('logo_file', ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        $stmt->bind_param('s', $result['file_path']);
        $stmt->execute();
    }

    jsonResponse(true, 'File uploaded successfully', [
        'file_name' => $result['file_name'],
        'file_path' => $result['file_path']
    ]);
}

function deleteFileAPI() {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['file_path'])) {
        jsonResponse(false, 'File path is required');
    }
    
    $file_path = $input['file_path'];
    $full_path = str_replace(APP_URL, $_SERVER['DOCUMENT_ROOT'], $file_path);
    
    if (file_exists($full_path)) {
        if (unlink($full_path)) {
            jsonResponse(true, 'File deleted successfully');
        } else {
            jsonResponse(false, 'Failed to delete file');
        }
    } else {
        jsonResponse(false, 'File not found');
    }
}
