<?php
session_set_cookie_params([
    'httponly' => true, 
    'samesite' => 'Lax' // Cross-site 요청에 대한 보호(Lax, Strict, None)
]);

session_start();

require_once 'config/db.php';

require_once 'queries.php';

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$posts_per_page = 10;
$offset = ($page - 1) * $posts_per_page;


// 게시판 정보 가져오기
if (isset($_GET['id'])) {
    $board_id = htmlspecialchars($_GET['id']);

    $total_posts_stmt = $pdo->prepare("SELECT COUNT(*) FROM posts WHERE board_id = ?");
    $total_posts_stmt->execute([$board_id]);
    $total_posts = $total_posts_stmt->fetchColumn();
    
    // 각 게시판에 해당하는 총 페이지 수
    $total_pages = ceil($total_posts / $posts_per_page);

    $stmt = $pdo->prepare("SELECT * FROM boards WHERE id = ?");
    $stmt->execute([$board_id]);
    $board = $stmt->fetch();

    if (!$board) {
        echo "<script>alert('존재하지 않는 게시판입니다.'); window.location.href='index.php';</script>";
        exit();
    }

    // 게시판에 속하는 글 목록 가져오기
    $stmt = $pdo->prepare("SELECT posts.id, posts.title, posts.created_at, users.username 
                           FROM posts 
                           JOIN users ON posts.user_id = users.id 
                           WHERE posts.board_id = ? 
                           ORDER BY posts.created_at DESC
                           LIMIT $posts_per_page OFFSET $offset");
    $stmt->execute([$board_id]);
    $posts = $stmt->fetchAll();
} else {
    $total_posts_stmt = $pdo->query("SELECT COUNT(*) FROM posts");
    $total_posts = $total_posts_stmt->fetchColumn();
    
    $total_pages = ceil($total_posts / $posts_per_page);

    $stmt = $pdo->prepare("SELECT posts.id, posts.title, posts.created_at, users.username 
                           FROM posts 
                           JOIN users ON posts.user_id = users.id 
                           ORDER BY posts.created_at DESC
                           LIMIT $posts_per_page OFFSET $offset");
    $stmt->execute();
    $all_posts = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=New+Amsterdam&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="styles/base.css"> 
    <link rel="stylesheet" href="styles/main.css">
    <link rel="icon" href="favicon/favicon.ico" type="image/x-icon">
    <?php if(isset($_GET['id'])): ?>
        <title><?php echo htmlspecialchars($board['name']); ?></title>
    <?php else: ?>
        <title>전체글</title>
    <?php endif; ?>
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
        <?php require_once 'sidebar.php'?>

        <section id="content">
            <?php if(isset($_GET['id'])): ?>
                <h1><?php echo htmlspecialchars($board['name']); ?></h1>
            <?php else: ?>
                <h2>전체글</h2>
            <?php endif; ?>

            <?php if (isset($_GET['id']) && $posts): ?>
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
                    <?php $counter = $total_posts - ($page - 1) * $posts_per_page; ?>
                    <?php foreach ($posts as $post): ?>
                        <tr>
                            <td><?php echo $counter; ?></td>
                            <td>
                                <a href="post.php?id=<?php echo htmlspecialchars($post['id']); ?>">
                                        <?php echo htmlspecialchars($post['title']); ?>
                                </a>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($post['username']); ?>
                            </td>
                            <td>
                                <?php echo date('Y-m-d H:i', strtotime($post['created_at'])); ?>
                            </td>
                        </tr>
                        <?php $counter--; ?>
                    <?php endforeach; ?>
                    </tbody>
                    </table>

                    <?php elseif (!isset($_GET['id']) && $all_posts): ?>
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
                            <?php $counter = $total_posts - ($page - 1) * $posts_per_page; ?>
                            <?php foreach ($all_posts as $post): ?>
                                <tr>
                                    <td><?php echo $counter ?></td>
                                    <td>
                                        <a href="post.php?id=<?php echo htmlspecialchars($post['id']); ?>">
                                            <?php echo htmlspecialchars($post['title']); ?>
                                        </a>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($post['username']); ?>
                                    </td>
                                    <td>
                                        <?php echo date('Y-m-d H:i', strtotime($post['created_at'])); ?>
                                    </td>
                                </tr>
                                <?php $counter--; ?>
                            <?php endforeach; ?>
                    </tbody>
                            </table>
                <?php else: ?>
                    <p>게시글이 없습니다.</p>
                <?php endif; ?>
                
                <?php if((!isset($_GET['id']) && $all_posts) || isset($_GET['id']) && ($posts)): ?>
                <div id="pagination">
                    <?php if ($page > 1): ?>
                        <span>< </span>
                        <a href="?page=<?php echo $page - 1; ?><?php echo isset($board_id) ? '&id=' . $board_id : ''; ?>">이전</a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?page=<?php echo $i; ?><?php echo isset($board_id) ? '&id=' . $board_id : ''; ?>"<?php if ($i === $page) echo ' class="active"'; ?>>
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?php echo $page + 1; ?><?php echo isset($board_id) ? '&id=' . $board_id : ''; ?>">다음</a><span> ></span>
                    <?php endif; ?>
                    </div>
                <?php else: ?>
                
            <?php endif; ?>
            </section>
        </div>
</body>
</html>
