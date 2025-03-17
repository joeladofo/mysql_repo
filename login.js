document.getElementById("loginForm").addEventListener("submit", function (event) {
    const email = document.getElementById("email").value;
    const password = document.getElementById("password").value;

    let isValid = true;

    // Validate email
    if (!email.includes("@") || !email.includes(".")) {
        document.getElementById("emailError").innerText = "Please enter a valid email address.";
        isValid = false;
    } else {
        document.getElementById("emailError").innerText = "";
    }

    // Validate password length
    if (password.length < 6) {
        document.getElementById("passwordError").innerText = "Password must be at least 6 characters.";
        isValid = false;
    } else {
        document.getElementById("passwordError").innerText = "";
    }

    // Prevent form submission if validation fails
    if (!isValid) {
        event.preventDefault();
    }
});
