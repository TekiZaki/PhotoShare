<?php
session_start();
require 'config.php';

if (!isset($_SESSION['username'])) {
    $_SESSION['error'] = "You must log in first.";
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $fileInfo = pathinfo($_FILES['image']['name']);
        $fileExt = strtolower($fileInfo['extension']);

        if (in_array($fileExt, $allowed)) {
            $uploadDir = 'images/';
            $uploadFile = $uploadDir . uniqid() . '.' . $fileExt; // Unique name to prevent overwriting

            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
                $stmt = $conn->prepare("INSERT INTO images (user_id, image_path) VALUES (?, ?)");
                $stmt->bind_param("is", $_SESSION['user_id'], $uploadFile);
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
