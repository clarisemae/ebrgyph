// Function to sort the table based on dropdown selection
function sortTableBy(criteria) {
    const table = document.getElementById('announcement-table-body');
    const rows = Array.from(table.rows);

    rows.sort((a, b) => {
        let aValue, bValue;

        // Determine sorting criteria
        switch (criteria) {
            case 'title':
                aValue = a.cells[1].textContent.trim(); // Title column
                bValue = b.cells[1].textContent.trim();
                break;
            case 'date':
                aValue = new Date(a.cells[3].textContent.trim()); // Date column
                bValue = new Date(b.cells[3].textContent.trim());
                break;
            case 'status':
                // Access the selected value of the dropdown in the status column
                aValue = a.cells[5].querySelector('select')?.value.trim() || '';
                bValue = b.cells[5].querySelector('select')?.value.trim() || '';
                break;
            default:
                // Default sorting by No. column
                aValue = parseInt(a.cells[0].textContent.trim(), 10); // Row number column
                bValue = parseInt(b.cells[0].textContent.trim(), 10);
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

// Add event listener for dropdown change
document.getElementById('sort-by').addEventListener('change', (event) => {
    const criteria = event.target.value;
    console.log('Sorting by:', criteria); // Debugging
    sortTableBy(criteria);
});

// Debugging Function
function debugRows() {
    const table = document.getElementById('announcement-table-body');
    const rows = Array.from(table.rows);
    rows.forEach((row, index) => {
        console.log(`Row ${index + 1}:`, {
            row_number: row.cells[0]?.textContent.trim(),
            title: row.cells[1]?.textContent.trim(),
            date: row.cells[3]?.textContent.trim(),
            status: row.cells[5]?.querySelector('select')?.value.trim(),
        });
    });
}

// Call debugRows for debugging
document.getElementById('sort-by').addEventListener('change', debugRows);



// Add event listener for dropdown change
document.getElementById('sort-by').addEventListener('change', (event) => {
    const criteria = event.target.value;
    sortTableBy(criteria);
  });
  
  // Add event listener for search input
  document.getElementById('search-announcements').addEventListener('input', function () {
    const searchValue = this.value.toLowerCase();
    const table = document.getElementById('announcement-table-body');
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
  
    // Check if no rows are visible and display "No records found"
    if (!isAnyRowVisible) {
      if (!document.getElementById('no-records')) {
        const noRecordsRow = document.createElement('tr');
        noRecordsRow.id = 'no-records';
        noRecordsRow.innerHTML = `<td colspan="7" style="text-align: center;">No records found</td>`;
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
  