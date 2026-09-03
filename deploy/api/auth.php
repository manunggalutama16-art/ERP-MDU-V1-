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
    
    $stmt = $conn->prepare("SELECT id, name, email, password, role FROM users WHERE email = ? LIMIT 1");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        jsonResponse(false, 'Invalid email or password', null, 401);
    }
    
    $user = $result->fetch_assoc();
    
    if (password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        
        // Remove password from response
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
