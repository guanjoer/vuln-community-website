<?php
session_set_cookie_params([
    'httponly' => true, 
    'samesite' => 'Lax' // Cross-site 요청에 대한 보호(Lax, Strict, None)
]);
session_start();

require_once './error_handling.php';

require_once '../config/db.php';

require_once '../queries.php';

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$boards_per_page = 5;
$offset = ($page - 1) * $boards_per_page;

$total_boards_stmt = $pdo->query("SELECT COUNT(*) FROM boards");
$total_boards = $total_boards_stmt->fetchColumn();

$total_pages = ceil($total_boards / $boards_per_page);

// 게시판 목록 가져오기
$stmt = $pdo->query("SELECT * FROM boards ORDER BY created_at DESC
					 LIMIT $boards_per_page OFFSET $offset");
$boards = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>게시판 관리</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=New+Amsterdam&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="../styles/base.css"> 
	<link rel="stylesheet" href="../styles/main.css"> 
	<link rel="stylesheet" href="styles/boards.css">

	<link rel="icon" href="../favicon/favicon.ico" type="image/x-icon">
</head>
<body>
    <?php require_once 'admin_header.php' ?>

    <div id="main-container">
        <?php require_once 'admin_sidebar.php'?>
        <section id="content">
    <h1>관리자 > 게시판 관리</h1>
	
    <button onclick="location.href='create_board.php'">새 게시판 생성</button>

    <h2>게시판 목록</h2>

	<table>
		<thead>
			<tr>
				<th>이름</th>
				<th>설명</th>
				<th>수정</th>
				<th>삭제</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($boards as $board): ?>
			<tr>
				<td class="board-name">
					<?php echo htmlspecialchars($board['name']); ?>
				</td>
				<td>
					<?php echo htmlspecialchars($board['description']); ?>
				</td>
				<td>
					<a class="board-btn" href="edit_board.php?id=<?php echo $board['id']; ?>">수정</a>
				</td>
				<td>
					<a class="board-btn" href="delete_board.php?id=<?php echo $board['id']; ?>" onclick="return confirm('이 게시판을 삭제하시겠습니까?')">삭제</a>
				</td>
			</tr>
			<?php endforeach; ?>
        </section>
	</div>
	</tbody>
	</table>
	
	<?php if($boards): ?>
	<div id="pagination">
		<?php if ($page > 1): ?>
			<span>< </span><a href="?page=<?php echo $page - 1; ?>">이전</a>
		<?php endif; ?>

		<?php for ($i = 1; $i <= $total_pages; $i++): ?>
			<a href="?page=<?php echo $i; ?>"<?php if ($i === $page) echo ' class="active"'; ?>>
				<?php echo $i; ?>
			</a>
		<?php endfor; ?>

		<?php if ($page < $total_pages): ?>
			<a href="?page=<?php echo $page + 1; ?>">다음</a><span> ></span>
		<?php endif; ?>
	</div>
	<?php endif; ?>
</body>
</html>
