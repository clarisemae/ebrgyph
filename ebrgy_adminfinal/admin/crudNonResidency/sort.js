// Function to sort the Certificate of Non-Residency table based on dropdown selection
function sortTableBy(criteria) {
    const table = document.getElementById('certificate-table-body');
    const rows = Array.from(table.rows);

    rows.sort((a, b) => {
        let aValue, bValue;

        // Determine sorting criteria
        switch (criteria) {
            default:
                aValue = parseInt(a.cells[0].textContent.trim(), 10); // ID column
                bValue = parseInt(b.cells[0].textContent.trim(), 10);
                break;
            case 'name':
                aValue = a.cells[2].textContent.trim(); // Full Name column
                bValue = b.cells[2].textContent.trim();
                break;
            case 'date':
                aValue = new Date(a.cells[7].textContent.trim()); // Requested Date column
                bValue = new Date(b.cells[7].textContent.trim());
                break;
            case 'purpose':
                aValue = a.cells[9].textContent.trim(); // Non-Residency Purpose column
                bValue = b.cells[9].textContent.trim();
                break;
            case 'other_purpose':
                aValue = a.cells[10].textContent.trim(); // Other Purpose column
                bValue = b.cells[10].textContent.trim();
                break;
        }

        // Sort alphabetically or numerically
        if (typeof aValue === 'string') {
            return aValue.localeCompare(bValue);
        } else {
            return aValue - bValue;
        }
    });

    // Append sorted rows back to the table
    table.innerHTML = ''; // Clear existing rows
    rows.forEach(row => table.appendChild(row));
}

// Event listener for live search functionality
document.addEventListener("DOMContentLoaded", () => {
    // Event listener for sorting
    document.getElementById('sort-by-certificates').addEventListener('change', (event) => {
        const criteria = event.target.value;
        sortTableBy(criteria);
    });

    // Event listener for searching
    document.getElementById('search-certificates').addEventListener('input', function () {
        const searchValue = this.value.toLowerCase();
        const table = document.getElementById('certificate-table-body');
        const tableRows = table.querySelectorAll('tr');
        let isAnyRowVisible = false;

        tableRows.forEach(row => {
            const rowText = row.textContent.toLowerCase();
            if (rowText.includes(searchValue)) {
                row.style.display = ''; // Show matching row
                isAnyRowVisible = true; // At least one row is visible
            } else {
                row.style.display = 'none'; // Hide non-matching row
            }
        });

        // Display "No records found" if no matching rows
        if (!isAnyRowVisible) {
            if (!document.getElementById('no-records')) {
                const noRecordsRow = document.createElement('tr');
                noRecordsRow.id = 'no-records';
                noRecordsRow.innerHTML = `<td colspan="18" style="text-align: center;">No records found</td>`;
                table.appendChild(noRecordsRow);
            }
        } else {
            // Remove "No records found" message if rows are visible
            const noRecordsRow = document.getElementById('no-records');
            if (noRecordsRow) {
                noRecordsRow.remove();
            }
        }
    });
});
