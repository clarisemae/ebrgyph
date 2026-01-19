document.getElementById("addBlotterForm").addEventListener("submit", async function (e) {
    e.preventDefault(); // Prevent the default form submission behavior

    const formData = new FormData(this);

    try {
        const response = await fetch("crudBlotter/add_blotter.php", {
            method: "POST",
            body: formData,
        });

        const result = await response.json();
        if (result.status === "success") {
            alert(result.message);
            location.reload(); // Reload the page to show the new record
        } else {
            alert(result.message || "Failed to add record.");
        }
    } catch (error) {
        console.error("Error adding record:", error);
        alert("An unexpected error occurred.");
    }
});
