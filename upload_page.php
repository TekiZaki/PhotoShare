<?php
session_start();
if (!isset($_SESSION['username'])) {
    $_SESSION['error'] = "You must log in first.";
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Upload Image</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
    <style>
        .drop-zone {
            width: 100%;
            max-width: 500px;
            height: 200px;
            border: 2px dashed #ccc;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            color: #aaa;
            margin: 0 auto;
            cursor: pointer;
        }

        .drop-zone.dragover {
            border-color: #333;
            color: #333;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Upload Image</h1>
        <nav>
            <a href="dashboard.php">Account</a>
            <a href="display.php">Display</a>
            <a href="upload_page.php">Upload</a>
        </nav>
    </div>
    <div class="container">
        <h2>Drag and Drop Image Upload</h2>
        <div class="drop-zone" id="drop-zone">
            Drag and drop an image here or click to upload
        </div>
        <form id="upload-form" action="upload.php" method="POST" enctype="multipart/form-data" style="display: none;">
            <input type="file" id="file-input" name="image" accept="image/*">
            <button type="submit">Upload</button>
        </form>
    </div>
    <script src="upload.js"></script>
</body>
</html>
