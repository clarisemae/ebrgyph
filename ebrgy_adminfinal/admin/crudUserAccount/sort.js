// Sort table by ID, name, gender, or civil status
function sortTableBy(criteria) {
    const table = document.getElementById('user-table-body');
    const rows = Array.from(table.rows);

    rows.sort((a, b) => {
        let aValue, bValue;

        switch (criteria) {
            case 'default':
                aValue = parseInt(a.cells[0].textContent.trim(), 10); // ID column
                bValue = parseInt(b.cells[0].textContent.trim(), 10);
                break;
            case 'name':
                aValue = a.cells[2].textContent.trim(); // Full Name
                bValue = b.cells[2].textContent.trim();
                break;
            case 'gender':
                aValue = a.cells[4].textContent.trim(); // Gender
                bValue = b.cells[4].textContent.trim();
                break;
            case 'civil_status':
                aValue = a.cells[5].textContent.trim(); // Civil Status
                bValue = b.cells[5].textContent.trim();
                break;
            default:
                return 0;
        }

        // Sort alphabetically or numerically
        if (typeof aValue === 'string') {
            return aValue.localeCompare(bValue);
        } else {
            return aValue - bValue;
        }
    });

    // Append sorted rows back to the table
    table.innerHTML = '';
    rows.forEach(row => table.appendChild(row));
}

// Search functionality
document.getElementById('search-users').addEventListener('input', function () {
    const searchValue = this.value.toLowerCase();
    const table = document.getElementById('user-table-body');
    const tableRows = table.querySelectorAll('tr');
    let isAnyRowVisible = false;

    tableRows.forEach(row => {
        const rowText = row.textContent.toLowerCase();
        if (rowText.includes(searchValue)) {
            row.style.display = ''; // Show matching row
            isAnyRowVisible = true;
        } else {
            row.style.display = 'none'; // Hide non-matching row
        }
    });

    // Check if no rows are visible and display "No records found"
    if (!isAnyRowVisible) {
        if (!document.getElementById('no-records')) {
            const noRecordsRow = document.createElement('tr');
            noRecordsRow.id = 'no-records';
            noRecordsRow.innerHTML = `<td colspan="19" style="text-align: center;">No records found.</td>`;
            table.appendChild(noRecordsRow);
        }
    } else {
        const noRecordsRow = document.getElementById('no-records');
        if (noRecordsRow) {
            noRecordsRow.remove();
        }
    }
});

// Add event listener for sorting
document.getElementById('sort-by').addEventListener('change', (event) => {
    const criteria = event.target.value;
    sortTableBy(criteria);
});

// Fetch users on page load
document.addEventListener('DOMContentLoaded', fetchUsers);
