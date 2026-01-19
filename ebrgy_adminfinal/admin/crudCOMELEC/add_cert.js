document.getElementById("addRecordForm").addEventListener("submit", function (e) {
    e.preventDefault(); // Prevent the default form submission

    const formData = new FormData(this); // Create FormData object from the form

    fetch("crudCOMELEC/add_cert.php", { // Updated path
        method: "POST",
        body: formData, // Send form data to the server
    })
    .then(response => response.text())
    .then(data => {
        alert(data); // Display success or error message
        location.reload(); // Reload the page to show the new record
    })
    .catch(error => {
        console.error("Error:", error); // Log any errors
        alert("An error occurred while adding the record.");
    });
});
