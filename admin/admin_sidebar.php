<!-- 사이드바: 프로필 및 게시판 목록 -->
<aside id="sidebar">
	<div class="profile-info">
		<div class="profile-info-2">
			<?php if (isset($_SESSION['user_id'])): ?>
				<img id="profile-preview" src="../uploads/<?php echo !empty($user['profile_image']) ? htmlspecialchars($user['profile_image']) : 'default.png'; ?>" alt="프로필 이미지">
				<span id="username">어서오세요<br><strong><?php echo htmlspecialchars($user['username']); ?></strong>님</span>
		</div>
		<div class="btn login sidebar-btn logout-btn">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="logout_process.php?logout=true">로그아웃</a>
            <?php endif; ?>
		</div>
			<div class="btn login sidebar-btn" id="logout-btn">
				<?php if ($_SESSION['role'] == 'admin'): ?>
					<a href="dashboard.php">Dashboard</a><br>
				<?php endif; ?>
					<a href="users.php">Users</a>
					<a href="boards.php">Boards</a>
					<a href="posts.php">Posts</a>
			</div>
			<!-- <div>
				<a href="../mypage.php">Profile</a>
			</div> -->
<?php else: ?>
	<div class="btn login">
		<a href="login.php">로그인</a>
		<a href="signup.php">회원가입</a>
	</div>
<?php endif; ?>
	</div>
</aside>