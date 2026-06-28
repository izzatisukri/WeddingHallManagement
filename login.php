<?php

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = strtolower(trim($_POST['email']));
    $password = $_POST['password'];

    if (strpos($email, "admin") !== false) {

        header("Location: admin_dashboard.php");
        exit();

    } elseif (strpos($email, "owner") !== false) {

        header("Location: venue_owner.php");
        exit();

    } else {

        header("Location: client.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login Account</title>

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
    background: radial-gradient(circle at center, #7d0552 0%, #520131 70%, #2b0019 100%);
    height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

.container{
    text-align:center;
    width:100%;
    max-width:580px;
}

.logo{
    width:210px;
    margin-bottom:12px;
}

.title{
    color:white;
    margin-bottom:22px;
    font-size: 22px;
}

.form-card{
    background:#DCDCDC;
    padding:38px; 
    border-radius:18px;
    box-shadow: 0 10px 22px rgba(0,0,0,0.28); 
}

.form-group{
    margin-bottom:20px; 
}

.form-group label{
    display:block;
    margin-bottom:10px; 
    font-weight:600; 
    font-size: 14px; 
}

.form-group input{
    width:100%;
    padding:13px; 
    border-radius:10px; 
    border:1px solid #ccc;
    font-size: 14px;
}

.btn-login{
    width:100%;
    padding:15px; 
    background:#710349;
    color:white;
    border:none;
    border-radius:10px; 
    cursor:pointer;
    margin-top:12px; 
    font-size: 15px; 
    font-weight: 600; 
    transition: 0.2s ease;
}

.btn-login:hover{
    background:#540236;
    transform: scale(1.03);
}

.footer-links{
    margin-top:20px; 
    text-align:center;
    font-size: 13px;
}

.footer-links a{
    color:#710349;
    text-decoration:none;
    font-weight: 500; 
}

.footer-links a:hover{
    text-decoration:underline;
}
</style>

</head>

<body>

<div class="container">

    <img src="images/wedding_logo.png" class="logo">

    <h2 class="title">Login to Your Account</h2>

    <div class="form-card">

        <form method="POST" action="">

            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" placeholder="Enter email address" required>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Enter password" required>
            </div>

            <button type="submit" class="btn-login">
                LOGIN
            </button>

            <div class="footer-links">
                <a href="reset.php">Forgot Password</a> |
                Don't have account?
                <a href="register.php">Register here</a>
            </div>

        </form>

    </div>

</div>

</body>
</html>