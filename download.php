<?php
session_start();
require_once 'config/db.php';

if (isset($_GET['post_id']) && isset($_GET['file_id'])) {
    $post_id = (int) $_GET['post_id'];   
    $file_id = (int) $_GET['file_id']; 

    // post_id와 file_id가 일치하는 파일
    $stmt = $pdo->prepare("SELECT * FROM uploads WHERE post_id = ? AND id = ?");
    $stmt->execute([$post_id, $file_id]);
    $file = $stmt->fetch();

    if ($file) {
        // 파일 경로 설정
		$base_dir = 'C:/xampp/htdocs/community_site/';

		$combined_path = $base_dir . $file['file_path'];
		
		// 파일 정규화. 즉 상대 경로를 절대 경로로 치환
        $file_path = realpath($base_dir . $file['file_path']);

        // 파일 존재 유무 및 경로 검증
		// $combined_path의 값이 $base_dir의 문자열 값으로 시작한다면 0 반환
        if (file_exists($combined_path) && strpos($file_path, $base_dir) === 0 && file_exists($file_path)) {
            // 파일 다운로드를 위한 헤더 설정
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
            header('Content-Length: ' . filesize($file_path));

            // 파일을 읽어서 클라이언트로 전송
            readfile($file_path);
            exit;
        } else {
            header("HTTP/1.0 404 Not Found");
			include('./errors/404.php');
            exit;
        }
    } else {
        // 파일이 존재하지 않거나 post_id에 속하지 않을 경우
        echo "<script>alert('파일을 찾을 수 없습니다.'); window.location.href='post.php?id=$post_id';</script>";
        exit;
    }
} else {
    // 파라미터가 없을 경우
    header("HTTP/1.0 404 Not Found");
	include('./errors/404.php');
    exit;
}
?>
