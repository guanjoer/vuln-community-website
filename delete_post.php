<?php
session_set_cookie_params([
    'httponly' => true, 
    'samesite' => 'Lax' // Cross-site 요청에 대한 보호(Lax, Strict, None)
]);
session_start();

// 로그인 여부 확인
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'config/db.php';

// 게시글 정보 가져오기
if (isset($_GET['id'])) {
    $post_id = $_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ?");
    $stmt->execute([$post_id]);
    $post = $stmt->fetch();

    if (!$post) {
        echo "<script>alert('존재하지 않는 게시글입니다.'); history.back();</script>";
        exit();
    }

    // 작성자 또는 관리자 여부 확인
    if ($post['user_id'] != $_SESSION['user_id'] && $_SESSION['role'] !== 'admin') {
        echo "<script>alert('게시글을 삭제할 권한이 없습니다.'); history.back();</script>";
        exit();
    }

    // 업로드된 파일 정보 가져오기
    $stmt = $pdo->prepare("SELECT * FROM uploads WHERE post_id = ?");
    $stmt->execute([$post_id]);
    $files = $stmt->fetchAll();

    // 파일 삭제
    foreach ($files as $file) {
        if (file_exists($file['file_path'])) {
            unlink($file['file_path']); // 서버에서 파일 삭제
        }
    }

    // uploads 테이블에서 파일 정보 삭제
    $stmt = $pdo->prepare("DELETE FROM uploads WHERE post_id = ?");
    $stmt->execute([$post_id]);

    // 게시글 삭제 처리
    $stmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");
    $stmt->execute([$post_id]);

    echo "<script>alert('게시글이 성공적으로 삭제되었습니다.')";

    if (isset($_SESSION['user_id']) && $_SESSION['role'] == 'admin') {
        header("Location: admin/posts.php");
        exit();
    } else {
        header("Location: index.php");
        exit();
    }
    exit();
} else {
    header("Location: index.php");
    exit();
}
?>
