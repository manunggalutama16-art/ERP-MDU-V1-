<?php
session_start();
require_once 'api/config.php';

// If already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
} else {
    header('Location: login.php');
    exit();
}
