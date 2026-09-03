<?php
// ============================================
// ERP Procurement MDU - Configuration
// Updated for Supabase PostgreSQL
// ============================================

// Database Configuration - Supabase PostgreSQL
// Get these from: https://supabase.com/dashboard/project/_/settings/database
define('DB_HOST', 'db.supabase.co');  // Your Supabase project reference + .supabase.co
define('DB_PORT', '5432');
define('DB_USER', 'postgres.YOUR_PROJECT_REF');  // e.g., postgres.abcdefghij
define('DB_PASS', 'YOUR_SUPABASE_PASSWORD');     // Your database password
define('DB_NAME', 'postgres');
define('DB_SSLMODE', 'require');

// Application Configuration
define('APP_URL', 'https://www.procurement.mdutama.com');
define('APP_NAME', 'Nexus Procurement');

// Upload Configuration
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('UPLOAD_URL', APP_URL . '/uploads/');
define('MAX_UPLOAD_SIZE', 10 * 1024 * 1024); // 10MB

// Session Configuration
session_set_cookie_params([
    'lifetime' => 86400,
    'path' => '/',
    'domain' => parse_url(APP_URL, PHP_URL_HOST),
    'secure' => true,  // HTTPS enforced
    'httponly' => true,
    'samesite' => 'Lax'
]);
session_start();

// CORS Headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: ' . APP_URL);
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Error Reporting (Set ke 0 di production)
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');
error_reporting(E_ALL);

// ============================================
// Database Connection (PostgreSQL via pg_connect)
// ============================================
function getConnection() {
    try {
        // Check if PostgreSQL extension is available
        if (!function_exists('pg_connect')) {
            throw new Exception('PostgreSQL extension not available. Please enable php_pgsql in php.ini.');
        }
        
        $conn_string = sprintf(
            "host=%s port=%s dbname=%s user=%s password=%s sslmode=%s",
            DB_HOST,
            DB_PORT,
            DB_NAME,
            DB_USER,
            DB_PASS,
            DB_SSLMODE
        );
        
        $conn = @pg_connect($conn_string);
        
        if (!$conn) {
            throw new Exception('Koneksi database gagal: Could not connect to PostgreSQL');
        }
        
        // Set client encoding
        pg_set_client_encoding($conn, 'UTF8');
        
        return $conn;
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false, 
            'message' => 'Database connection error: ' . $e->getMessage()
        ]);
        exit();
    }
}

// ============================================
// PostgreSQL Query Helpers
// ============================================

/**
 * Execute a query with parameters (PostgreSQL style: $1, $2, etc.)
 */
function pg_query_params_safe($conn, $query, $params = []) {
    if (empty($params)) {
        return pg_query($conn, $query);
    }
    return pg_query_params($conn, $query, $params);
}

/**
 * Fetch all rows as associative array
 */
function pg_fetch_all_assoc($result) {
    $rows = [];
    while ($row = pg_fetch_assoc($result)) {
        $rows[] = $row;
    }
    return $rows;
}

/**
 * Get number of rows
 */
function pg_num_rows_safe($result) {
    return pg_num_rows($result);
}

/**
 * Get inserted ID (PostgreSQL uses RETURNING or pg_last_oid)
 */
function pg_last_insert_id($conn, $result = null) {
    if ($result) {
        // Try to get ID from RETURNING clause
        $row = pg_fetch_assoc($result);
        if ($row && isset($row['id'])) {
            return (int)$row['id'];
        }
    }
    // Fallback: use pg_last_oid (deprecated but works)
    $oid = pg_last_oid($conn);
    if ($oid) {
        // Convert OID to actual ID using a sequence query
        return (int)$oid;
    }
    return 0;
}

// JSON Response Helper
function jsonResponse($success, $message, $data = null, $code = 200) {
    http_response_code($code);
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit();
}

// Input Sanitization
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

// Authentication Check
function requireAuth() {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
        jsonResponse(false, 'Unauthorized access', null, 401);
    }
}

// Admin Check
function requireAdmin() {
    requireAuth();
    if ($_SESSION['user_role'] !== 'admin') {
        jsonResponse(false, 'Admin access required', null, 403);
    }
}

// Generate PO Number (PostgreSQL compatible)
function generatePONumber($conn) {
    $year = date('Y');
    $month = date('m');
    $prefix = "PO-{$year}{$month}-";
    
    $result = pg_query_params($conn, 
        "SELECT po_number FROM purchase_orders WHERE po_number LIKE $1 ORDER BY id DESC LIMIT 1",
        [$prefix . '%']
    );
    
    if ($result && pg_num_rows($result) > 0) {
        $row = pg_fetch_assoc($result);
        $lastNumber = (int)str_replace($prefix, '', $row['po_number']);
        $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
    } else {
        $newNumber = '001';
    }
    
    return $prefix . $newNumber;
}

