

document.getElementById("loginForm").addEventListener("submit", function(event) {
    event.preventDefault();
    var username = document.getElementById("username").value;
    var password = document.getElementById("password").value;
    
    // Validation côté client (vous pouvez ajouter plus de validations si nécessaire)
    if (username.trim() === "" || password.trim() === "") {
        document.getElementById("message").innerText = "Please fill all the form.";
        return;
    }

    // Si les champs sont remplis, soumettre le formulaire
    this.submit();
});

document.getElementById("showSignupForm").addEventListener("click", function() {
    document.getElementById("loginContainer").style.display = "none";
    document.getElementById("signupContainer").style.display = "block";
});

document.getElementById("backToLogin").addEventListener("click", function() {
    document.getElementById("loginContainer").style.display = "block";
    document.getElementById("signupContainer").style.display = "none";
});


document.getElementById("signupForm").addEventListener("submit", function(event) {
    event.preventDefault();
    var fullName = document.getElementById("fullName").value;
    var email = document.getElementById("email").value;
    var gender = document.getElementById("gender").value;
    var age = document.getElementById("age").value;
    var telephone = document.getElementById("telephone").value;
    var username = document.getElementById("signupUsername").value;
    var password = document.getElementById("signupPassword").value;

    // Validation côté client (vous pouvez ajouter plus de validations si nécessaire)
    if (fullName.trim() === "" || email.trim() === "" || gender.trim()=== ""|| age.trim() === "" || telephone.trim() === "" || username.trim() === "" || password.trim() === "") {
        document.getElementById("signupMessage").innerText = "Please fill all the form.";
        return;
    }

    // Soumettre le formulaire si les champs sont remplis
    this.submit();
});

