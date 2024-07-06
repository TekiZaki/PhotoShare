<?php
session_start();
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $fileInfo = pathinfo($_FILES['image']['name']);
        $fileExt = strtolower($fileInfo['extension']);

        if (in_array($fileExt, $allowed)) {
            $uploadDir = 'images/';
            $uploadFile = $uploadDir . basename($_FILES['image']['name']);

            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
                $userId = $_SESSION['user_id']; // Get user_id from session
                $stmt = $conn->prepare("INSERT INTO images (user_id, image_path, upload_time) VALUES (?, ?, NOW())");
                $stmt->bind_param("is", $userId, $uploadFile);
                $stmt->execute();
                $stmt->close();
                header("Location: display.php");
                exit();
            } else {
                echo "Failed to upload image.";
            }
        } else {
            echo "Invalid file type.";
        }
    } else {
        echo "No file uploaded or upload error.";
    }
}
?>