// Handle File Upload
function uploadFile($file, $type) {
    $allowedTypes = [
        'npwp' => ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'],
        'signatures' => ['image/png', 'image/jpg', 'image/jpeg', 'application/pdf'],
        'quotations' => ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg'],
        'invoices' => ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg'],
        'supporting' => ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg', 'application/zip'],
        'logo' => ['image/jpeg', 'image/png', 'image/jpg']
    ];
    
    if (!isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'File upload error'];
    }
    
    if ($file['size'] > MAX_UPLOAD_SIZE) {
        return ['success' => false, 'message' => 'File size exceeds 10MB limit'];
    }
    
    $fileType = $file['type'];
    if (!in_array($fileType, $allowedTypes[$type])) {
        return ['success' => false, 'message' => 'Invalid file type'];
    }
    
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $fileName = uniqid() . '.' . $extension;
    $uploadDir = UPLOAD_PATH . $type . '/';
    
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $destination = $uploadDir . $fileName;
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return [
            'success' => true,
            'file_name' => $fileName,
            'file_path' => UPLOAD_URL . $type . '/' . $fileName
        ];
    }
    
    return ['success' => false, 'message' => 'Failed to move uploaded file'];
}

// Format Currency (Indonesian)
function formatCurrency($value) {
    if (!$value) return 'Rp 0';
    return 'Rp ' . number_format($value, 0, ',', '.');
}

// ============================================
// PO Activity Log helpers
// ============================================
function ensurePoActivityTable($conn) {
    pg_query($conn, "CREATE TABLE IF NOT EXISTS po_activity_log (
        id SERIAL PRIMARY KEY,
        po_id INTEGER NOT NULL REFERENCES purchase_orders(id) ON DELETE CASCADE,
        action VARCHAR(50) NOT NULL,
        description VARCHAR(255) DEFAULT NULL,
        created_by INTEGER REFERENCES users(id) ON DELETE SET NULL,
        created_at TIMESTAMPTZ DEFAULT NOW()
    )");
}

function logPoActivity($conn, $poId, $action, $description = '') {
    ensurePoActivityTable($conn);
    $userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
    $result = pg_query_params($conn,
        "INSERT INTO po_activity_log (po_id, action, description, created_by) VALUES ($1, $2, $3, $4) RETURNING id",
        [$poId, $action, $description, $userId]
    );
    return $result !== false;
}

function getPoActivity($conn, $poId) {
    ensurePoActivityTable($conn);
    $result = pg_query_params($conn,
        "SELECT a.*, u.name as user_name FROM po_activity_log a LEFT JOIN users u ON a.created_by = u.id WHERE a.po_id = $1 ORDER BY a.created_at DESC, a.id DESC",
        [$poId]
    );
    return pg_fetch_all_assoc($result);
}

// User initials for avatar placeholder
function userInitials($name) {
    $name = trim((string)$name);
    if ($name === '') return '?';
    $words = preg_split('/\s+/', $name);
    $initials = '';
    foreach ($words as $word) {
        if ($word === '') continue;
        $initials .= mb_strtoupper(mb_substr($word, 0, 1, 'UTF-8'), 'UTF-8');
        if (mb_strlen($initials) >= 2) break;
    }
    return $initials !== '' ? $initials : '?';
}

// Format Date (Indonesian)
function formatDate($dateString) {
    if (!$dateString) return '-';
    $date = new DateTime($dateString);
    return $date->format('d M Y');
}

// ============================================
// CSRF Protection
// ============================================
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function csrfField() {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(generateCSRFToken()) . '">';
}

// Rate limiting for login attempts
function checkRateLimit($identifier, $maxAttempts = 5, $windowSeconds = 300) {
    $key = 'rate_limit_' . $identifier;
    $now = time();
    
    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = ['count' => 0, 'first_attempt' => $now];
    }
    
    $data = &$_SESSION[$key];
    
    // Reset if window expired
    if ($now - $data['first_attempt'] > $windowSeconds) {
        $data = ['count' => 0, 'first_attempt' => $now];
        return true;
    }
    
    // Check if too many attempts
    if ($data['count'] >= $maxAttempts) {
        return false;
    }
    
    $data['count']++;
    return true;
}
