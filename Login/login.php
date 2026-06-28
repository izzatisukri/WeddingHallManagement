<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = strtolower(trim($_POST['email']));
    $password = $_POST['password'];

    // Contoh sementara (tanpa database)
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
    width:220px;
    margin-bottom:10px;
}

.title{
    color:white;
    margin-bottom:20px;
}

.form-card{
    background:#DCDCDC;
    padding:35px;
    border-radius:20px;
}

.form-group{
    margin-bottom:18px;
}

.form-group label{
    display:block;
    margin-bottom:8px;
    font-weight:bold;
}

.form-group input{
    width:100%;
    padding:12px;
    border-radius:8px;
    border:1px solid #ccc;
}

.btn-login{
    width:100%;
    padding:14px;
    background:#710349;
    color:white;
    border:none;
    border-radius:8px;
    cursor:pointer;
    margin-top:10px;
}

.btn-login:hover{
    background:#540236;
}

.footer-links{
    margin-top:18px;
    text-align:center;
}

.footer-links a{
    color:#710349;
    text-decoration:none;
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
                <input
                    type="email"
                    name="email"
                    placeholder="customer@gmail.com"
                    required>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input
                    type="password"
                    name="password"
                    placeholder="Enter your password"
                    required>
            </div>

            <button type="submit" class="btn-login">
                LOGIN
            </button>

            <div class="footer-links">
                <a href="reset.php">Forgot Password</a> |
                Don't have an account?
                <a href="register.php">Register here</a>
            </div>

        </form>

    </div>

</div>

</body>
</html>