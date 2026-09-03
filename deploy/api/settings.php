<?php
require_once 'config.php';
requireAuth();
requireAdmin();

$method = $_SERVER['REQUEST_METHOD'];
$conn = getConnection();

switch ($method) {
    case 'GET':
        getSettings($conn);
        break;
        
    case 'PUT':
        updateSettings($conn);
        break;
        
    default:
        jsonResponse(false, 'Method not allowed', null, 405);
}

function getSettings($conn) {
    $result = pg_query($conn, "SELECT * FROM system_settings");
    $settings = [];
    
    while ($row = pg_fetch_assoc($result)) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
    
    jsonResponse(true, 'Settings retrieved', $settings);
}

function updateSettings($conn) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!is_array($input)) {
        jsonResponse(false, 'Invalid input data');
    }
    
    pg_query($conn, 'BEGIN');
    
    try {
        foreach ($input as $key => $value) {
            pg_query_params($conn,
                "INSERT INTO system_settings (setting_key, setting_value) VALUES ($1, $2) 
                 ON CONFLICT (setting_key) DO UPDATE SET setting_value = EXCLUDED.setting_value",
                [$key, $value]
            );
        }
        
        pg_query($conn, 'COMMIT');
        jsonResponse(true, 'Settings updated successfully');
        
    } catch (Exception $e) {
        pg_query($conn, 'ROLLBACK');
        jsonResponse(false, 'Failed to update settings: ' . $e->getMessage());
    }
}
