<?php
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['action'])) {
            jsonResponse(false, 'Action required');
        }
        
        $action = $input['action'];
        
        if ($action === 'login') {
            login($input);
        } elseif ($action === 'logout') {
            logout();
        } else {
            jsonResponse(false, 'Invalid action');
        }
        break;
        
    case 'GET':
        if (isset($_GET['action']) && $_GET['action'] === 'check') {
            checkAuth();
        } else {
            jsonResponse(false, 'Invalid request');
        }
        break;
        
    default:
        jsonResponse(false, 'Method not allowed', null, 405);
}

function login($input) {
    $conn = getConnection();
    
    $email = isset($input['email']) ? sanitize($input['email']) : '';
    $password = isset($input['password']) ? $input['password'] : '';
    
    if (empty($email) || empty($password)) {
        jsonResponse(false, 'Email and password are required');
    }
    
    // Rate limiting
    if (!checkRateLimit($email)) {
        jsonResponse(false, 'Too many login attempts. Please try again later.', null, 429);
    }
    
    $result = pg_query_params($conn,
        "SELECT id, name, email, password, role FROM users WHERE email = $1 LIMIT 1",
        [$email]
    );
    
    if (!$result || pg_num_rows($result) === 0) {
        jsonResponse(false, 'Invalid email or password', null, 401);
    }
    
    $user = pg_fetch_assoc($result);
    
    if (password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        
        // Reset rate limit on successful login
        unset($_SESSION['rate_limit_' . $email]);
        
        unset($user['password']);
        
        jsonResponse(true, 'Login successful', [
            'user' => $user,
            'token' => bin2hex(random_bytes(32))
        ]);
    } else {
        jsonResponse(false, 'Invalid email or password', null, 401);
    }
}

function logout() {
    session_destroy();
    jsonResponse(true, 'Logout successful');
}

function checkAuth() {
    if (isset($_SESSION['user_id'])) {
        jsonResponse(true, 'Authenticated', [
            'user_id' => $_SESSION['user_id'],
            'user_name' => $_SESSION['user_name'],
            'user_email' => $_SESSION['user_email'],
            'user_role' => $_SESSION['user_role']
        ]);
    } else {
        jsonResponse(false, 'Not authenticated', null, 401);
    }
}
