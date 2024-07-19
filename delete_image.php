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

    // Start a transaction
    $conn->begin_transaction();

    try {
        // Delete associated likes
        $stmt = $conn->prepare("DELETE FROM likes WHERE image_id = ?");
        $stmt->bind_param("i", $image_id);
        $stmt->execute();
        $stmt->close();

        // Delete associated comments
        $stmt = $conn->prepare("DELETE FROM comments WHERE image_id = ?");
        $stmt->bind_param("i", $image_id);
        $stmt->execute();
        $stmt->close();

        // Delete the image record from the database
        $stmt = $conn->prepare("DELETE FROM images WHERE id = ?");
        $stmt->bind_param("i", $image_id);
        $stmt->execute();
        $stmt->close();

        // If all queries were successful, commit the transaction
        $conn->commit();

        // Delete the image file from the server
        if (file_exists($image_path)) {
            unlink($image_path);
        }

        echo "Image and associated data deleted successfully.";
    } catch (Exception $e) {
        // If an error occurred, roll back the transaction
        $conn->rollback();
        echo "Error deleting image and associated data: " . $e->getMessage();
    }
} else {
    echo "Invalid request.";
}

$conn->close();
?>
