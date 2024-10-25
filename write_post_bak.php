<?php
session_set_cookie_params([
    'httponly' => true, 
    'samesite' => 'Lax'
]);
session_start();

// CSRF Token
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
$stmt = $pdo->query("SELECT id, name FROM boards ORDER BY name ASC");
$boards = $stmt->fetchAll();


// 게시글 작성 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if($_SESSION['csrf_token'] != $_POST['_csrf']) {
        echo "<script>alert('잘못된 접근입니다.'); history.back();</script>";
        exit();
    }

    $user_id = htmlspecialchars($_SESSION['user_id']);
    $board_id = htmlspecialchars($_POST['board_id']);
    $title = htmlspecialchars($_POST['title']);
    $content = htmlspecialchars($_POST['content']);

    // 파일 업로드 처리
    $upload_success = true;

    if (isset($_FILES['uploaded_file']) && $_FILES['uploaded_file']['error'] == 0) {
        // 화이트리스트 기반 파일 확장자, MIME type 검증
        $allowed_extensions = ['png', 'jpg', 'pdf', 'xlsx'];
        $allowed_mime_types = ['image/png', 'image/jpeg', 'application/pdf', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];

        // 파일 정보 추출
        $file_extension = pathinfo($_FILES['uploaded_file']['name'], PATHINFO_EXTENSION);
        $file_extension = strtolower($file_extension);
        $file_name_without_ext = pathinfo($_FILES['uploaded_file']['name'], PATHINFO_FILENAME);
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $_FILES['uploaded_file']['tmp_name']);

        if (in_array($file_extension, $allowed_extensions) && in_array($mime_type, $allowed_mime_types)) {
            $upload_dir = 'uploads/';
            $file_name =  $file_name_without_ext.'_'.uniqid() . '.' . $file_extension; // 파일 이름 난수화
            $file_path = $upload_dir . $file_name;

            if (!move_uploaded_file($_FILES['uploaded_file']['tmp_name'], $file_path)) {
                $upload_success = false;
                echo "<script>alert('파일 업로드 중 오류가 발생했습니다.'); history.back();</script>";
                exit();
            }
        } else {
            $upload_success = false;
            echo "<script>alert('허용되지 않은 파일 형식입니다.'); history.back();</script>";
            exit();
        }
    }

    // 파일 업로드가 성공한 경우에만 게시글 저장
    if ($upload_success) {
        $stmt = $pdo->prepare("INSERT INTO posts (user_id, board_id, title, content) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $board_id, $title, $content]);

        // 게시글 ID
        $post_id = $pdo->lastInsertId();

        // 파일 정보 데이터베이스에 저장 (파일 업로드가 성공했을 경우)
        if (isset($file_path)) {
            $stmt = $pdo->prepare("INSERT INTO uploads (post_id, file_name, file_path) VALUES (?, ?, ?)");
            $stmt->execute([$post_id, $_FILES['uploaded_file']['name'], $file_path]);
        }

        echo "<script>alert('게시글이 성공적으로 작성되었습니다.'); window.location.href='index.php';</script>";
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    } else {
        echo "<script>alert('게시글 작성이 취소되었습니다. 허용된 파일 형식을 사용해주세요.'); history.back();</script>";
        exit();
    }

    exit();
}
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>글쓰기</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=New+Amsterdam&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="styles/base.css">
    <link rel="stylesheet" href="styles/write.css">
    <link rel="icon" href="favicon/favicon.ico" type="image/x-icon">
</head>
<body>
    <?php require_once 'header.php' ?>

    <div id="create-post-content">
        <h1>Create Post</h1>
    
        <form method="post" action="write_post.php" enctype="multipart/form-data">
        <?php echo '<input type="hidden" name="_csrf" value="' . $_SESSION['csrf_token'] . '">'; ?>
            <div id="create-post-select-title">
            <label for="board_id">SELECT BOARD</label>
            <select id="board_id" name="board_id" required>
                <?php foreach ($boards as $board): ?>
                    <option value="<?php echo $board['id']; ?>"><?php echo htmlspecialchars($board['name']); ?></option>
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
