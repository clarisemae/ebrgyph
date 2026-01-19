document.addEventListener("DOMContentLoaded", function () {
    const addAnnouncementForm = document.getElementById("addAnnouncementForm");
    if (addAnnouncementForm) {
        addAnnouncementForm.addEventListener("submit", async function (e) {
            e.preventDefault(); // Prevent default form submission

            const formData = new FormData(this);
            formData.append("add_announcement", true);

            try {
                const response = await fetch("crudAnnouncement/add_announcement.php", {
                    method: "POST",
                    body: formData,
                });

                const text = await response.text();
                console.log("Raw response:", text);

                const result = JSON.parse(text);
                console.log("Parsed response:", result);

                const alertBox = document.createElement("div");
                alertBox.className = `alert ${
                    result.status === "success" ? "alert-success" : "alert-danger"
                }`;
                alertBox.textContent = result.message;

                const modalBody = document.querySelector("#addAnnouncementModal .modal-body");
                modalBody.prepend(alertBox);

                if (result.status === "success") {
                    setTimeout(() => location.reload(), 500);
                }
            } catch (error) {
                console.error("Error adding announcement:", error);
                alert("An unexpected error occurred. Check the console for details.");
            }
        });
    } else {
        console.error("Add Announcement Form not found.");
    }
});
