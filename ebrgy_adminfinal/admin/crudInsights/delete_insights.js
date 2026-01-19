function deleteInsight(id) {
  if (confirm("Are you sure you want to delete this insight?")) {
    $.ajax({
      url: "crudInsights/delete_insights.php",
      method: "POST",
      data: { id: id },
      success: function (response) {
        alert("Insight deleted successfully!");
        location.reload();
      },
      error: function () {
        alert("Error deleting insight.");
      },
    });
  }
}
