function redirectToAccountSettings() {
  // Redirect to account settings page
  window.location.href = "account-settings.php"; 
}


// Retrieve the image source from the <img> element inside the <td>
const imageElement = document.getElementById('profileImage');
if (imageElement) {
    const imagePath = imageElement.src; // Get the src attribute of the image
    document.getElementById('edit_image_preview').src = imagePath; // Set the preview image in the modal
} else {
    console.error('Image not found in the profile.');
}

// Handle file upload (if a new image is uploaded)
const fileInput = document.getElementById('imageUploadInput'); // Assuming you have an input element for file upload

fileInput.addEventListener('change', function (event) {
    const file = event.target.files[0];

    if (file) {
        const formData = new FormData();
        formData.append('image', file);

        // Send the image to the server using AJAX
        fetch('upload_profile_image.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // Update the profile image in the header
                imageElement.src = data.newImagePath;
                alert('Profile image updated successfully.');
            } else {
                alert('Failed to upload image.');
            }
        })
        .catch(error => {
            console.error('Error uploading image:', error);
        });
    }
});