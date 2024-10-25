<?php
session_set_cookie_params([
    'httponly' => true, 
    'samesite' => 'Lax' // Cross-site 요청에 대한 보호(Lax, Strict, None)
]);
session_start();

require_once 'config/db.php';

// 댓글 삭제 처리
if (isset($_GET['id']) && isset($_GET['post_id'])) {
    $comment_id = $_GET['id'];
    $post_id = $_GET['post_id'];

    // 댓글 정보 가져오기
    $stmt = $pdo->prepare("SELECT * FROM comments WHERE id = ?");
    $stmt->execute([$comment_id]);
    $comment = $stmt->fetch();

    if (!$comment) {
        echo "<script>alert('존재하지 않는 댓글입니다.'); window.location.href='post.php?id=$post_id';</script>";
        exit();
    }

    // 작성자 또는 관리자 여부 확인
    if ($comment['user_id'] != $_SESSION['user_id'] && $_SESSION['role'] !== 'admin') {
        echo "<script>alert('댓글을 삭제할 권한이 없습니다.'); window.location.href='post.php?id=$post_id';</script>";
        exit();
    }

    // 댓글 삭제
    $stmt = $pdo->prepare("DELETE FROM comments WHERE id = ?");
    $stmt->execute([$comment_id]);

    echo "<script>alert('댓글이 성공적으로 삭제되었습니다.'); window.location.href='post.php?id=$post_id';</script>";
    exit();
} else {
    header("Location: index.php");
    exit();
}
?>
