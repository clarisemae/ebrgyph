document.addEventListener("DOMContentLoaded", () => {
  const burgerMenu = document.getElementById("burger-menu");
  const burgerDropdown = document.getElementById("burger-dropdown");

  burgerMenu.addEventListener("click", () => {
    const isExpanded = burgerMenu.getAttribute("aria-expanded") === "true";
    burgerMenu.setAttribute("aria-expanded", !isExpanded);
    burgerDropdown.classList.toggle("active");
    burgerDropdown.setAttribute("aria-hidden", isExpanded);
  });

  document
    .getElementById("id-photo")
    .addEventListener("change", function (event) {
      const [file] = event.target.files;
      const img = document.getElementById("uploaded-photo");
      if (file) {
        img.src = URL.createObjectURL(file);
        img.style.display = "block";
        document.getElementById("upload-button").style.display = "none";
      }
    });
});
