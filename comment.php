<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id']) || !isset($_POST['image_id']) || !isset($_POST['comment'])) {
    echo json_encode(['success' => false]);
    exit();
}

$user_id = $_SESSION['user_id'];
$image_id = $_POST['image_id'];
$comment = $_POST['comment'];

$stmt = $conn->prepare("INSERT INTO comments (user_id, image_id, content) VALUES (?, ?, ?)");
$stmt->bind_param("iis", $user_id, $image_id, $comment);
$stmt->execute();

$comment_id = $stmt->insert_id;

$stmt = $conn->prepare("SELECT c.*, u.username FROM comments c JOIN users u ON c.user_id = u.id WHERE c.id = ?");
$stmt->bind_param("i", $comment_id);
$stmt->execute();
$result = $stmt->get_result();
$comment_data = $result->fetch_assoc();

$conn->close();

echo json_encode([
    'success' => true,
    'username' => $comment_data['username'],
    'comment' => $comment_data['content'],
    'created_at' => $comment_data['created_at']
]);
