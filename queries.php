<?php
// 사용자 정보 가져오기
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $result = $pdo->query("SELECT username, profile_image FROM users WHERE id = '$user_id'");
    $user = $result->fetch();
}

// 게시판 목록 가져오기
$query = "SELECT id, name FROM boards ORDER BY name ASC";
$result = $pdo->query($query);
$all_boards = $result->fetchAll();

?>