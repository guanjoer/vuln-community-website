<?php
session_set_cookie_params([
    'httponly' => true, 
    'samesite' => 'Lax'
]);
session_start();

require_once './error_handling.php';

require_once '../config/db.php';

// 게시판 삭제
if (isset($_GET['id'])) {
    $user_id = $_GET['id'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if ($user) {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$user_id]);

        echo "<script>alert('사용자가 성공적으로 삭제되었습니다.'); window.location.href='users.php';</script>";
    } else {
        echo "<script>alert('존재하지 않는 사용자입니다.'); history.back();</script>";
        exit();
    }
} else {
    header("Location: users.php");
    exit();
}
?>
