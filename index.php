<?php
session_set_cookie_params([
    'httponly' => true, 
    'samesite' => 'Lax' // Cross-site 요청에 대한 보호(Lax, Strict, None)
]);

session_start();

// CSRF Token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require_once 'config/db.php';

require_once 'queries.php';

// 현재 페이지 번호
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// 한 페이지에 표시할 게시글 수
$posts_per_page = 10;

// offset + 1의 레코드 부터 데이터를 가져옴 // 즉 offest = 15이면 16번째 레코드 부터 데이터를 가져옴
$offset = ($page - 1) * $posts_per_page;

// 전체 글의 수
$total_posts_stmt = $pdo->query("SELECT COUNT(*) FROM posts");
$total_posts = $total_posts_stmt->fetchColumn();

// 전체 페이지 수 // 32개의 글이 존재하고 페이지 당 10개의 글을 표시한다면 3.2의 올림인 4 페이지가 전체 페이지 수
$total_pages = ceil($total_posts / $posts_per_page);

// 현재 페이지에 해당하는 글
$stmt = $pdo->query("SELECT posts.id, posts.title, posts.created_at, users.username, posts.board_id 
                     FROM posts 
                     JOIN users ON posts.user_id = users.id 
                     ORDER BY posts.created_at DESC 
                     LIMIT $posts_per_page OFFSET $offset");
$posts = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>GuanJoer' Community</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=New+Amsterdam&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="styles/base.css"> 
    <link rel="stylesheet" href="styles/main.css"> 
    <link rel="icon" href="favicon/favicon.ico" type="image/x-icon">
</head>
<body>
    <?php require_once 'header.php' ?>

    <nav id="search-bar">
        <form method="get" action="search.php">
            <input type="text" name="q" placeholder="검색어를 입력하세요" required>
            <button type="submit">검색</button>
        </form>
    </nav>

    <div id="main-container">
        <!-- 사이드바: 프로필 및 게시판 목록 -->
        <?php require_once 'sidebar.php'?>

        <!-- 메인 콘텐츠: 전체 글 목록 -->
        <section id="content">
            <h2 class="header-2"><a href="board.php">전체글 보기</a></h2>
            <?php if($posts): ?>
            <table>
                <thead>
                    <tr>
                        <th>번호</th>
                        <th>제목</th>
                        <th>글쓴이</th>
                        <th>작성일</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    // $counter = count($posts); 
                    $counter = $total_posts - ($page - 1) * $posts_per_page;
                    foreach ($posts as $post): 
                    ?>
                        <tr>
                            <td><?php echo $counter; ?></td>
                            <td>
                                <a href="post.php?id=<?php echo $post['id']."&board=".$post['board_id']; ?>">
                                    <?php echo htmlspecialchars($post['title']); ?>
                                </a>
                            </td>
                            <td><?php echo htmlspecialchars($post['username']); ?></td>
                            <td><?php echo date('Y-m-d H:i', strtotime($post['created_at'])); ?></td>
                        </tr>
                    <?php 
                    $counter--;
                    endforeach; 
                    ?>
                </tbody>
            </table>
            <?php else: ?>
                <p>게시글이 없습니다.</p>
            <?php endif; ?>
            
            <?php if($posts): ?>
            <!-- 페이지 네비게이션 -->
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
        </section>
    </div>
    
    <a href="redirect.php?url=http://127.0.0.1/community_site/post.php?id=27&board=3">Redirection Test</button>
</body>
</html>
