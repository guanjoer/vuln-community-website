<?php
if (isset($_GET['id'])) {
    $post_id = $_GET['id'];
    $result = $pdo->query("SELECT * FROM posts WHERE id = '$post_id'");
    $post = $result->fetch();

    $result = $pdo->query("SELECT * FROM uploads WHERE post_id = '$post_id'");
    $file = $result->fetch();
} 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $content = $_POST['content'];

    if ($upload_success) {
        $stmt = $pdo->exec("UPDATE posts SET title = '$title', content = '$content' WHERE id = '$post_id'");
	}
}
?>

<form method="post" action="edit_post.php?id=<?php echo $post_id; ?>" enctype="multipart/form-data">
	<label for="title">TITLE</label>
	<input type="text" id="title" name="title" value="<?php echo $post['title']; ?>" required>

	<label for="content">CONTENT</label>
	<textarea id="content" name="content" rows="10" required><?php echo $post['content']; ?></textarea>

	<div id="upload-file">
		<label for="uploaded_file">UPLOADED FILE</label>
		<?php if ($file): ?>
			<p><a href="<?php echo $file['file_path']; ?>" download><?php echo $file['file_name']; ?></a></p>
			<input type="file" id="uploaded_file" name="uploaded_file">
		<?php else: ?>
			<input type="file" id="uploaded_file" name="uploaded_file">
	</div>
	<?php endif; ?>
	
	<button type="submit">UPDATE</button>
</form>

<a id="back-btn" href="post.php?id=<?php echo $post_id; ?>">BACK</a>