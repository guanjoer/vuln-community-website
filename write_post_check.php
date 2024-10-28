<?php
// session_set_cookie_params([
//     'httponly' => true, 
//     'samesite' => 'Lax'
// ]);
session_start();

require_once 'config/db.php';
require_once 'queries.php';


if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // if ($_SESSION['csrf_token'] != $_POST['_csrf']) {
    //     echo "<script>alert('잘못된 접근입니다.'); history.back();</script>";
    //     exit();
    // }

    $user_id = $_SESSION['user_id'];
    $board_id = $_POST['board_id'];
    $title = $_POST['title'];
    $title = addslashes($title);
    $content = $_POST['content'];
    $content = addslashes($content);

	
    $upload_success = true;

    if (isset($_FILES['uploaded_file']) && $_FILES['uploaded_file']['error'] == 0) {
        // $allowed_extensions = ['png', 'jpg', 'pdf', 'xlsx'];
        // $allowed_mime_types = ['image/png', 'image/jpeg', 'application/pdf', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
        $disallowed_exts = ['php', 'php3'];

        $file_extension = pathinfo($_FILES['uploaded_file']['name'], PATHINFO_EXTENSION);
        $file_extension = $file_extension;
        $file_name_without_ext = pathinfo($_FILES['uploaded_file']['name'], PATHINFO_FILENAME);
        // $finfo = finfo_open(FILEINFO_MIME_TYPE);
        // $mime_type = finfo_file($finfo, $_FILES['uploaded_file']['tmp_name']);

        if (!in_array($file_extension, $disallowed_exts)) {
            $upload_dir = 'uploads/';
            // $file_name =  $file_name_without_ext.'_'.uniqid() . '.' . $file_extension;
            $file_name =  $file_name_without_ext. '.' . $file_extension;
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

    // 파일 업로드 성공 시 게시글 저장
    if ($upload_success) {
        $result = $pdo->exec("INSERT INTO posts (user_id, board_id, title, content) VALUES ($user_id, $board_id, '$title', '$content')");

        // 게시글 ID
        $post_id = $pdo->lastInsertId();

        // 파일 정보 저장
        if (isset($file_path)) {
            $stmt = $pdo->exec("INSERT INTO uploads (post_id, file_name, file_path) VALUES ('$post_id', '" . $_FILES['uploaded_file']['name'] . "', '$file_path')");
        }
        echo "<script>alert('게시글이 성공적으로 작성되었습니다.'); window.location.href='index.php';</script>";
    } else {
        echo "<script>alert('게시글 작성이 취소되었습니다. 허용된 파일 형식을 사용해주세요.'); history.back();</script>";
        exit();
    }

    exit();
}
?>
