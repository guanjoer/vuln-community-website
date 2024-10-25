<!-- 사이드바: 프로필 및 게시판 목록 -->
<aside id="sidebar">
	<div class="profile-info">
		<div class="profile-info-2">
			<?php if (isset($_SESSION['user_id'])): ?>
				<img id="profile-preview" src="uploads/<?php echo !empty($user['profile_image']) ? htmlspecialchars($user['profile_image']) : 'default.png'; ?>" alt="프로필 이미지">
				<span id="username">어서오세요<br><strong><?php echo htmlspecialchars($user['username']); ?></strong> 님</span>
		</div>
		<div class="btn login sidebar-btn logout-btn">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="logout_process.php?logout=true">로그아웃</a>
            <?php endif; ?>
		</div>
			<div class="btn login sidebar-btn" id="logout-btn">
				<?php if ($_SESSION['role'] == 'admin'): ?>
					<a href="admin/dashboard.php">관리자 대시보드</a>
				<?php endif; ?>
				<a href="write_post.php">글쓰기</a>
				<a href="mypage.php">마이페이지</a>
			</div>
<?php else: ?>
	<div class="btn login">
		<a href="login.php">로그인</a>
		<a href="signup.php">회원가입</a>
	</div>
<?php endif; ?>
	</div>
	<h2>게시판 목록</h2>
	<ul>
			<li><a href="board.php">📃 ALL POSTS</a></li>
		<?php foreach ($all_boards as $each_board): ?>
			<li><a href="board.php?id=<?php echo $each_board['id']; ?>">📃 <?php echo htmlspecialchars($each_board['name']); ?></a></li>
		<?php endforeach; ?>
	</ul>
</aside>