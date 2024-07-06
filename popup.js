document.addEventListener("DOMContentLoaded", loadImages);

async function loadImages() {
  try {
    const response = await fetch("get_images.php");
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }
    const result = await response.json();
    if (result.success) {
      result.images.forEach(displayImage);
    } else {
      console.error("Failed to load images");
    }
  } catch (error) {
    console.error("Error fetching images: ", error);
  }
}

function displayImage(image) {
  const gallery = document.getElementById("gallery");
  const imageContainer = document.createElement("div");
  imageContainer.classList.add("image");

  const img = document.createElement("img");
  img.src = image.image_path;

  img.addEventListener("click", () => {
    const popup = document.querySelector(".popup-image");
    popup.style.display = "flex";
    const popupImg = popup.querySelector("img");
    popupImg.src = img.getAttribute("src");
  });

  if (image.user_id == getUserId()) {
    const deleteButton = document.createElement("button");
    deleteButton.classList.add("delete-button");
    deleteButton.textContent = "Delete";
    deleteButton.addEventListener("click", async (event) => {
      event.stopPropagation(); // Prevent the click from triggering the image zoom
      await deleteImage(image.id, image.image_path, imageContainer);
    });
    imageContainer.appendChild(deleteButton);
  }

  imageContainer.appendChild(img);
  gallery.appendChild(imageContainer);
}

async function deleteImage(imageId, imagePath, imageContainer) {
  try {
    const response = await fetch("delete_image.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: `image_id=${imageId}`,
    });

    const result = await response.text();
    if (result.includes("Image deleted successfully.")) {
      // Remove the image container from the DOM
      imageContainer.remove();
      showMessage("Image deleted successfully.");
    } else {
      console.error(result);
    }
  } catch (error) {
    console.error("Error deleting image: ", error);
  }
}

function showMessage(message) {
  const messageBox = document.getElementById("message-box");
  messageBox.textContent = message;
  messageBox.style.display = "block";
  setTimeout(() => {
    messageBox.style.display = "none";
  }, 5000);
}

function getUserId() {
  // Assuming user_id is stored in a global variable or data attribute
  return document.body.dataset.userId;
}

document.querySelector(".popup-image span").onclick = () => {
  document.querySelector(".popup-image").style.display = "none";
};
