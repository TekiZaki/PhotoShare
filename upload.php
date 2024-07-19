<?php
session_start();
require 'config.php';

if (!isset($_SESSION['username'])) {
    $_SESSION['error'] = "You must log in first.";
    header("Location: login.php");
    exit();
}

function resizeImage($source, $destination, $max_width, $max_height, $quality = 80) {
    list($orig_width, $orig_height, $file_type) = getimagesize($source);

    // Skip resizing for GIF images
    if ($file_type == IMAGETYPE_GIF) {
        return move_uploaded_file($source, $destination);
    }

    // Calculate aspect ratio
    $aspect_ratio = $orig_width / $orig_height;

    // Determine new dimensions while maintaining aspect ratio
    if ($max_width / $max_height > $aspect_ratio) {
        $new_width = $max_height * $aspect_ratio;
        $new_height = $max_height;
    } else {
        $new_width = $max_width;
        $new_height = $max_width / $aspect_ratio;
    }

    // Create new image resource based on file type
    switch ($file_type) {
        case IMAGETYPE_JPEG:
            $image = imagecreatefromjpeg($source);
            break;
        case IMAGETYPE_PNG:
            $image = imagecreatefrompng($source);
            imagealphablending($image, false);
            imagesavealpha($image, true);
            break;
        default:
            return false; // Invalid image type
    }

    // Create a new true color image with desired dimensions
    $new_image = imagecreatetruecolor($new_width, $new_height);

    // Preserve transparency for PNG images
    if ($file_type === IMAGETYPE_PNG) {
        imagealphablending($new_image, false);
        imagesavealpha($new_image, true);
        $transparent = imagecolorallocatealpha($new_image, 0, 0, 0, 127);
        imagefill($new_image, 0, 0, $transparent);
    }

    // Resize and save the image based on its type
    if (!imagecopyresampled($new_image, $image, 0, 0, 0, 0, $new_width, $new_height, $orig_width, $orig_height)) {
        imagedestroy($image);
        imagedestroy($new_image);
        return false;
    }

    switch ($file_type) {
        case IMAGETYPE_JPEG:
            if (!imagejpeg($new_image, $destination, $quality)) {
                imagedestroy($image);
                imagedestroy($new_image);
                return false;
            }
            break;
        case IMAGETYPE_PNG:
            if (!imagepng($new_image, $destination)) {
                imagedestroy($image);
                imagedestroy($new_image);
                return false;
            }
            break;
        default:
            imagedestroy($image);
            imagedestroy($new_image);
            return false; // Invalid image type
    }

    imagedestroy($image);
    imagedestroy($new_image);

    return true;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_FILES['images'])) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $uploadDir = 'images/';

        foreach ($_FILES['images']['tmp_name'] as $key => $tmpName) {
            if ($_FILES['images']['error'][$key] == 0) {
                $fileInfo = pathinfo($_FILES['images']['name'][$key]);
                $fileExt = strtolower($fileInfo['extension']);

                if (in_array($fileExt, $allowed)) {
                    $uploadFile = $uploadDir . uniqid() . '.' . $fileExt; // Unique name to prevent overwriting

                    if ($fileExt == 'gif' || resizeImage($tmpName, $uploadFile, 800, 600)) { // Resize to fit within 800x600
                        if ($fileExt == 'gif') {
                            // Move GIF files without resizing
                            move_uploaded_file($tmpName, $uploadFile);
                        }

                        $stmt = $conn->prepare("INSERT INTO images (user_id, image_path) VALUES (?, ?)");
                        $stmt->bind_param("is", $_SESSION['user_id'], $uploadFile);
                        if ($stmt->execute()) {
                            $stmt->close();
                        } else {
                            echo "Failed to upload image.";
                            exit();
                        }
                    } else {
                        echo "Failed to resize image.";
                        exit();
                    }
                } else {
                    echo "Invalid file type.";
                    exit();
                }
            } else {
                echo "Error uploading file.";
                exit();
            }
        }
        header("Location: display");
        exit();
    } else {
        echo "No files uploaded or upload error.";
    }
}
?>
