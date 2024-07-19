<?php
session_start();
require 'config.php';

if (!isset($_SESSION['username'])) {
    $_SESSION['error'] = "You must log in first.";
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: display.php");
    exit();
}

$image_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Fetch image details
$stmt = $conn->prepare("SELECT i.*, u.username, 
    (SELECT COUNT(*) FROM likes WHERE image_id = i.id) AS like_count,
    (SELECT COUNT(*) FROM comments WHERE image_id = i.id) AS comment_count,
    (SELECT COUNT(*) FROM likes WHERE image_id = i.id AND user_id = ?) AS user_liked
    FROM images i 
    JOIN users u ON i.user_id = u.id 
    WHERE i.id = ?");
$stmt->bind_param("ii", $user_id, $image_id);
$stmt->execute();
$result = $stmt->get_result();
$image = $result->fetch_assoc();
$stmt->close();

if (!$image) {
    header("Location: display.php");
    exit();
}

// Fetch comments
$stmt = $conn->prepare("SELECT c.*, u.username FROM comments c JOIN users u ON c.user_id = u.id WHERE c.image_id = ? ORDER BY c.created_at DESC");
$stmt->bind_param("i", $image_id);
$stmt->execute();
$comments_result = $stmt->get_result();
$comments = $comments_result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$conn->close();
?>

<?php include_once "header.php"; ?>
    <style>
        .image-container {
            max-width: 800px;
            margin: 0 auto;
        }
        .image-container img {
            width: 100%;
            height: auto;
        }
        .like-button {
            background-color: <?php echo $image['user_liked'] ? 'red' : 'grey'; ?>;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
        }
        .comment-form textarea {
            width: 100%;
            height: 100px;
        }
        .comment {
            border: 1px solid #ddd;
            padding: 10px;
            margin-bottom: 10px;
        }
    </style>
<body>
    <div class="header">
        <h1>Image Detail</h1>
        <nav>
            <a href="dashboard.php">Account</a>
            <a href="display.php">Display</a>
            <a href="upload_page.php">Upload</a>
        </nav>
    </div>
    <div class="container">
        <div class="image-container">
            <img src="<?php echo htmlspecialchars($image['image_path']); ?>" alt="Image">
            <p>Uploaded by: <?php echo htmlspecialchars($image['username']); ?></p>
            <p>Likes: <span id="like-count"><?php echo $image['like_count']; ?></span></p>
            <button class="like-button" onclick="toggleLike(<?php echo $image_id; ?>)"><?php echo $image['user_liked'] ? 'Unlike' : 'Like'; ?></button>
            
            <h3>Comments (<?php echo $image['comment_count']; ?>)</h3>
            <form class="comment-form" onsubmit="postComment(event, <?php echo $image_id; ?>)">
                <textarea name="comment" required></textarea>
                <button type="submit">Post Comment</button>
            </form>
            
            <div id="comments-container">
                <?php foreach ($comments as $comment): ?>
                    <div class="comment">
                        <p><strong><?php echo htmlspecialchars($comment['username']); ?>:</strong> <?php echo htmlspecialchars($comment['content']); ?></p>
                        <small><?php echo $comment['created_at']; ?></small>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <script>
    function toggleLike(imageId) {
        fetch('like.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'image_id=' + imageId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const likeButton = document.querySelector('.like-button');
                const likeCount = document.getElementById('like-count');
                likeCount.textContent = data.likes;
                likeButton.textContent = data.liked ? 'Unlike' : 'Like';
                likeButton.style.backgroundColor = data.liked ? 'red' : 'grey';
            }
        });
    }

    function postComment(event, imageId) {
        event.preventDefault();
        const form = event.target;
        const comment = form.comment.value;

        fetch('comment.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'image_id=' + imageId + '&comment=' + encodeURIComponent(comment)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const commentsContainer = document.getElementById('comments-container');
                commentsContainer.innerHTML = `
                    <div class="comment">
                        <p><strong>${data.username}:</strong> ${data.comment}</p>
                        <small>${data.created_at}</small>
                    </div>
                ` + commentsContainer.innerHTML;
                form.reset();
            }
        });
    }
    </script>
</body>
</html>
