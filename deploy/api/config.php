<?php
// ============================================
// ERP Procurement MDU - Configuration
// SETUP INI SEBELUM DEPLOY!
// ============================================

// Database Configuration
// Ganti dengan kredensial database MySQL Anda dari cPanel Niagahoster
define('DB_HOST', 'localhost');
define('DB_USER', 'USERNAME_DATABASE_ANDA');     // Contoh: mdutama_user
define('DB_PASS', 'PASSWORD_DATABASE_ANDA');     // Contoh: password123
define('DB_NAME', 'NAMA_DATABASE_ANDA');         // Contoh: mdutama_procurement

// Application Configuration
// Jika deploy di subfolder, tambahkan nama foldernya
// Contoh: http://procurement.mdutama.com/erp/
define('APP_URL', 'http://procurement.mdutama.com');
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
    'secure' => isset($_SERVER['HTTPS']),
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

// Database Connection
function getConnection() {
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($conn->connect_error) {
            throw new Exception('Koneksi database gagal: ' . $conn->connect_error);
        }
        
        $conn->set_charset('utf8mb4');
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

// Generate PO Number
function generatePONumber($conn) {
    $year = date('Y');
    $month = date('m');
    $prefix = "PO-{$year}{$month}-";
    
    $result = $conn->query("SELECT po_number FROM purchase_orders WHERE po_number LIKE '{$prefix}%' ORDER BY id DESC LIMIT 1");
    if ($result && $row = $result->fetch_assoc()) {
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
// The table is created on demand so existing deployments self-heal
// (no manual migration needed).
// ============================================
function ensurePoActivityTable($conn) {
    $conn->query("CREATE TABLE IF NOT EXISTS po_activity_log (
        id INT AUTO_INCREMENT PRIMARY KEY,
        po_id INT NOT NULL,
        action VARCHAR(50) NOT NULL,
        description VARCHAR(255) DEFAULT NULL,
        created_by INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (po_id) REFERENCES purchase_orders(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

function logPoActivity($conn, $poId, $action, $description = '') {
    ensurePoActivityTable($conn);
    $stmt = $conn->prepare("INSERT INTO po_activity_log (po_id, action, description, created_by) VALUES (?, ?, ?, ?)");
    $userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
    $stmt->bind_param('issi', $poId, $action, $description, $userId);
    return $stmt->execute();
}

function getPoActivity($conn, $poId) {
    ensurePoActivityTable($conn);
    $stmt = $conn->prepare("SELECT a.*, u.name as user_name FROM po_activity_log a LEFT JOIN users u ON a.created_by = u.id WHERE a.po_id = ? ORDER BY a.created_at DESC, a.id DESC");
    $stmt->bind_param('i', $poId);
    $stmt->execute();
    $rows = [];
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    return $rows;
}

// User initials for avatar placeholder (no external image dependency)
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
