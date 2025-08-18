<?php 
include("../controllers/page_controller.php");
require_once("../repositories/db_connect.php");
$u_id = $_SESSION['user']['id'];


$isAdmin = false;
if ($u_id) {
    $stmtAdmin = $pdo->prepare("CALL IsUserAdmin(:uid)");
    $stmtAdmin->bindParam(':uid', $u_id, PDO::PARAM_INT);
    $stmtAdmin->execute();
    $result = $stmtAdmin->fetch(PDO::FETCH_ASSOC);
    $stmtAdmin->closeCursor();

    $isAdmin = $result && $result['is_admin'] == 1;
}


// Handle new post
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    if (!empty($_POST['topic']) && !empty($_POST['content'])) {
        $stmt = $pdo->prepare("CALL AddPost(:u_id, :topic, :content)");
        $stmt->execute(['u_id' => $u_id, 'topic' => $_POST['topic'], 'content' => $_POST['content']]);
        $stmt->closeCursor();
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $post_id = (int)$_POST['post_id'];
    
    if ($isAdmin) {
        // Admin can delete any post
        $stmt = $pdo->prepare("DELETE FROM posts WHERE post_id = :post_id");
        $stmt->execute(['post_id' => $post_id]);
    } else {
        // User can only delete their own post
        $stmt = $pdo->prepare("DELETE FROM posts WHERE post_id = :post_id AND u_id = :u_id");
        $stmt->execute(['post_id' => $post_id, 'u_id' => $u_id]);
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Handle edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $post_id = (int)$_POST['post_id'];
    $stmt = $pdo->prepare("UPDATE posts SET topic = :topic, content = :content WHERE post_id = :post_id AND u_id = :u_id");
    $stmt->execute([
        'topic' => $_POST['topic'],
        'content' => $_POST['content'],
        'post_id' => $post_id,
        'u_id' => $u_id
    ]);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Handle like/dislike
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reaction'])) {
    $post_id = (int)$_POST['post_id'];
    $is_like = ($_POST['reaction'] === 'like') ? 1 : 0;

    $stmt = $pdo->prepare("CALL TogglePostLike(:post_id, :u_id, :is_like)");
    $stmt->execute(['post_id' => $post_id, 'u_id' => $u_id, 'is_like' => $is_like]);
    $stmt->closeCursor();

    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Handle new comment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'comment') {
    $post_id = (int)$_POST['post_id'];
    if (!empty($_POST['comment_text'])) {
        $stmt = $pdo->prepare("CALL AddComment(:post_id, :u_id, :comment_text)");
        $stmt->execute([
            'post_id' => $post_id,
            'u_id' => $u_id,
            'comment_text' => $_POST['comment_text']
        ]);
        $stmt->closeCursor();
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Fetch posts with user reaction
$stmt = $pdo->prepare("CALL GetPostsFull(:u_id)");
$stmt->execute(['u_id' => $u_id]);
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
$stmt->closeCursor();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Community Posts</title>
    <link rel="stylesheet" href="../styles/styles/community.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@900&display=swap" rel="stylesheet"/>
</head>
<body>

<header>
    <div class="navbar">
        <div id="back_button" class="nav-button">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Back</span>
        </div>
    </div>
</header>

<main class="community-container">
    <h1 class="page-title">Community Posts</h1>

    <!-- New post form -->
    <div class="new-post">
        <h2>Create New Post</h2>
        <form method="POST">
            <input type="hidden" name="action" value="add">
            <label>Topic:</label>
            <input type="text" name="topic" required>
            <label>Content:</label>
            <textarea name="content" required></textarea>
            <div class="form-actions">
                <button type="submit" class="btn-save">Post</button>
            </div>
        </form>
    </div>

    <?php foreach ($posts as $post): ?>
    <div class="post-container">
        <!-- LEFT SIDE (7 parts) -->
        <div class="post-left">
            <span class="topic"><?= htmlspecialchars($post['topic']) ?></span>
            <span class="username">Posted by: <?= htmlspecialchars($post['u_name']) ?></span>
            <span class="post-time"><?= date("M j, h:i A", strtotime($post['created_at'])) ?></span>
            <div class="content"><?= nl2br(htmlspecialchars($post['content'])) ?></div>

            <div class="buttons">
                <!-- Like -->
                <form method="POST">
                    <input type="hidden" name="post_id" value="<?= $post['post_id'] ?>">
                    <input type="hidden" name="reaction" value="like">
                    <button type="submit" class="like <?= $post['user_reaction'] === '1' ? 'active' : '' ?>">
                        👍 Like (<?= $post['likes'] ?>)
                    </button>
                </form>

                <!-- Dislike -->
                <form method="POST">
                    <input type="hidden" name="post_id" value="<?= $post['post_id'] ?>">
                    <input type="hidden" name="reaction" value="dislike">
                    <button type="submit" class="dislike <?= $post['user_reaction'] === '0' ? 'active' : '' ?>">
                        👎 Dislike (<?= $post['dislikes'] ?>)
                    </button>
                </form>

                <!-- Edit/Delete -->
<?php if ($post['u_id'] == $u_id || $isAdmin): ?>
    <form method="POST" class="delete-form" onsubmit="return confirm('Delete this post?');">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="post_id" value="<?= $post['post_id'] ?>">
        <button type="submit" class="delete">Delete</button>
    </form>
<?php endif; ?>

<?php if ($post['u_id'] == $u_id): ?>
    <button type="button" class="edit" onclick="toggleEdit(<?= $post['post_id'] ?>)">Edit</button>
    <!-- Inline edit form -->
    <form method="POST" class="edit-form" id="edit-form-<?= $post['post_id'] ?>" style="display:none;" onsubmit="return confirm('Save changes to this post?');">
        <input type="hidden" name="action" value="edit">
        <input type="hidden" name="post_id" value="<?= $post['post_id'] ?>">

        <label>Topic:</label>
        <textarea name="topic" required style="width:100%; margin-bottom:5px;"><?= htmlspecialchars($post['topic']) ?></textarea>

        <label>Content:</label>
        <textarea name="content" required style="width:100%; height:70px; margin-bottom:5px;"><?= htmlspecialchars($post['content']) ?></textarea>

        <button type="submit" class="edit">Save</button>
        <button type="button" onclick="toggleEdit(<?= $post['post_id'] ?>)">Cancel</button>
    </form>
<?php endif; ?>

            </div>
        </div>

        <!-- RIGHT SIDE (3 parts) -->
        <div class="post-right">
            <!-- Comment form -->
            <form method="POST" class="comment-form">
                <input type="hidden" name="action" value="comment">
                <input type="hidden" name="post_id" value="<?= $post['post_id'] ?>">
                <input type="text" name="comment_text" placeholder="Write a comment..." required>
                <button type="submit">Comment</button>
            </form>

            <!-- Comments list -->
            <?php
            $cstmt = $pdo->prepare("SELECT c.comment_text, u.u_name, c.created_at
                                    FROM comments c
                                    JOIN users u ON c.u_id = u.u_id
                                    WHERE c.post_id = :post_id
                                    ORDER BY c.created_at DESC");
            $cstmt->execute(['post_id' => $post['post_id']]);
            $comments = $cstmt->fetchAll(PDO::FETCH_ASSOC);
            $cstmt->closeCursor();
            ?>

            <?php if ($comments): ?>
                <div class="comment-list">
                    <?php foreach ($comments as $comment): ?>
                        <div class="comment">
                            <strong><?= htmlspecialchars($comment['u_name']) ?>:</strong>
                            <?= htmlspecialchars($comment['comment_text']) ?>
                            <span class="comment-time">
                                (<?= date("M j, h:i A", strtotime($comment['created_at'])) ?>)
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endforeach; ?>

</main>

<footer class="custom-footer">
    <div class="footer-content">
        <div class="footer-column">
            <h3>Help Centre</h3>
            <p>Mon–Fri 9.00 - 18.00</p>
            <p>Sat 9.00 - 17.30</p>
            <p>Sun & Bank Holidays CLOSED</p>
            <div class="social-icons">
                <i class="fab fa-facebook-f"></i>
                <i class="fab fa-x-twitter"></i>
                <i class="fab fa-instagram"></i>
                <i class="fab fa-youtube"></i>
                <i class="fab fa-tiktok"></i>
            </div>
        </div>
        <div class="footer-column">
            <a href="#">About us</a>
            <a href="#">Contact us</a>
            <a href="#">Authors & experts</a>
            <a href="#">Community news</a>
        </div>
        <div class="footer-column">
            <a href="#">Careers</a>
            <a href="#">Partners</a>
        </div>
    </div>
    <hr>
    <div class="footer-bottom">
        <p>© 2025 Carverly Ltd. All rights reserved</p>
    </div>
</footer>

<script>
function toggleEdit(postId) {
    const form = document.getElementById('edit-form-' + postId);
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
}
document.getElementById("back_button").addEventListener("click", () => {
    window.location.href = "../pages/homepage.php";
});
</script>

</body>
</html>
