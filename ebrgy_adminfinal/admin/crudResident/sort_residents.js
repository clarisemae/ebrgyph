// Function to sort the table based on dropdown selection
function sortTableBy(criteria) {
  const table = document.getElementById('resident-table-body');
  const rows = Array.from(table.rows);

  rows.sort((a, b) => {
    let aValue, bValue;

    // Determine sorting criteria
    switch (criteria) {
      default:
        // No. column - numeric sorting
        aValue = parseInt(a.cells[0].textContent.trim(), 10); 
        bValue = parseInt(b.cells[0].textContent.trim(), 10);
        break;
      case 'gender':
        // Gender column - case-insensitive sorting
        aValue = a.cells[4].textContent.trim().toLowerCase(); // Gender column (index 3)
        bValue = b.cells[4].textContent.trim().toLowerCase();
        break;
      case 'sector':
        // Sector column - case-insensitive sorting
        aValue = a.cells[5].textContent.trim().toLowerCase(); // Sector column (index 4)
        bValue = b.cells[5].textContent.trim().toLowerCase();
        break;
      case 'name':
        // Name column - case-insensitive sorting
        aValue = a.cells[1].textContent.trim().toLowerCase(); // Name column (index 1)
        bValue = b.cells[1].textContent.trim().toLowerCase();
        break;
    }

    // Sort alphabetically or numerically based on the type of values
    if (typeof aValue === 'string') {
      return aValue.localeCompare(bValue);  // Sort alphabetically for strings
    } else {
      return aValue - bValue;  // Sort numerically for numbers
    }
  });

  // Append sorted rows back to the table
  table.innerHTML = ''; // Clear existing rows
  rows.forEach(row => table.appendChild(row));
}

// Add event listener for dropdown change
document.getElementById('sort-by').addEventListener('change', (event) => {
  const criteria = event.target.value;
  sortTableBy(criteria);
});

// Search functionality
document.getElementById('search-residents').addEventListener('input', function () {
  const searchValue = this.value.toLowerCase();
  const table = document.getElementById('resident-table-body');
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
