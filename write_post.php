<?php
// session_set_cookie_params([
//     'httponly' => true, 
//     'samesite' => 'Lax'
// ]);
session_start();

// CSRF Token 설정
// if (!isset($_SESSION['csrf_token'])) {
//     $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
// }

require_once 'config/db.php';
require_once 'queries.php';

// 로그인 여부 확인
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// 게시판 목록 가져오기
$query = "SELECT id, name FROM boards ORDER BY name ASC";
$result = $pdo->query($query);
$boards = $result->fetchAll();
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>글쓰기</title>
    <link rel="stylesheet" href="styles/base.css">
    <link rel="stylesheet" href="styles/write.css">
</head>
<body>
    <?php require_once 'header.php' ?>

    <div id="create-post-content">
        <h1>Create Post</h1>
    
        <form method="post" action="write_post_check.php" enctype="multipart/form-data">
        <?php echo '<input type="hidden" name="_csrf" value="' . $_SESSION['csrf_token'] . '">'; ?>
            <div id="create-post-select-title">
                <label for="board_id">SELECT BOARD</label>
                <select id="board_id" name="board_id" required>
                    <?php foreach ($boards as $board): ?>
                        <option value="<?php echo $board['id']; ?>"><?php echo $board['name']; ?></option>
                    <?php endforeach; ?>
                </select><br>
    
                <label for="title">TITLE</label>
                <input type="text" id="title" name="title" required><br>
            </div>

            <label for="content">CONTENT</label>
            <textarea id="content" name="content" rows="10" required></textarea><br>
            
            <div id="upload-file">
                <label for="uploaded_file">UPLOAD FILE</label>
                <input type="file" id="uploaded_file" name="uploaded_file"><br>
            </div>
            
            <button type="submit">CREATE</button>
        </form>
    </div>
</body>
</html>
