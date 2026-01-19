// /js/utils.js

// Utility function for making AJAX requests
function makeAjaxRequest(url, method, data, callback) {
    const xhr = new XMLHttpRequest();
    xhr.open(method, url, true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
  
    xhr.onload = function() {
      if (xhr.status == 200) {
        callback(xhr.responseText);
      } else {
        console.error("Error with the request:", xhr.statusText);
      }
    };
  
    xhr.send(data);
  }
