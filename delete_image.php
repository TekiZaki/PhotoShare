<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    echo "You must be logged in to delete an image.";
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['image_id']) && isset($_POST['image_path'])) {
    $image_id = $_POST['image_id'];
    $image_path = $_POST['image_path'];

    // Check if the user is the owner of the image
    $stmt = $conn->prepare("SELECT user_id FROM images WHERE id = ?");
    $stmt->bind_param("i", $image_id);
    $stmt->execute();
    $stmt->bind_result($owner_id);
    $stmt->fetch();
    $stmt->close();

    if ($owner_id != $user_id) {
        echo "You are not authorized to delete this image.";
        exit();
    }

    // Delete the image record from the database
    $stmt = $conn->prepare("DELETE FROM images WHERE id = ?");
    $stmt->bind_param("i", $image_id);
    if ($stmt->execute()) {
        $stmt->close();
        // Delete the image file from the server
        if (file_exists($image_path)) {
            unlink($image_path);
        }
        echo "Image deleted successfully.";
    } else {
        echo "Error deleting image from the database.";
    }
} else {
    echo "Invalid request.";
}
?>
