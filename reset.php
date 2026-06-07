<?php
session_start();

$conn = mysqli_connect("localhost", "root", "", "wedding_db");

if (!$conn) {
    die("Database Connection Failed: " . mysqli_connect_error());
}

$message = "";
$message_color = "red";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (!isset($_SESSION['user_id'])) {
        $message = "Please Log In First!";
    } 
    else if ($new_password !== $confirm_password) {
        $message = "Passwords do not match!";
    } 
    else {
        $user_id = $_SESSION['user_id'];
        $role = $_SESSION['user_role'];
        
        $table_name = "";
        if ($role == 'customer') {
            $table_name = "customer";
        } else if ($role == 'venue_owner') {
            $table_name = "venue_owner";
        } else if ($role == 'admin') {
            $table_name = "admin";
        }

        if ($table_name != "") {
            $query = "UPDATE $table_name SET password = '$new_password' WHERE id = '$user_id'";
            
            if (mysqli_query($conn, $query)) {
                $message = "Password updated successfully for " . strtoupper($role) . " account!";
                $message_color = "green";
            } else {
                $message = "Failed to update password: " . mysqli_error($conn);
            }
        } else {
            $message = "Invalid user role!";
        }
    }
}
?>
    

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>

    <style>
    * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Georgia', Times, serif;
    background: #710349;
    height: 100vh;
    width: 100vw;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 15px 20px;
    overflow: hidden;
}

.container {
    text-align: center;
    width: 100%;
    max-width: 1000px;
    padding: 0;
}

.logo-container {
    margin-bottom: 0;
    width: 100%;
}

.logo {
    width: 100%;
    max-width: 450px; 
    height: auto;
    display: block;
    margin: 0 auto;
}

.title {
    color: white;
    font-size: 40px;
    margin: 8px 0 22px 0;
    font-weight: normal;
    letter-spacing: 1px;
}

.form-card {
    background-color: #DCDCDC;
    border-radius: 45px;
    padding: 45px 75px;
    text-align: left;
    box-shadow: 0px 12px 35px;
    width: 100%;
}

.instruction {
    color: black;
    font-size: 21px;
    text-align: center;
    margin-bottom: 32px;
    font-weight: 500;
}

.form-group {
    margin-bottom: 24px;
}

.form-group label {
    display: block;
    font-size: 21px;
    color: black;
    margin-bottom: 10px;
    font-weight: bold;
}

.form-group input {
    width: 100%;
    padding: 18px 22px;
    border: none;
    border-radius: 12px;
    font-size: 19px;
    color: black;
    background-color: white;
}

.form-group input::placeholder {
    color: #A49A9A;
    font-size: 19px; 
}

.btn-reset {
    width: 100%;
    background-color: #710349;
    color: white;
    border: none;
    border-radius: 40px;
    padding: 20px;
    font-size: 23px;
    font-family: 'Georgia', serif;
    cursor: pointer;
    text-transform: uppercase;
    margin-top: 15px;
    margin-bottom: 25px;
    letter-spacing: 2px;
    font-weight: bold;
}

.btn-reset:hover {
    background-color: #710349;
}

.login-link {
    text-align: center;
    font-size: 19px;
    color: black;
}

.login-link a {
    color: #5778FE;
    text-decoration: none;
    font-weight: bold;
}

.login-link a:hover {
    text-decoration: underline;
}
    </style>

</head>
<body>
    <div class="container">
        <div class="logo-container">
            <img src="wedding_logo.png" alt="Wedding Logo" class="logo">
        </div>

        <h2 class="title">Reset Password</h2>

        <div class="form-card">
            <p class="instruction">Please type something you will remember.</p>

            <form action="#" method="POST">
                <div class="form-group">
                    <label for="new-password">New Password</label>
                    <input type="password" id="new-password" placeholder="Enter New Passsword" required>
                </div>

                <div class="form-group">
                    <label for="confirm-password">Confirm New Password</label>
                    <input type="password" id="confirm-password" placeholder="Confirm Your New Password" required>
                </div>

                <button type="submit" class="btn-reset">Reset Password</button>

                <div class="login-link">
                    Already have an account ? <a href="#">Login here</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>