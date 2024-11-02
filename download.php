<?php
session_start();

if (isset($_GET['file_path'])) {
    $file_path = $_GET['file_path'];

    if (file_exists($file_path)) {
        // 파일 다운로드를 위한 헤더 설정
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
        header('Content-Length: ' . filesize($file_path));

        readfile($file_path);
        exit;
    } else {
        echo "<script>alert(". $file_path . " '파일을 찾을 수 없습니다.'); history.back();</script>";
        exit;
    }
} else {
    echo "<script>alert('잘못된 요청입니다.'); history.back();</script>";
    exit;
}
?>
