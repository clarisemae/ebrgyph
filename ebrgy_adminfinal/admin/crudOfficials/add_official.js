document.addEventListener("DOMContentLoaded", function () {
    const addOfficialForm = document.getElementById("addOfficialForm");
    if (addOfficialForm) {
        addOfficialForm.addEventListener("submit", async function (e) {
            e.preventDefault(); // Prevent default form submission

            const formData = new FormData(this);
            formData.append("add_official", true);

            try {
                const response = await fetch("crudOfficials/add_official.php", {
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

                const modalBody = document.querySelector("#addOfficialModal .modal-body");
                modalBody.prepend(alertBox);

                if (result.status === "success") {
                    setTimeout(() => location.reload(), 2000);
                }
            } catch (error) {
                console.error("Error adding official:", error);
                alert("An unexpected error occurred. Check the console for details.");
            }
        });
    } else {
        console.error("Add Official Form not found.");
    }
});
