<?php
session_set_cookie_params([
    'httponly' => true, 
    'samesite' => 'Lax' // Cross-site 요청에 대한 보호(Lax, Strict, None)
]);
session_start();

require_once 'config/db.php';

// 로그인 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // 사용자 정보 확인
    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // 세션에 사용자 정보 저장
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['uuid'] = $user['uuid'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        // Prevent Session Fixation Attack
        session_regenerate_id(true);
        
        // 로그인 성공 시 리다이렉트
        $redirect_url = isset($_POST['redirect_url']) ? $_POST['redirect_url'] : 'index.php';
        // var_dump($redirect_url);
        if (strpos($redirect_url, '/signup') || strpos($redirect_url, '/signup_success.php')) {
            $redirect_url = 'index.php';
        }

        // if (strpos($redirect_url, '192.168.0.10') === false && strpos($redirect_url, 'localhost') === false) {
        //     echo "<script>alert('올바르지 않은 접근입니다.'); history.back();</script>";
        //    exit();
        //}

        header("Location: " . $redirect_url);
        exit();
    } else {
        echo "<script>alert('로그인에 실패했습니다. 다시 시도해주세요.'); history.back();</script>";
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>Login</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=New+Amsterdam&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="styles/base.css"> 
    <link rel="stylesheet" href="styles/sign.css">
    <link rel="icon" href="favicon/favicon.ico" type="image/x-icon">
</head>
<body>
    <?php require_once 'header.php' ?>

    <div id="login-container">
        <h1>Login</h1>
        <?php if (isset($error)): ?>
            <p style="color:red;"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <form method="post" action="login.php">
            <input type="hidden" name="redirect_url" value="<?php echo htmlspecialchars($_SERVER['HTTP_REFERER']); ?>">
            <input type="text" id="username" name="username" placeholder="USER NAME" required><br>
    
            <input type="password" id="password" name="password" placeholder="PASSWORD" required><br>
    
            <button type="submit">LOGIN</button>
        </form>
        <p id="account-message">
            <a href="signup.php">Do you not have an account yet?</a>
        </p>
    </div>
</body>
</html>
