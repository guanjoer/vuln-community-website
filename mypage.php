<?php
session_set_cookie_params([
    'httponly' => true, 
    'samesite' => 'Lax' // Cross-site 요청에 대한 보호(Lax, Strict, None)
]);
session_start();

require_once 'config/db.php';

require_once 'queries.php';

// 사용자 정보 가져오기
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT username, email, profile_image, password FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// 프로필 업데이트 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $profile_image = $user['profile_image']; // 기존 이미지

    // 프로필 이미지 업로드 처리
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $target_dir = "uploads/";
        $imageFileType = strtolower(pathinfo($_FILES["profile_image"]["name"], PATHINFO_EXTENSION));
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

        // 파일 유형 체크
        if (in_array($imageFileType, $allowed_types)) {
            // 기존 이미지 파일 삭제
            if (!empty($profile_image) && file_exists($target_dir . $profile_image)) {
                unlink($target_dir . $profile_image);
            }

            // 새로운 이미지 파일 저장
            $new_filename = uniqid() . "." . $imageFileType;
            $target_file = $target_dir . $new_filename;
            if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_file)) {
                $profile_image = $new_filename;
            } else {
                echo "<script>alert('파일 업로드 중 오류가 발생했습니다.'); history.back();</script>";
                exit();
            }
        } else {
            echo "<script>alert('허용되지 않는 파일 형식입니다.'); history.back();</script>";
            exit();
        }
    }

    // 비밀번호 변경 처리
    if (!empty($_POST['current_password']) && !empty($_POST['new_password']) && !empty($_POST['confirm_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        // 현재 비밀번호 검증
        if (password_verify($current_password, $user['password'])) {
            // 새로운 비밀번호 확인
            if ($new_password === $confirm_password) {
                // 비밀번호 해시화
                $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);

                // 비밀번호 업데이트
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$new_password_hash, $user_id]);

                echo "<script>alert('비밀번호가 성공적으로 변경되었습니다.');</script>";
            } else {
                echo "<script>alert('새 비밀번호가 일치하지 않습니다.'); history.back();</script>";
                exit();
            }
        } else {
            echo "<script>alert('현재 비밀번호가 올바르지 않습니다.'); history.back();</script>";
            exit();
        }
    }

    // 사용자 정보 업데이트
    $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, profile_image = ? WHERE id = ?");
    $stmt->execute([$username, $email, $profile_image, $user_id]);

    // 업데이트된 정보를 세션에 반영
    $_SESSION['username'] = $username;

    // 사용자에게 성공 메시지 표시
    echo "<script>alert('프로필이 성공적으로 업데이트되었습니다.'); window.location.href='mypage.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>Account Info</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=New+Amsterdam&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="styles/base.css"> 
    <link rel="stylesheet" href="styles/mypage.css">
    <link rel="icon" href="favicon/favicon.ico" type="image/x-icon">
    <script>
        function previewImage(event) {
            const reader = new FileReader();
            reader.onload = function(){
                const output = document.getElementById('profile-preview-2');
                output.src = reader.result;
                output.style.borderRadius = '50%';
                output.style.objectFit = 'cover';
            }
            reader.readAsDataURL(event.target.files[0]);
        }
    </script>
</head>
<body>
    <?php require_once 'header.php' ?>

    <div id="main-container">
        <?php require_once 'sidebar.php'?>

        <section id="content">
        <h1>Account Info</h1>

    <form method="post" action="mypage.php" enctype="multipart/form-data">
        <label for="username">USER NAME</label>
        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required><br>

        <label for="email">E-MAIL</label>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required><br>

        <label for="profile_image">PROFILE IMAGE</label><br>
        <img id="profile-preview-2" src="uploads/<?php echo !empty($user['profile_image']) ? htmlspecialchars($user['profile_image']) : 'default.png'; ?>" alt="프로필 이미지" width="100" height="100"><br>
        <input type="file" id="profile_image" name="profile_image" accept="image/*" onchange="previewImage(event)"><br>

        <h2>CHAGE PASSWORD</h2>
        <label for="current_password">Current Password</label>
        <input type="password" id="current_password" name="current_password" placeholder="Enter a current password"><br>

        <label for="new_password">New Password</label>
        <input type="password" id="new_password" name="new_password" placeholder="Enter a new password"><br>

        <label for="confirm_password">Confirm New Password</label>
        <input type="password" id="confirm_password" name="confirm_password" placeholder="Enter a confirm new password"><br>

        <button type="submit">Update</button>
    </form>
    </section>
    </div>
</body>
</html>
