<?php
// session_set_cookie_params([
//     'httponly' => true, 
//     'samesite' => 'Lax' 
// ]);

session_start();

require_once 'config/db.php';

require_once 'queries.php';

$page = isset($_GET['page']) ? $_GET['page'] : 1;
$posts_per_page = 10;
$offset = ((int)$page - 1) * $posts_per_page;

// Default: DESC
$order = isset($_GET['order']) && $_GET['order'] === 'asc' ? 'ASC' : 'DESC';
$new_order = $order === 'ASC' ? 'desc' : 'asc';


if (isset($_GET['id'])) {
    $board_id = $_GET['id'];
    $board_id = $board_id;

    $total_posts_query = $pdo->query("SELECT COUNT(*) FROM posts WHERE board_id = '$board_id'");
    $total_posts = $total_posts_query->fetchColumn();
    
    // 각 게시판에 해당하는 총 페이지 수
    $total_pages = ceil($total_posts / $posts_per_page);

    $result = $pdo->query("SELECT * FROM boards WHERE id = '$board_id'");
    $board = $result->fetch();

    if (!$board) {
        echo "<script>alert('존재하지 않는 게시판입니다.'); window.location.href='index.php';</script>";
        exit();
    }

    // 게시판에 속하는 글 목록 가져오기
    $result = $pdo->query("SELECT posts.id, posts.title, posts.created_at, users.username 
                           FROM posts 
                           JOIN users ON posts.user_id = users.id 
                           WHERE posts.board_id = '$board_id'
                           ORDER BY posts.created_at $order
                           LIMIT $posts_per_page OFFSET $offset");
    $posts = $result->fetchAll();
} else {
    $total_posts_stmt = $pdo->query("SELECT COUNT(*) FROM posts");
    $total_posts = $total_posts_stmt->fetchColumn();
    
    $total_pages = ceil($total_posts / $posts_per_page);

    $result = $pdo->query("SELECT posts.id, posts.title, posts.created_at, users.username 
                           FROM posts 
                           JOIN users ON posts.user_id = users.id 
                           ORDER BY posts.created_at $order
                           LIMIT $posts_per_page OFFSET $offset");
    $all_posts = $result->fetchAll();
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
        <title><?php echo $board['name']; ?></title>
    <?php else: ?>
        <title>전체글</title>
    <?php endif; ?>
</head>
<body>
    
    <?php require_once 'header.php' ?>

    <nav id="search-bar">
        <form method="get" action="search.php">
            <input type="text" name="q" placeholder="Type to Search..." required>
            <button type="submit">
                <img src="icons/search.png" alt="search-icon" width="25px">
            </button>
        </form>
    </nav>

    <div id="main-container">
        <?php require_once 'sidebar.php'?>

        <section id="content">
            <?php if(isset($_GET['id'])): ?>
                <h1><?php echo $board['name']; ?></h1>
            <?php else: ?>
                <h2>전체글</h2>
            <?php endif; ?>
            <p>현재 페이지: 
            <?php if(isset($_GET['page'])): ?>
                <script>
                    const params = new URLSearchParams(document.location.search);
                    const pageValue = params.get('page');
                    document.write(pageValue);
                </script>
            <?php else: ?>
                1               
             <?php endif; ?>   
            </p>

            <?php if (isset($_GET['id']) && $posts): ?>
            <table>
                <thead class="table-header">
                    <tr>
                        <th>번호</th>
                        <th>제목</th>
                        <th>글쓴이</th>
                        <th>
                            <a href="?page=<?php echo $page; ?><?php echo isset($board_id) ? '&id=' . $board_id : ''; ?>&order=<?php echo $new_order; ?>">작성일</a>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php $counter = $total_posts - ((int)$page - 1) * $posts_per_page; ?>
                    <?php foreach ($posts as $post): ?>
                        <tr>
                            <td><?php echo $counter; ?></td>
                            <td>
                                <a href="post.php?id=<?php echo $post['id']; ?>">
                                        <?php echo $post['title']; ?>
                                </a>
                            </td>
                            <td>
                                <?php echo $post['username']; ?>
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
                            <?php $counter = $total_posts - ((int)$page - 1) * $posts_per_page; ?>
                            <?php foreach ($all_posts as $post): ?>
                                <tr>
                                    <td><?php echo $counter ?></td>
                                    <td>
                                        <a href="post.php?id=<?php echo $post['id']; ?>">
                                            <?php echo $post['title']; ?>
                                        </a>
                                    </td>
                                    <td>
                                        <?php echo $post['username']; ?>
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
                        <a href="?page=<?php echo $i; ?><?php echo isset($board_id) ? '&id=' . $board_id : ''; ?>"<?php if ($i === (int)$page) echo ' class="active"'; ?>>
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
