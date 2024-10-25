<?php
session_set_cookie_params([
    'httponly' => true, 
    'samesite' => 'Lax' 
]);
session_start();

// 로그아웃 처리
if (isset($_GET['logout'])) {
    session_destroy();
    
    $redirect_url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null;

    if ($redirect_url && strpos($redirect_url, '/admin') !== false) {
        header("Location: ../index.php"); 
        exit();
    } else {
        if ($redirect_url) {
            header("Location: $redirect_url");
        } else {
            header("Location: ../index.php");
        }
        exit();
    }
}
?>
