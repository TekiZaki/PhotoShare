<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id']) || !isset($_POST['image_id'])) {
    echo json_encode(['success' => false]);
    exit();
}

$user_id = $_SESSION['user_id'];
$image_id = $_POST['image_id'];

$stmt = $conn->prepare("SELECT * FROM likes WHERE user_id = ? AND image_id = ?");
$stmt->bind_param("ii", $user_id, $image_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // User has already liked, so unlike
    $stmt = $conn->prepare("DELETE FROM likes WHERE user_id = ? AND image_id = ?");
    $stmt->bind_param("ii", $user_id, $image_id);
    $stmt->execute();
    $liked = false;
} else {
    // User hasn't liked, so add like
    $stmt = $conn->prepare("INSERT INTO likes (user_id, image_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $user_id, $image_id);
    $stmt->execute();
    $liked = true;
}

// Get updated like count
$stmt = $conn->prepare("SELECT COUNT(*) AS like_count FROM likes WHERE image_id = ?");
$stmt->bind_param("i", $image_id);
$stmt->execute();
$result = $stmt->get_result();
$like_count = $result->fetch_assoc()['like_count'];

$conn->close();

echo json_encode(['success' => true, 'liked' => $liked, 'likes' => $like_count]);