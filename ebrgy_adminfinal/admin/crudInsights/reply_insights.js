function replyToInsight(id) {
  document.getElementById("reply_id").value = id;
  const replyInsightModal = new bootstrap.Modal(document.getElementById("replyInsightModal"));
  replyInsightModal.show();
}

function submitReply() {
  const id = document.getElementById("reply_id").value;
  const replyComment = document.getElementById("reply_comment").value;

  if (!replyComment.trim()) {
    alert("Please enter a reply.");
    return;
  }

  fetch('sendReplyEmail.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({ id: id, reply_comment: replyComment })
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      alert("Reply sent successfully!");
      document.getElementById("replyInsightModal").classList.remove("show");
      // Optionally reset the modal input fields or refresh the page
      document.getElementById("reply_comment").value = '';
    } else {
      alert(data.message);
    }
  })
  .catch(error => {
    console.error('Error sending reply:', error);
    alert("Failed to send reply.");
  });
}