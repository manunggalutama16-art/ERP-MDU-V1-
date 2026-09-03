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
    $result = $conn->query("SELECT * FROM system_settings");
    $settings = [];
    
    while ($row = $result->fetch_assoc()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
    
    jsonResponse(true, 'Settings retrieved', $settings);
}

function updateSettings($conn) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!is_array($input)) {
        jsonResponse(false, 'Invalid input data');
    }
    
    $conn->begin_transaction();
    
    try {
        foreach ($input as $key => $value) {
            $stmt = $conn->prepare("INSERT INTO system_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
            $stmt->bind_param('sss', $key, $value, $value);
            
            if (!$stmt->execute()) {
                throw new Exception('Failed to update setting: ' . $key);
            }
        }
        
        $conn->commit();
        jsonResponse(true, 'Settings updated successfully');
        
    } catch (Exception $e) {
        $conn->rollback();
        jsonResponse(false, 'Failed to update settings: ' . $e->getMessage());
    }
}
