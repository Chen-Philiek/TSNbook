//logout js
document.getElementById('logout').addEventListener('click', function(event) {
    event.preventDefault();
    var confirmation = confirm("Are you sure to log out ?");
    if (confirmation) {
        window.location.href = '../html/connexion.html';
    }
});