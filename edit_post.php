<?php
session_set_cookie_params([
    'httponly' => true, 
    'samesite' => 'Lax' // Cross-site 요청에 대한 보호(Lax, Strict, None)
]);
session_start();

require_once 'config/db.php';

// 게시글 정보 가져오기
if (isset($_GET['id'])) {
    $post_id = htmlspecialchars($_GET['id']);
    $stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ?");
    $stmt->execute([$post_id]);
    $post = $stmt->fetch();

    if (!$post) {
        echo "<script>alert('존재하지 않는 게시글입니다.'); history.back();</script>";
        exit();
    }

    // 작성자 또는 관리자 여부 확인
    if ($post['user_id'] != $_SESSION['user_id'] && $_SESSION['role'] !== 'admin') {
        echo "<script>alert('게시글을 수정할 권한이 없습니다.'); history.back();</script>";
        exit();
    }

    // 기존 파일 정보 가져오기
    $stmt = $pdo->prepare("SELECT * FROM uploads WHERE post_id = ?");
    $stmt->execute([$post_id]);
    $file = $stmt->fetch();
} else {
    header("Location: index.php");
    exit();
}

// 게시글 수정 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $content = $_POST['content'];

    // 파일 수정 처리
    $upload_success = true;
    $new_file_name = null;

    if (isset($_FILES['uploaded_file']) && $_FILES['uploaded_file']['error'] == 0) {
        $allowed_extensions = ['png', 'PNG', 'jpg', 'pdf', 'xlsx'];
        $file_extension = pathinfo($_FILES['uploaded_file']['name'], PATHINFO_EXTENSION);
        $file_name_without_ext = pathinfo($_FILES['uploaded_file']['name'], PATHINFO_FILENAME);

        if (in_array($file_extension, $allowed_extensions)) {
            $upload_dir = 'uploads/';
            $new_file_name =  $file_name_without_ext.'_'.uniqid() . '.' . $file_extension;
            $new_file_path = $upload_dir . $new_file_name;

            if (!move_uploaded_file($_FILES['uploaded_file']['tmp_name'], $new_file_path)) {
                $upload_success = false;
                echo "<script>alert('파일 업로드 중 오류가 발생했습니다.'); history.back();</script>";
            } else {
                // 기존 파일이 있으면 삭제
                if ($file && file_exists($file['file_path'])) {
                    unlink($file['file_path']);
                }
            }
        } else {
            $upload_success = false;
            echo "<script>alert('허용되지 않은 파일 형식입니다.'); history.back(); </script>";
            exit();
        }
    }

    if ($upload_success) {
        // 게시글 업데이트
        $stmt = $pdo->prepare("UPDATE posts SET title = ?, content = ? WHERE id = ?");
        $stmt->execute([$title, $content, $post_id]);

        // 파일 정보 업데이트 또는 삽입
        if ($new_file_name) {
            if ($file) {
                // 기존 파일 정보 업데이트
                $stmt = $pdo->prepare("UPDATE uploads SET file_name = ?, file_path = ? WHERE id = ?");
                $stmt->execute([$_FILES['uploaded_file']['name'], $new_file_path, $file['id']]);
            } else {
                // 새로운 파일 정보 삽입
                $stmt = $pdo->prepare("INSERT INTO uploads (post_id, file_name, file_path) VALUES (?, ?, ?)");
                $stmt->execute([$post_id, $_FILES['uploaded_file']['name'], $new_file_path]);
            }
        }

        echo "<script>alert('게시글이 성공적으로 수정되었습니다.'); window.location.href='post.php?id=$post_id';</script>";
    }

    exit();
}
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>게시글 수정</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=New+Amsterdam&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="styles/base.css">
    <link rel="stylesheet" href="styles/write.css">
    <link rel="stylesheet" href="styles/edit_post.css">
    <link rel="icon" href="favicon/favicon.ico" type="image/x-icon">
</head>
<body>
    <?php require_once 'header.php' ?>

    <div id="create-post-content">
        <h1>EDIT POST</h1>

        <form method="post" action="edit_post.php?id=<?php echo htmlspecialchars($post_id); ?>" enctype="multipart/form-data">
            <label for="title">TITLE</label>
            <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($post['title']); ?>" required>

            <label for="content">CONTENT</label>
            <textarea id="content" name="content" rows="10" required><?php echo htmlspecialchars($post['content']); ?></textarea>

            <div id="upload-file">
                <label for="uploaded_file">UPLOADED FILE</label>
                <?php if ($file): ?>
                    <p><a href="<?php echo htmlspecialchars($file['file_path']); ?>" download><?php echo htmlspecialchars($file['file_name']); ?></a></p>
                    <input type="file" id="uploaded_file" name="uploaded_file">
                <?php else: ?>
                    <input type="file" id="uploaded_file" name="uploaded_file">
            </div>
            <?php endif; ?>

            <button type="submit">UPDATE</button>
        </form>
        <a id="back-btn" href="post.php?id=<?php echo $post_id; ?>">BACK</a>
    </div>
</body>
</html>
