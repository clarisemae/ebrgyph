async function fetchInsights() {
  try {
    const response = await fetch("crudInsights/fetch_insights.php");
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }
    const insights = await response.json();
    renderInsightsTable(insights);
  } catch (error) {
    console.error("Error fetching insights:", error);
  }
}

function renderInsightsTable(insights) {
  const tableBody = document.getElementById("insights-table-body");
  tableBody.innerHTML = ""; // Clear existing rows

  insights.forEach((insight, index) => {
    const row = `
      <tr data-id="${insight.id}">
          <td>${index + 1}</td> <!-- Dynamically add row number -->
          <td>${insight.name}</td>
          <td>${insight.email}</td>
          <td>${insight.date}</td>
          <td>${insight.type}</td>
          <td>${insight.comment}</td>
          <td>${insight.submitted_at}</td>
          <td>
                 <button class="btn btn-info" onclick="replyToInsight(${
                   insight.id
                 })">Reply</button>
                    <button class="btn btn-danger" onclick="deleteInsight(${
                      insight.id
                    })">Delete</button>    
          </td>
      </tr>
    `;
    tableBody.innerHTML += row;
  });
}

document.addEventListener("DOMContentLoaded", fetchInsights);
