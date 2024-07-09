<?php
session_start();
require 'config.php';

if (!isset($_SESSION['username'])) {
    $_SESSION['error'] = "Anda harus login terlebih dahulu.";
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
$user_id = $_SESSION['user_id'];

// Fetch images uploaded by the user
$stmt = $conn->prepare("SELECT id, image_path FROM images WHERE user_id = ? ORDER BY upload_time DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$images = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Dashboard</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
    <style>
      .username-display {
        font-family: "Courier New", Courier, monospace;
        font-size: 1.5em;
        color: #ff5733;
        background-color: #f0f0f0;
        padding: 5px 10px;
        border-radius: 5px;
        border: 2px solid #ff5733;
      }
      .image-container {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        justify-content: center;
      }
      .image {
        position: relative;
        height: 250px;
        width: 100%;
        max-width: 350px;
        overflow: hidden;
        border-radius: 8px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        cursor: pointer;
        transition: transform 0.3s;
      }
      .image img {
        height: 100%;
        width: 100%;
        object-fit: contain;
        transition: transform 0.3s;
      }
      .image:hover img {
        transform: scale(1.1);
      }
      .delete-button {
        position: absolute;
        top: 10px;
        right: 10px;
        background-color: red;
        color: white;
        border: none;
        border-radius: 5px;
        padding: 5px 10px;
        cursor: pointer;
        z-index: 1;
      }
      .message-box {
        background-color: #4CAF50;
        color: white;
        padding: 10px;
        margin-bottom: 20px;
        display: none;
      }
    </style>
</head>
<body>
    <div class="header">
      <h1>Account</h1>
      <nav>
        <a href="dashboard.php">Account</a>
        <a href="display.php">Display</a>
        <a href="upload_page.php">Upload</a>
      </nav>
    </div>
    <div class="secondnav">
        <a href="logout.php">Logout</a>
    </div>
    <div class="container">
    <h2>Selamat Datang, <?php echo '<span class="username-display">' . htmlspecialchars($_SESSION['username']) . '</span>'; ?></h2>
    <p>Ini adalah halaman dashboard.</p>
    <div class="message-box" id="message-box"></div>
    <div class="image-container" id="gallery">
        <?php foreach ($images as $image): ?>
            <div class="image">
                <a href="image_detail.php?id=<?php echo htmlspecialchars($image['id']); ?>">
                    <img src="<?php echo htmlspecialchars($image['image_path']); ?>" alt="User Image">
                </a>
                <button class="delete-button" onclick="deleteImage(<?php echo $image['id']; ?>, '<?php echo htmlspecialchars($image['image_path']); ?>', this)">Delete</button>
            </div>
        <?php endforeach; ?>
    </div>
</div>
    <script>
    async function deleteImage(imageId, imagePath, deleteButton) {
        if (!confirm('Are you sure you want to delete this image?')) {
            return;
        }

        try {
            const response = await fetch("delete_image.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: `image_id=${imageId}&image_path=${encodeURIComponent(imagePath)}`
            });

            const result = await response.text();
            if (result.includes("Image and associated data deleted successfully.")) {
                // Remove the image container from the DOM
                deleteButton.closest('.image').remove();
                showMessage("Image deleted successfully.");
            } else {
                console.error(result);
                showMessage("Error deleting image. Please try again.");
            }
        } catch (error) {
            console.error("Error deleting image: ", error);
            showMessage("Error deleting image. Please try again.");
        }
    }

    function showMessage(message) {
        const messageBox = document.getElementById("message-box");
        messageBox.textContent = message;
        messageBox.style.display = "block";
        setTimeout(() => {
            messageBox.style.display = "none";
        }, 5000);
    }
</script>
</body>
</html>
