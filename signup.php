<?php
session_set_cookie_params([
    'httponly' => true, 
    'samesite' => 'Lax' // Cross-site 요청에 대한 보호(Lax, Strict, None)
]);
session_start();

// DB 연결 설정
$host = getenv('DB_HOST');
$db = getenv('DB_NAME');
$user = getenv('DB_USER');
$pass = getenv('DB_PASS');
$charset = 'utf8mb4';

require_once 'config/db.php';

// 회원가입 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $pass_confirm = $_POST['pass-confirm'];

    // 비밀번호 확인
    if ($password !== $pass_confirm) {
        echo "<script>alert('비밀번호가 일치하지 않습니다. 다시 확인해주세요.'); window.history.back();</script>";
        exit();
    }

    if ($password == "" || $pass_confirm == "") {
        echo "<script>alert('비밀번호를 입력해주세요.'); window.history.back();</script>";
        exit();
    }

    if ($username == "" || $email == "") {
        echo "<script>alert('아이디 및 이메일을 확인하세요.'); window.history.back();</script>";
        exit();
    }

    $password_hashed = password_hash($password, PASSWORD_BCRYPT);

    // 중복 체크 - 이메일
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetchColumn() > 0) {
        echo "<script>alert('이미 존재하는 이메일입니다. 다른 이메일을 사용해주세요.'); window.history.back();</script>";
        exit();
    }

    // 중복 체크 - 아이디
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetchColumn() > 0) {
        echo "<script>alert('이미 존재하는 아이디입니다. 다른 아이디를 사용해주세요.'); window.history.back();</script>";
        exit();
    }

    // 사용자 등록
    $uuid = bin2hex(random_bytes(16));
    $sql = "INSERT INTO users (uuid, username, email, password) VALUES (?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$uuid, $username, $email, $password_hashed]);

    $_SESSION['username'] = $username;

    header("Location: signup_success.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>Sign Up</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=New+Amsterdam&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="styles/base.css"> 
    <link rel="stylesheet" href="styles/sign.css">
    <link rel="stylesheet" href="styles/signup.css">
    <link rel="icon" href="favicon/favicon.ico" type="image/x-icon">
    <script>
        function checkUsername() {
            const username = document.getElementById('username').value;
            if (username === '') {
                alert('아이디를 입력해주세요.');
                return;
            }

            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'check_username.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                if (xhr.status === 200) {
                    const response = JSON.parse(xhr.responseText);
                    if (response.available) {
                        alert('사용 가능한 아이디입니다.');
                    } else {
                        alert('이미 사용 중인 아이디입니다. 다른 아이디를 입력해주세요.');
                        document.getElementById('username').focus();
                    }
                }
            };
            xhr.send('username=' + encodeURIComponent(username));
        }

        function validateForm() {
            const password = document.getElementById('password').value;
            const passConfirm = document.getElementById('pass-confirm').value;

            if (password !== passConfirm) {
                alert('비밀번호가 일치하지 않습니다. 다시 확인해주세요.');
                document.getElementById('pass-confirm').focus();
                return false;
            }
            return true;
        }
    </script>
</head>
<body>
    <?php require_once 'header.php' ?>

    <div id="login-container">
        <h1>Sign up</h1>
        <form method="post" action="signup.php" onsubmit="return validateForm();">
            <input type="email" id="email" name="email" placeholder="E-MAIL" required><br>
        
        <div class="input-group">
            <input type="text" id="username" name="username" placeholder="USER NAME" required>
            <a href="#" class="confirm-btn" onclick="checkUsername()">Confirm Username</a>
        </div>
            <input type="password" id="password" name="password" placeholder="PASSWORD" required><br>
            <input type="password" id="pass-confirm" name="pass-confirm" placeholder="CONFIRM PASSWORD" required><br>
            <button type="submit">SIGN UP</button>
        </form>
        <p id="account-message"><a href="login.php">Do you have already Account?</a></p>
    </div>
</body>
</html>
