<?php
session_start();
if (!isset($_SESSION['username'])) {
    $_SESSION['error'] = "You must log in first.";
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<?php include_once "header.php"; ?>
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
<body>
    <div class="header">
        <h1>Upload Images</h1>
        <nav>
            <a href="dashboard">Account</a>
            <a href="display">Display</a>
            <a href="upload_page">Upload</a>
        </nav>
    </div>
    <div class="container">
        <h2>Drag and Drop Image Upload</h2>
        <div class="drop-zone" id="drop-zone">
            Drag and drop images here or click to upload
        </div>
        <form id="upload-form" action="upload.php" method="POST" enctype="multipart/form-data">
            <input type="file" id="file-input" name="images[]" accept="image/*" multiple style="display: none" />
        </form>
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const dropZone = document.getElementById("drop-zone");
            const fileInput = document.getElementById("file-input");
            const uploadForm = document.getElementById("upload-form");

            dropZone.addEventListener("dragover", (e) => {
                e.preventDefault();
                dropZone.classList.add("dragover");
            });

            dropZone.addEventListener("dragleave", () => {
                dropZone.classList.remove("dragover");
            });

            dropZone.addEventListener("drop", (e) => {
                e.preventDefault();
                dropZone.classList.remove("dragover");

                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    fileInput.files = files;
                    uploadForm.submit();
                }
            });

            dropZone.addEventListener("click", () => {
                fileInput.click();
            });

            fileInput.addEventListener("change", () => {
                if (fileInput.files.length > 0) {
                    uploadForm.submit();
                }
            });
        });
    </script>
</body>
</html>
