// Function to validate the password length
function validatePassword() {
    var passwordInput = document.getElementById("password");
    var password = passwordInput.value;
    var errorMessage = document.getElementById("password-error");

    // Check if the password is shorter than 6 characters
    if (password.length < 6) {
        // Display an error message
        errorMessage.innerHTML = "Password must be at least 6 characters long.";
        enableSubmitButton(false); // Disable submit button
        return false;
    }

    // Password is valid, clear error message and enable submit button
    errorMessage.innerHTML = "";
    enableSubmitButton(true); // Enable submit button
    return true;
}


// Function to validate the email format
function validateEmail() {
    var emailInput = document.getElementById("email");
    var email = emailInput.value;
    var errorMessage = document.getElementById("email-error");

    // Regular expression to check the email format
    var emailRegex = /^[\w-]+(\.[\w-]+)*@([\w-]+\.)+((com)|(net)|(ph))$/;

    // Check if the email format is valid
    if (!emailRegex.test(email)) {
        // Display an error message
        errorMessage.innerText = "Please enter a valid email address.";
        enableSubmitButton(false); // Disable submit button
        return false;
    }

    // Email format is valid, clear error message
    errorMessage.innerText = "";
    checkRegistrationValidity(); // Check if registration is valid
    return true;
}

// Function to enable or disable the submit button
function enableSubmitButton(enable) {
    var submitButton = document.getElementById("submit-btn");
    submitButton.disabled = !enable;
}

// Function to check if the registration is valid
function checkRegistrationValidity() {
    var usernameInput = document.getElementById("username");
    var username = usernameInput.value;
    var emailInput = document.getElementById("email");
    var email = emailInput.value;

    // Perform AJAX request to check if the email is available
    var emailRequest = new XMLHttpRequest();
    emailRequest.open("POST", "./php/check_email.php", true);
    emailRequest.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    emailRequest.onreadystatechange = function () {
        if (emailRequest.readyState === 4 && emailRequest.status === 200) {
            if (emailRequest.responseText === "exists") {
                var emailErrorMessage = document.getElementById("email-error");
                emailErrorMessage.innerText = "Email is already taken.";
                enableSubmitButton(false); // Disable submit button
            } else {
                var emailErrorMessage = document.getElementById("email-error");
                emailErrorMessage.innerText = "";
                enableSubmitButton(true); // Enable submit button
            }
        }
    };
    emailRequest.send("email=" + email);

    // Perform AJAX request to check if the username is available
    var usernameRequest = new XMLHttpRequest();
    usernameRequest.open("POST", "./php/check_username.php", true);
    usernameRequest.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    usernameRequest.onreadystatechange = function () {
        if (usernameRequest.readyState === 4 && usernameRequest.status === 200) {
            if (usernameRequest.responseText === "exists") {
                var usernameErrorMessage = document.getElementById("username-error");
                usernameErrorMessage.innerText = "Username is already taken.";
                enableSubmitButton(false); // Disable submit button
            } else {
                var usernameErrorMessage = document.getElementById("username-error");
                usernameErrorMessage.innerText = "";
                enableSubmitButton(true); // Enable submit button
            }
        }
    };
    usernameRequest.send("username=" + username);
}

