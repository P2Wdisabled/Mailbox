document.addEventListener('DOMContentLoaded', function() {
    fetch('php/fetch_emails.php')
    .then(response => response.text())
    .then(data => {
        document.getElementById('emails').innerHTML = data;
    });
});
