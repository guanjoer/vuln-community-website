<?php
session_set_cookie_params([
    'httponly' => true, 
    'samesite' => 'Lax'
]);
session_start();

require_once './error_handling.php';

require_once '../config/db.php';

// 게시판 삭제 처리
if (isset($_GET['id'])) {
    $board_id = $_GET['id'];

    // 게시판 존재 여부 확인
    $stmt = $pdo->prepare("SELECT * FROM boards WHERE id = ?");
    $stmt->execute([$board_id]);
    $board = $stmt->fetch();

    if ($board) {
        // 게시판 삭제
        $stmt = $pdo->prepare("DELETE FROM boards WHERE id = ?");
        $stmt->execute([$board_id]);

        // 추가: 게시판에 속한 게시글도 삭제하려면, 아래 코드 주석을 해제
        // $stmt = $pdo->prepare("DELETE FROM posts WHERE board_id = ?");
        // $stmt->execute([$board_id]);

        echo "<script>alert('게시판이 성공적으로 삭제되었습니다.'); window.location.href='boards.php';</script>";
    } else {
        echo "<script>alert('존재하지 않는 게시판입니다.'); history.back();</script>";
        exit();
    }
} else {
    header("Location: boards.php");
    exit();
}
?>
