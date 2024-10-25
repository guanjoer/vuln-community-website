<?php
// 리다이렉트할 URL 가져오기
if (isset($_GET['url'])) {
    $url = $_GET['url'];

    // 301 혹은 302 상태 코드로 리다이렉트 (예시로 302 리다이렉트를 사용)
    header("Location: $url", true, 302); // 301을 사용하고 싶으면 302 대신 301로 변경
    exit();
}
?>