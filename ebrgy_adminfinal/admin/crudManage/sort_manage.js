// Function to sort the table based on dropdown selection
function sortTableBy(criteria) {
  const table = document.getElementById('manage-table-body');
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
              aValue = a.cells[1].textContent.trim(); // Name column
              bValue = b.cells[1].textContent.trim();
              break;
          case 'role':
              aValue = a.cells[5].textContent.trim(); // Role column
              bValue = b.cells[5].textContent.trim();
              break;
          case 'date_created':
              aValue = new Date(a.cells[6].textContent.trim()).getTime(); // Date Created column
              bValue = new Date(b.cells[6].textContent.trim()).getTime();
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
  sortTableBy(criteria);
});

// Add event listener for dropdown change
document.getElementById('sort-by').addEventListener('change', (event) => {
    const criteria = event.target.value;
    sortTableBy(criteria);
  });
  
  document.getElementById('search-accounts').addEventListener('input', function () {
    const searchValue = this.value.toLowerCase();
    const table = document.getElementById('manage-table-body');
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
        noRecordsRow.innerHTML = `<td colspan="9" style="text-align: center;">No records found</td>`;
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
  