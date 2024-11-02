<?php
// session_set_cookie_params([
//     'httponly' => true, 
//     'samesite' => 'Lax' // Cross-site 요청에 대한 보호(Lax, Strict, None)
// ]);
session_start();

require_once 'config/db.php';

require_once 'queries.php';


$post_id = $_GET['id'];
$post_id = addslashes($post_id);
$result = $pdo->query("SELECT * FROM posts WHERE id = $post_id");
$post = $result->fetch();

if($post) {
    $post_user_id = (int)$post['user_id'];
    $post_user_id = $post_user_id;
    $result = $pdo->query("SELECT * FROM users WHERE id = $post_user_id");
    $post_user = $result->fetch();
}

if (!$post) {
    // echo "<script>alert('존재하지 않는 게시글입니다.'); window.location.href='index.php';</script>";
    echo "<script>alert('존재하지 않는 게시글입니다.');</script>";
    if (isset($_SERVER['HTTP_REFERER'])) {
        header("Location: " . $_SERVER['HTTP_REFERER']);
    } else {
        echo "<script>window.history.back();</script>";
    }
    exit();
}


// 댓글 작성 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment_content'])) {
	if (!isset($_SESSION['user_id'])) {
        echo "<script>alert('로그인이 필요합니다.'); window.location.href='login.php';</script>";
        exit();
    }

    $comment_content = addslashes($_POST['comment_content']);
    $user_id = $_SESSION['user_id'];

    $result = $pdo->exec("INSERT INTO comments (post_id, user_id, content) VALUES ('$post_id', '$user_id', '$comment_content')");

    echo "<script>alert('댓글이 성공적으로 작성되었습니다.'); window.location.href='post.php?id=$post_id';</script>";
    exit();
}

// 댓글 목록 가져오기
$result = $pdo->query("SELECT comments.*, users.username, users.homepage, users.profile_image FROM comments JOIN users ON comments.user_id = users.id WHERE comments.post_id = '$post_id' ORDER BY comments.created_at ASC");
$comments = $result->fetchAll();

// 파일 정보 가져오기
$result = $pdo->query("SELECT * FROM uploads WHERE post_id = '$post_id'");
$files = $result->fetchAll();

// 게시판 정보 가져오기
$board_id = $post['board_id'];
$result = $pdo->query("SELECT * FROM boards WHERE id = $board_id");
$board = $result->fetch();
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title><?php echo $post['title']; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=New+Amsterdam&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="styles/base.css"> 
    <link rel="stylesheet" href="styles/post.css">
    <link rel="icon" href="favicon/favicon.ico" type="image/x-icon">
</head>
<body>
    <?php require_once 'header.php' ?>
    <div id="main-container">
        <!-- 사이드바: 프로필 및 게시판 목록 -->
        <?php require_once 'sidebar.php'?>

        <section id="content">
            <p class="board-name"><a href="board.php?id=<?php echo $board['id']; ?>"><?= $board['name']; ?> ></a></p>
            <h1><?php echo $post['title']; ?></h1>
            <div class="post-profile-info">
				<img id="post-profile" src="uploads/<?php echo !empty($post_user['profile_image']) ? $post_user['profile_image'] : 'default.png'; ?>" alt="프로필 이미지">
                <div class="post-profile-info-2">
                    <p><?php echo $post_user['username'] ?></p>
                    <span><?php echo date('Y-m-d H:i', strtotime($post_user['created_at'])); ?></span>
                </div>
            </div>
                <p class="post-content"><?php echo $post['content']; ?></p>

            <?php if (!empty($files)): ?>
                <h2>첨부 파일</h2>
                <ul>
                    <?php foreach ($files as $file): ?>
                        <li>
                            <a href="download.php?file_path=<?= urlencode($file['file_path']); ?>">
                                <?php echo $file['file_name']; ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
                
            <?php if(isset($_SESSION['user_id']) and isset($_SESSION['role'])): ?>
                <?php if ($post['user_id'] == $_SESSION['user_id'] || $_SESSION['role'] === 'admin'): ?>
                    <div class="post-btn">
                    <a href="edit_post.php?id=<?php echo $post_id; ?>">수정</a>
                    <a href="delete_post.php?id=<?php echo $post_id; ?>" onclick="return confirm('이 게시글을 삭제하시겠습니까?')">삭제</a>
                </div>
                <?php endif; ?>
            <?php endif; ?>

            <h2 id="comment-title-2">댓글</h2>
            <ul>
                <?php foreach ($comments as $comment): ?>
                <li>
                    <div class="comment-author-area">
                        <img id="post-profile" src="uploads/<?php echo !empty($comment['profile_image']) ? $comment['profile_image'] : 'default.png'; ?>" alt="프로필 이미지">
                            <span class="comment-author">
                                <a href="<?php echo isset($_GET['homepage']) ? $_GET['homepage'] : $comment['homepage'];?>"><?php echo $comment['username']; ?></a>
                            </span>
        
                            <span class="comment-date"><?php echo date('Y-m-d H:i', strtotime($comment['created_at'])); ?></span>

                            <?php if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && ($comment['user_id'] == $_SESSION['user_id'] || $_SESSION['role'] === 'admin')): ?>
                                <div id="comment-delete-btn">
                                <a href="delete_comment.php?id=<?php echo $comment['id']; ?>&post_id=<?php echo $post_id; ?>" onclick="return confirm('이 댓글을 삭제하시겠습니까?')">삭제</a>
                                </div>
                            <?php endif; ?>

                        </div>
                        
                        <p class="comment-body"><?php echo $comment['content']; ?></p>

                    
                    </li>
                <?php endforeach; ?>
            </ul>
            
            <!-- <h2>댓글 작성</h2> -->
            <form method="post" action="post.php?id=<?php echo $post_id; ?>" onsubmit="return checkLoginAndSubmit();">
                <textarea name="comment_content" rows="3" required></textarea><br>
                <button class="post-btn post-btn2" type="submit">댓글 작성</button>
            </form>

            <script>
                function checkLoginAndSubmit() {
                    <?php if (!isset($_SESSION['user_id'])): ?>
                        alert('로그인이 필요합니다.');
                        window.location.href = 'login.php';
                        return false;
                    <?php endif; ?>
                    return true;
                }
            </script>
            <?php if(isset($_SESSION['user_id']) and isset($_SESSION['role'])): ?>
                <?php if ($_SESSION['role'] == 'admin'): ?>
                <button class="list-btn" onclick="location.href='admin/posts.php'">글 관리</button>
                <?php endif; ?>
            <?php else: ?>
                <button class="list-btn" onclick="location.href='board.php?id=<?= $board['id']; ?>'">목록</button>
            <?php endif; ?>
        </section>
    </div>
</body>
</html>
