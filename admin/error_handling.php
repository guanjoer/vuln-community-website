<?php
// 관리자 여부 확인
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("HTTP/1.0 404 Not Found");
    include('../errors/admin/404.php');
    exit();
}
?>