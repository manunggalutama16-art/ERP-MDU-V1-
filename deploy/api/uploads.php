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
    $type = isset($_POST['type']) ? sanitize($_POST['type']) : (isset($_GET['type']) ? sanitize($_GET['type']) : '');

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
        if (!isset($_POST['po_id'])) {
            jsonResponse(false, 'PO ID is required');
        }
        $po_id = (int)$_POST['po_id'];
        $file_size = $_FILES['file']['size'];
        $uploaded_by = $_SESSION['user_id'];

        pg_query_params($conn,
            "INSERT INTO po_attachments (po_id, type, file_name, file_path, file_size, uploaded_by) VALUES ($1, $2, $3, $4, $5, $6)",
            [$po_id, $attachmentType, $result['file_name'], $result['file_path'], $file_size, $uploaded_by]
        );

        logPoActivity($conn, $po_id, 'attachment_uploaded', 'File dilampirkan: ' . $result['file_name']);
    } else if ($type === 'npwp') {
        $vendor_id = isset($_POST['vendor_id']) ? (int)$_POST['vendor_id'] : 0;
        if ($vendor_id > 0) {
            pg_query_params($conn,
                "UPDATE vendors SET npwp_file = $1 WHERE id = $2",
                [$result['file_path'], $vendor_id]
            );
        }
    } else if ($type === 'signatures') {
        pg_query_params($conn,
            "INSERT INTO system_settings (setting_key, setting_value) VALUES ('signature_file', $1) 
             ON CONFLICT (setting_key) DO UPDATE SET setting_value = EXCLUDED.setting_value",
            [$result['file_path']]
        );
    } else if ($type === 'logo') {
        pg_query_params($conn,
            "INSERT INTO system_settings (setting_key, setting_value) VALUES ('logo_file', $1) 
             ON CONFLICT (setting_key) DO UPDATE SET setting_value = EXCLUDED.setting_value",
            [$result['file_path']]
        );
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
