# GuanJoer' Community

해당 프로젝트는 `PHP`, `MySQL`을 주요 기술 스택으로 사용하여 만든 **커뮤니티 웹 사이트**입니다.

---

### **주요 기능**

- 회원 가입 및 로그인(세션 기반 인증)
- 게시글 **CRUD(Create/Read/Update/Delete)**
- 파일 업로드 기능
- 파일 다운로드 기능
- 댓글 기능
- 게시판 기능
- 프로필 수정 기능(프로필 사진/비밀번호/아이디/이메일)
- 게시글 검색 기능
- 페이지네이션 기능(게시글/사용자/게시판 목록)
- 관리자 대시보드 기능(사용자/게시판/게시글 관리)

---

### **공격에 대한 대응 로직**

#### **XSS**

- `htmlspecialchars` 함수를 사용하여 사용자 입력값의 입력과 출력을 이스케이프 처리하여 **XSS** 공격에 대한 대응 로직 구현
- `httpOnly` 플래그 설정을 통해 Javascript를 통해 세션 ID가 저장된 쿠키 값에 접근하지 못하도록 하여 **Cookie Replay Attack**에 대한 대응 로직 구현

```php
<?php
session_set_cookie_params([
    'httponly' => true, 
]);
?>
```

#### **SQL Injection**

- **Prepared Statements**를 사용하여 SQL 쿼리와 사용자 입력 값을 분리하여 **SQL Injection** 공격에 대한 대응 로직 구현

```php
$stmt = $pdo->prepare("INSERT INTO posts (user_id, board_id, title, content) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $board_id, $title, $content]);
```

#### **CSRF Attack**

- `CSRF Token`을 사용하여 **CSRF** 공격에 대한 대응 로직 구현

```php
// CSRF Token 생성
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// form 태그 내 CSRF Token 추가
<?php echo '<input type="hidden" name="_csrf" value="' . $_SESSION['csrf_token'] . '">'; ?>

// CSRF Token 검증 진행
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if($_SESSION['csrf_token'] != $_POST['_csrf']) {
        echo "<script>alert('잘못된 접근입니다.'); history.back();</script>";
    }
	// ...파일 업로드 검증 로직...
    // 게시글 작성 성공 시 CSRF Token 갱신
    echo "<script>alert('게시글이 성공적으로 작성되었습니다.'); window.location.href='index.php';</script>";
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
```

#### **File Upload Vulnerability**

- 파일 업로드 시, 파일 이름 **난수화**, **화이트리스트 기반**의 파일 **확장자**, **MIME type**의 검증 및 **.htaccess** 설정을 통해 **php** 파일이 **실행** 되지 않도록 하여 **파일 업로드 취약점**에 대한 대응 로직 구현

```php
$upload_success = true;
    if (isset($_FILES['uploaded_file']) && $_FILES['uploaded_file']['error'] == 0) {
        // 화이트리스트 기반 파일 확장자, MIME type 검증
        $allowed_extensions = ['png', 'jpg', 'pdf', 'xlsx'];
        $allowed_mime_types = ['image/png', 'image/jpeg', 'application/pdf', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];

        // 파일 정보 추출
        $file_extension = pathinfo($_FILES['uploaded_file']['name'], PATHINFO_EXTENSION);
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $_FILES['uploaded_file']['tmp_name']);

        if (in_array($file_extension, $allowed_extensions) && in_array($mime_type, $allowed_mime_types)) {
            $upload_dir = 'uploads/';
            $file_name =  uniqid() . '.' . $file_extension; // 파일 이름 난수화
            $file_path = $upload_dir . $file_name;

            if (!move_uploaded_file($_FILES['uploaded_file']['tmp_name'], $file_path)) {
                $upload_success = false;
                echo "<script>alert('파일 업로드 중 오류가 발생했습니다.'); history.back();</script>";
                exit();
            }
        } else {
            $upload_success = false;
            echo "<script>alert('허용되지 않은 파일 형식입니다.'); history.back();</script>";
            exit();
        }
    }
```

```bash
# .htaccess
<FilesMatch "\.(php|phtml|php3|php4|php5)$">
    Deny from all
</FilesMatch>
```

#### **Path Traversal Vulnerability**

