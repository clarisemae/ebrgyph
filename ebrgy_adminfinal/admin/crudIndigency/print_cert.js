// Print Certificate of Indigency record
async function printCertificate(certificateId) {
  if (!certificateId) {
    alert("Invalid Certificate ID.");
    return;
  }

  try {
    // Build the URL with the certificate ID
    const url = `crudIndigency/print_cert.php?id=${certificateId}`; // Ensure this matches the actual PHP file path

    // Open the PHP file in a new tab
    const printWindow = window.open(url, "_blank");

    // Focus on the new window
    if (printWindow) {
      printWindow.focus();
      printWindow.window.print();
    } else {
      alert("Please allow pop-ups to view the certificate.");
    }
  } catch (error) {
    console.error("Error opening print window:", error);
  }
}
