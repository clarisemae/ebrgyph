// Delete Certificate of Non-Residency record
async function deleteCertificate(certificateId) {
    if (confirm('Are you sure you want to delete this Certificate of Non-Residency record?')) {
        const formData = new FormData();
        formData.append('certificate_id', certificateId);
        formData.append('delete_certificate', true);

        try {
            const response = await fetch("crudNonResidency/delete_cert.php", { // Adjusted path
                method: "POST",
                body: formData,
            });

            const result = await response.text();
            alert(result); // Display the server response
            location.reload(); // Reload the page to reflect the changes
        } catch (error) {
            console.error("Error deleting certificate record:", error);
        }
    }
}