- 파일 다운로드 요청 시, 이름이 아닌 **파일 ID**와 **게시글 ID**를 기반으로 **DB** 내에 해당 파일이 존재하는지 확인하고, 해당 파일의 경로를 검증할 때, `/var/www/html/` 과 같이 `base` 디렉토리를 지정하여 해당 디렉토리로 시작하지 않을 경우 파일 다운로드가 불가능하도록 로직 설정. 또한 `realpath()` 함수를 사용하여, **파일 정규화**를 통해 `../../../` 와 같은 **상대 경로를 절대 경로로 치환**하여, 파일 다운로드 취약점에 대응

```php
if (isset($_GET['post_id']) && isset($_GET['file_id'])) {
    $post_id = (int) $_GET['post_id'];   
    $file_id = (int) $_GET['file_id']; 

    // post_id와 file_id가 일치하는 파일
    $stmt = $pdo->prepare("SELECT * FROM uploads WHERE post_id = ? AND id = ?");
    $stmt->execute([$post_id, $file_id]);
    $file = $stmt->fetch();

    if ($file) {
        // 파일 경로 설정
		$base_dir = 'C:/xampp/htdocs/community_site/';
		$combined_path = $base_dir . $file['file_path'];
		
		// 파일 정규화. 즉 상대 경로를 절대 경로로 치환
        $file_path = realpath($base_dir . $file['file_path']);

        // 파일 존재 유무 및 경로 검증
        if (file_exists($combined_path) && strpos($file_path, $base_dir) === 0 && file_exists($file_path)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
            header('Content-Length: ' . filesize($file_path));

            readfile($file_path);
            exit;
        } else {
            header("HTTP/1.0 404 Not Found");
			include('./errors/404.php');
            exit;
        }
    } else {
        echo "<script>alert('파일을 찾을 수 없습니다.'); window.location.href='post.php?id=$post_id';</script>";
        exit;
    }
} else {
    header("HTTP/1.0 404 Not Found");
    include('./errors/404.php');
    exit;
}
```


#### **Authentication & Access Contorl**

- 사용자에게 **ROLE(user/admin)** 을 부여하여 `ROLE === "admin"` 인 사용자만이 `/admin/` 경로에 해당하는 관리자 페이지에 접근이 가능하도록 로직 구현
- 만약 로그인이 되어 있지 않거나, `ROLE != "admin"` 인 사용자가 `/admin/` 경로에 접근을 시도할 때는 `error.php`라는 커스텀 에러 페이지를 보여주도록 로직 구현

```php
<?php
// 로그인 및 관리자 여부 확인
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("HTTP/1.0 404 Not Found");
    include('../errors/404.php');
    exit();
}
?>
```

- 게시글과 댓글에 대해 **작성자 본인**과 **관리자** 만이 **삭제**가 가능하도록 서버 측 로직 구현

```php
// 게시글 - 작성자 또는 관리자 여부 확인
if ($post['user_id'] != $_SESSION['user_id'] && $_SESSION['role'] !== 'admin') {
    echo "<script>alert('게시글을 삭제할 권한이 없습니다.'); history.back();</script>";
    exit();
}

// 댓글 - 작성자 또는 관리자 여부 확인
if ($comment['user_id'] != $_SESSION['user_id'] && $_SESSION['role'] !== 'admin') {
    echo "<script>alert('댓글을 삭제할 권한이 없습니다.'); window.location.href='post.php?id=$post_id';</script>";
    exit();
}
```

- 글 **작성자 본인**과 **관리**자 만이 해당 글 **수정**이 가능하도록 서버 측 로직 구현

```php
// 게시글 수정
if ($post['user_id'] != $_SESSION['user_id'] && $_SESSION['role'] !== 'admin') {
    echo "<script>alert('게시글을 수정할 권한이 없습니다.'); history.back();</script>";
    exit();
}
```

- 웹 서버 설정 파일 수정

```php
// /etc/apache2/apach2.conf
// 파일 다운로드 제한
<FilesMatch "\.(sql|ini|conf|log|sh)$">
        Order allow,deny
        Deny from all
</FilesMatch>

// HTTP Response 메시지 중, Server 헤더 내 버전 정보 삭제
ServerTokens Prod

// 에러페이지 하단 버전 정보 삭제
ServerSignature Off

// /usr/local/etc/php/php.ini
// X-Powered-By 헤더 내 버전 정보 삭제
expose_php = Off
```