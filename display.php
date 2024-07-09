<?php
session_start();
require 'config.php';

if (!isset($_SESSION['username'])) {
    $_SESSION['error'] = "You must log in first.";
    header("Location: login.php");
    exit();
}

$sql = "SELECT u.username, i.id, i.image_path FROM images i JOIN users u ON i.user_id = u.id ORDER BY i.upload_time DESC";
$result = $conn->query($sql);

$images = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $images[] = $row;
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Public Gallery</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
    <style>
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
        .username-display {
            position: absolute;
            bottom: 10px;
            left: 10px;
            background-color: rgba(0, 0, 0, 0.5);
            color: white;
            padding: 5px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Public Gallery</h1>
        <nav>
            <a href="dashboard.php">Account</a>
            <a href="display.php">Display</a>
            <a href="upload_page.php">Upload</a>
        </nav>
    </div>
    <div class="container">
      <h2>Image List</h2>
        <div class="image-container" id="gallery">
            <?php foreach ($images as $image): ?>
                <div class="image">
                    <a href="image_detail.php?id=<?php echo htmlspecialchars($image['id']); ?>">
                        <img src="<?php echo htmlspecialchars($image['image_path']); ?>" alt="Image">
                    </a>
                    <div class="username-display">
                        Uploaded by: <?php echo htmlspecialchars($image['username']); ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>