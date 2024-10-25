<?php
// WordPress
require_once('wp-includes/class-phpass.php'); // PHPass 클래스

$password = 'admin'; // 평문 비밀번호
$wp_hasher = new PasswordHash(8, true); // WordPress에서 사용하는 PHPass 해시 객체

$hashed_password = $wp_hasher->HashPassword($password);

echo $hashed_password;
?>
