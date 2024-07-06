<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Upload Image</title>
    <link rel="stylesheet" href="styles.css" />
    <link href="css/roboto.css" rel="stylesheet" />
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
    <div class="form-container">
      <form id="uploadForm" action="upload.php" method="post" enctype="multipart/form-data">
        <input type="file" id="imageInput" name="image" accept="image/*" required />
        <button type="submit" class="button">Upload Image</button>
      </form>
    </div>
  </body>
</html>
