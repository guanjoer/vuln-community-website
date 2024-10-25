<?php
// 사용자 정보 가져오기
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $stmt = $pdo->prepare("SELECT username, profile_image FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
}

// 게시판 목록 가져오기
$stmt = $pdo->query("SELECT id, name FROM boards ORDER BY name ASC");
$all_boards = $stmt->fetchAll();

?>