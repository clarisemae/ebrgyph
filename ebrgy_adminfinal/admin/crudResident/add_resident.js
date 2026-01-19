document.addEventListener("DOMContentLoaded", function () {
    const addResidentForm = document.getElementById("addResidentForm");
    if (addResidentForm) {
        addResidentForm.addEventListener("submit", async function (e) {
            e.preventDefault(); // Prevent default form submission

            const formData = new FormData(this);
            formData.append("add_resident", true);

            try {
                const response = await fetch("crudResident/add_resident.php", {
                    method: "POST",
                    body: formData,
                });

                const text = await response.text();
                console.log("Raw response:", text); // Log raw response for debugging

                const result = JSON.parse(text); // Parse JSON
                console.log("Parsed response:", result);

                const alertBox = document.createElement("div");
                alertBox.className = `alert ${
                    result.status === "success" ? "alert-success" : "alert-danger"
                }`;
                alertBox.textContent = result.message;

                const modalBody = document.querySelector("#addResidentModal .modal-body");
                modalBody.prepend(alertBox);

                if (result.status === "success") {
                    setTimeout(() => location.reload(), 2000);
                }
            } catch (error) {
                console.error("Error adding resident:", error);
                alert("An unexpected error occurred. Check the console for details.");
            }
        });
    } else {
        console.error("Add Resident Form not found.");
    }
});
