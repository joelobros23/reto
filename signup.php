<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reto - Signup</title>
    <link rel="stylesheet" href="css/signup.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>
    <nav>
        
    </nav>
<form action="./php/register.php" method="POST">
    <div style="display: flex; flex-wrap: wrap; justify-content: center; align-items: center; width: 100%;">
        <h1>Sign-up</h1>
    </div>
    <div class="inline-fields">
        <div class="icon"><i class="fa-solid fa-pencil fa-lg" style="color: #ffffff;"></i></div>
        <input id="name" type="text" name="name" placeholder="Name" required>
        <input id="lastname" type="text" name="lastname" placeholder="Last Name" required>
    </div>
    <div class="icon"><i class="fa-solid fa-user fa-lg" style="color: #ffffff;"></i></div>
    <input id="username" type="text" name="username" placeholder="Username" onblur="checkRegistrationValidity()" required>
    <div class="icon"><i class="fa-solid fa-envelope fa-lg" style="color: #ffffff;"></i></div>
    <input id="email" type="text" name="email" onblur="validateEmail()" placeholder="Email" onblur="checkRegistrationValidity()" required>
    <div class="icon"><i class="fa-solid fa-unlock-keyhole fa-lg" style="color: #ffffff;"></i></div>
    <input id="password" type="password" name="password" onkeyup="validatePassword()" placeholder="Password" required>

    <div class="inline-fields">
        <div class="icon"><i class="fa-solid fa-venus-mars" style="color: #ffffff;"></i></div>
        <select name="gender">
            <option value="Male">Male</option>
            <option value="Female">Female</option>
            <option style="background-color: pink;" value="Lesbian">Lesbian</option>
            <option style="background-color: pink;" value="Gay">Gay</option>
            <option style="background-color: pink;" value="Bisexual">Bisexual</option>
            <option style="background-color: pink;" value="Transgender">Transgender</option>
        </select>

        <div class="input-holder">
        <span class="icon"><i class="fa-solid fa-cake-candles fa-lg" style="color: #ffffff;"></i></span>
        <input type="date" id="birthdate" name="birthdate" required>
        </div>

    </div>

    <div class="submit-holder">
        <input id="submit-btn" type="submit" value="DONE!" onclick="checkRegistrationValidity()" disabled>
    </div>
        <!-- Error message element -->
    <!-- Error message elements -->
    <a href="login">Already have an acount? Click here to Login</a>
    <div class="error-holder">
    <p class="error" id="password-error"></p>
    <p class="error" id="email-error"></p>
    <div id="username-error" class="error"></div>
    </div>
</form>
<script src="./script/register.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js" integrity="sha512-fD9DI5bZwQxOi7MhYWnnNPlvXdp/2Pj3XSTRrFs5FQa4mizyGLnJcN6tuvUS6LbmgN1ut+XGSABKvjN0H6Aoow==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
</body>
</html>