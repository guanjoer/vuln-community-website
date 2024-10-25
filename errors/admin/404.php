<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>Page Not Found</title>

	<link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=New+Amsterdam&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">

	<link rel="stylesheet" href="../styles/base.css"> 
	<link rel="stylesheet" href="../styles/main.css"> 
    <link rel="stylesheet" href="../styles/error.css">
    <link rel="icon" href="../favicon/favicon.ico" type="image/x-icon">
</head>
<body>
	<?php require_once 'header.php' ?>
	<div id="err-container">
		<h1>Page Not Found</h1>
		<p>요청하신 페이지가 존재하지 않습니다.</p>
		<a id="home-btn" href="../index.php">홈으로 돌아가기</a>
	</div>
    
</body>
</html>