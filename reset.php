<?php
session_start();

$conn = mysqli_connect("localhost:3307", "root", "", "wedding_db");

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

        if (strlen($new_password) < 6) {
            $message = "Password must be at least 6 characters!";
        }
        else if (!preg_match('/[A-Z]/', $new_password)) {
            $message = "Password must contain at least 1 uppercase letter!";
        }
        else if (!preg_match('/[a-z]/', $new_password)) {
            $message = "Password must contain at least 1 lowercase letter!";
        }
        else if (!preg_match('/[\W_]/', $new_password)) {
            $message = "Password must contain at least 1 symbol!";
        }
        else {
            $table_name = "";
            if ($role == 'customer') {
                $table_name = "customer";
            }
            else if ($role == 'venue_owner') {
                $table_name = "venue_owner";
            }
            else if ($role == 'admin') {
                $table_name = "admin";
            }

            if ($table_name != "") {
                
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

                $query = "UPDATE $table_name SET password = ? WHERE id = ?";
                $stmt = mysqli_prepare($conn, $query);

                if ($stmt) {
                    mysqli_stmt_bind_param($stmt, "si", $hashed_password, $user_id);
                    
                    if (mysqli_stmt_execute($stmt)) {
                        $message = "Password updated successfully for " . strtoupper($role) . " account!";
                        $message_color = "green";
                    } else {
                        $message = "Failed to update password: " . mysqli_stmt_error($stmt);
                    }
                    mysqli_stmt_close($stmt);
                } else {
                    $message = "Database query error.";
                }
            }
            else {
                $message = "Invalid user role!";
            }
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
    background: linear-gradient(135deg, #710349 0%, #4a022f 100%);
    min-height: 100vh;
    width: 100vw;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

.container {
    text-align: center;
    width: 100%;
    max-width: 500px;
}

.logo-container {
    margin-bottom: 10px;
    width: 100%;
}

.logo {
    width: 100%;
    max-width: 260px;
    height: auto;
    display: block;
    margin: 0 auto;
}

.title {
    color: white;
    font-size: 26px;
    margin: 5px 0 25px 0;
    font-weight: 300;
    letter-spacing: 1.5px;
}

.form-card {
    background-color: #ffffff;
    border-radius: 16px;
    padding: 40px 35px;
    text-align: left;
    box-shadow: 0px 15px 35px #330121;
    width: 100%;
}

.instruction {
    color: #666;
    font-size: 15px;
    text-align: center;
    margin-bottom: 20px;
    font-weight: normal;
}

.alert-message {
    padding: 12px;
    border-radius: 8px;
    font-size: 14px;
    margin-bottom: 20px;
    text-align: center;
    font-weight: 500;
}
.alert-red {
    background-color: #fde8e8;
    color: #e53e3e;
    border: 1px solid #f8b4b4;
}
.alert-green {
    background-color: #def7ec;
    color: #03543f;
    border: 1px solid #84e1bc;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    font-size: 14px;
    color: #333;
    margin-bottom: 8px;
    font-weight: 600;
    letter-spacing: 0.5px;
}

.form-group input {
    width: 100%;
    padding: 12px 16px;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    font-size: 15px;
    color: #333;
    background-color: #f9f9f9;
    transition: all 0.3s ease;
}

.form-group input:focus {
    outline: none;
    border-color: #710349;
    background-color: #fff;
    box-shadow: 0 0 0 3px #f1e6ed;
}

.form-group input::placeholder {
    color: #b3b3b3;
    font-size: 14px;
}

.btn-reset {
    width: 100%;
    background-color: #710349;
    color: white;
    border: none;
    border-radius: 8px;
    padding: 14px;
    font-size: 16px;
    font-family: 'Georgia', serif;
    cursor: pointer;
    text-transform: uppercase;
    margin-top: 15px;
    margin-bottom: 20px;
    letter-spacing: 1.5px;
    font-weight: bold;
    transition: background-color 0.3s ease, transform 0.1s ease;
}

.btn-reset:hover {
    background-color: #540236;
}

.btn-reset:active {
    transform: scale(0.98);
}

.login-link {
    text-align: center;
    font-size: 14px;
    color: #666;
}

.login-link a {
    color: #710349;
    text-decoration: none;
    font-weight: bold;
    transition: color 0.2s ease;
}

.login-link a:hover {
    color: #4a022f;
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

            <?php if ($message != ""): ?>
                <div class="alert-message alert-<?php echo $message_color; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="form-group">
                    <label for="new-password">New Password</label>
                    <input type="password" id="new-password" name="new_password" placeholder="Enter New Password" required>
                </div>

                <div class="form-group">
                    <label for="confirm-password">Confirm New Password</label>
                    <input type="password" id="confirm-password" name="confirm_password" placeholder="Confirm Your New Password" required>
                </div>

                <button type="submit" class="btn-reset">Reset Password</button>

                <div class="login-link">
                    Already have an account? <a href="#">Login here</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>