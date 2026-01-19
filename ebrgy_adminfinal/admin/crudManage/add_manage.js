document.addEventListener("DOMContentLoaded", function () {
    const addAccountForm = document.getElementById("addAccountForm");
    if (addAccountForm) {
        addAccountForm.addEventListener("submit", async function (e) {
            e.preventDefault(); // Prevent default form submission

            const formData = new FormData(this);

            try {
                const response = await fetch('crudManage/add_manage.php', {
                    method: "POST",
                    body: formData,
                });

                const result = await response.json();

                alert(result.message);
                if (result.status === "success") {
                    location.reload(); // Reload the page to reflect changes
                }
            } catch (error) {
                console.error("Error adding account:", error);
                alert("An unexpected error occurred.");
            }
        });
    }
});
