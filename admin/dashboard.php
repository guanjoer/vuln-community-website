<?php
session_set_cookie_params([
    'httponly' => true, 
    'samesite' => 'Lax' // Cross-site 요청에 대한 보호(Lax, Strict, None)
]);
session_start();

require_once './error_handling.php';

require_once '../config/db.php';

require_once '../queries.php';

// 유저 수
$stmt = $pdo->query("SELECT COUNT(*)  FROM users");
$user_count = $stmt->fetchColumn();

// 게시판 수
$stmt = $pdo->query("SELECT COUNT(*)  FROM boards");
$board_count = $stmt->fetchColumn();

// 게시글
$stmt = $pdo->query("SELECT COUNT(*)  FROM posts");
$post_count = $stmt->fetchColumn();
?>


<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>관리자 대시보드</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=New+Amsterdam&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="../styles/base.css">
    <link rel="stylesheet" href="styles/dashboard.css">

    <link rel="icon" href="../favicon/favicon.ico" type="image/x-icon">
</head>
<body>
    <?php require_once 'admin_header.php' ?>

    <div id="main-container">
        <?php require_once 'admin_sidebar.php'?>
        <section id="content">
        <h1>관리자 > 대시보드</h1>

        <div id="info">
            <div class="info-content">
                <h2 class="content-title">
                    <a href="users.php">사용자 수</a>
                </h2>
                <span class="content-count"><?= $user_count ?></span> </p>
            </div>
            <div class="info-content">
                <h2 class="content-title">
                    <a href="posts.php">게시글 수</a>
                </h2>
                <span class="content-count"><?= $post_count ?></span>
            </div>
            <div class="info-content">
                <h2 class="content-title">
                    <a href="boards.php">게시판 수</a>
                </h2>
                <span class="content-count"><?= $board_count ?></span>
            </div>
        </div>

        </section>
    </div>
</body>
</html>
