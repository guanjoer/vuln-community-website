<?php
// session_set_cookie_params([
//     'httponly' => true, 
//     'samesite' => 'Lax'
// ]);
session_start();

require_once 'config/db.php';
require_once 'queries.php';


$query = isset($_GET['q']) ? $_GET['q'] : '';
$query = trim($query);
$query = strtolower($query);

if (empty($query)) {
    echo "<script>alert('검색어를 입력하세요.'); history.back();</script>";
    exit();
}


$search_query = "%" . $query . "%";

$search_q = "
    SELECT posts.id, posts.title, posts.created_at, users.username, posts.board_id 
    FROM posts 
    JOIN users ON posts.user_id = users.id 
    WHERE posts.title LIKE '$search_query' OR posts.content LIKE '$search_query'
    ORDER BY posts.created_at DESC
";

// $stmt = $pdo->query($search_q);
$result = $pdo->query($search_q);
$posts = $result->fetchAll();

$board_ids = array_unique(array_column($posts, 'board_id')); // board_id 필드의 값들만 새로운 배열로 생성 및 중복 제거
$boards = [];  // 빈 배열로 초기화
if (!empty($board_ids)) {
    $in  = implode(',', $board_ids);
    $result = $pdo->query("SELECT id, name FROM boards WHERE id IN ($in)");
    $boards = $result->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>Search Results</title>

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
        <?php require_once 'sidebar.php'?>
        <section id="content">

        <h2>검색어: "<?php echo $query; ?>"</h2>
        <?php if (count($posts) > 0): ?>
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
                    $counter = count($posts); ?>
            
                <?php foreach ($posts as $post): ?>
                    <?php
                    // 게시글의 게시판 이름을 가져오기
                    $board_name = 'none';
                    foreach ($boards as $board) {
                        if ($board['id'] == $post['board_id']) {
                            $board_name = $board['name'];
                            break;
                        }
                    }
                    ?>
                    <tr>
                        <td>
                            <?php echo $counter; ?>
                        </td>
                        <td>
                            <a href="post.php?id=<?php echo $post['id']; ?>&board=<?php echo $post['board_id']; ?>">
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
                    <?php $counter--; endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>검색 결과가 없습니다.</p>
        <?php endif; ?>
        </section>
        </div>
</body>
</html>
