<?php
/**
 * Authentication Handler
 * SMS3 Learning Management System
 */


require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize_input($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        header('Location: Log_in.php?error=' . urlencode('Please enter both username and password'));
        exit;
    }
    
    $auth = new Auth();
    $result = $auth->login($username, $password);
    
    if ($result['success']) {
        // Determine redirect URL based on user type
        switch ($result['user_type']) {
            case USER_TYPE_ADMIN:
                $redirect_url = '../Admin/Dashboard/dashboard.php';
                break;
            case USER_TYPE_TEACHER:
                $redirect_url = '../Teacher/Dashboard/dashboard.php';
                break;
            case USER_TYPE_STUDENT:
                $redirect_url = '../Students/Dashboard/dashboard.php';
                break;
            default:
                $redirect_url = '../index.php';
        }
        
        header('Location: ' . $redirect_url);
        exit;
    } else {
        header('Location: Log_in.php?error=' . urlencode($result['message']));
        exit;
    }
} else {
    header('Location: Log_in.php');
    exit;
}
?>
