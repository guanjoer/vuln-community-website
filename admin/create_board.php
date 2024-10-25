<?php
session_set_cookie_params([
    'httponly' => true, 
    'samesite' => 'Lax'
]);
session_start();

require_once './error_handling.php';

require_once '../config/db.php';

// 게시판 생성 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];

    // 게시판 중복 체크
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM boards WHERE name = ?");
    $stmt->execute([$name]);
    if ($stmt->fetchColumn() > 0) {
        echo "<script>alert('이미 존재하는 게시판 이름입니다. 다른 이름을 선택하세요.'); window.history.back();</script>";
        exit();
    } else {
        // 게시판 생성
        $stmt = $pdo->prepare("INSERT INTO boards (name, description) VALUES (?, ?)");
        $stmt->execute([$name, $description]);

        echo "<script>alert('게시판이 성공적으로 생성되었습니다.'); window.location.href='boards.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>게시판 생성</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=New+Amsterdam&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="../styles/base.css"> 
    <link rel="stylesheet" href="styles/write_board.css">

    <link rel="icon" href="../favicon/favicon.ico" type="image/x-icon">
</head>
<body>
    <?php require_once 'admin_header.php' ?>

    <div id="create-post-content">

    <h1>게시판 생성</h1>

    <form method="post" action="create_board.php">
        <label for="name">게시판 이름</label>
        <input type="text" id="name" name="name" required><br>

        <label for="description">게시판 설명</label>
        <textarea id="description" name="description" required></textarea><br>

        <button type="submit">생성하기</button>
    </form>

    <button class="back-btn" onclick="location.href='boards.php'">이전</button>
    </div>
</body>
</html>
