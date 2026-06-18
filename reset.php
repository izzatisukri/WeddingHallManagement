<?php
session_start();

// utk connectkan database dengan php
$conn = mysqli_connect("localhost:3307", "root", "", "wedding_db");

//utk tgk kalau database tu berjaya ke tak
if (!$conn) {
    die("Database Connection Failed: " . mysqli_connect_error());
}

// utk pastikan takkeluar tulisan pelik pelik
mysqli_set_charset($conn, "utf8mb4");

//utk message
$message = "";
$message_color = "red"; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // ambik dari form lepastu dibersihkn email supaya selamat
    $email = mysqli_real_escape_string($conn, $_POST['email']); 
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // setiap form tu kena wajib isi
    if (empty($email) || empty($new_password) || empty($confirm_password)) {
        $message = "All fields are required!";
    }
    //nk tgk password tu match ke tak
    else if ($new_password !== $confirm_password) {
        $message = "Passwords do not match!";
    }
    else {
        // utk pastikan pwd tu at least 6 char, 1 UC, 1 LC, 1 simbol
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
        //kalau user tak jumpa lagi, sistem kosongkan dulu semua info table and  column
        else {
            $table_name = "";
            $pwd_column = "";
            $email_column = "";
            
            // utk check email wyjud dalam table yg mana satu
            $check_client = mysqli_query($conn, "SELECT client_id FROM client WHERE client_email = '$email'");
            $check_owner = mysqli_query($conn, "SELECT owner_id FROM venue_owner WHERE owner_email = '$email'");
            $check_admin = mysqli_query($conn, "SELECT admin_id FROM admin WHERE admin_email = '$email'");

            //kalau user ni client simpan dalam table client
            if (mysqli_num_rows($check_client) > 0) {
                $table_name = "client"; 
                $pwd_column = "client_password";
                $email_column = "client_email";
            //kalau user ni owner simpan dalam table owner
            } else if (mysqli_num_rows($check_owner) > 0) {
                $table_name = "venue_owner";
                $pwd_column = "owner_password";
                $email_column = "owner_email";
            //kalau user ni admin simpan dalam table admin
            } else if (mysqli_num_rows($check_admin) > 0) {
                $table_name = "admin";
                $pwd_column = "admin_password";
                $email_column = "admin_email";
            }

            // klau user wujud, password baru akan diencrypt dulu sebelum disimpan
            if ($table_name != "") {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

                // system nk update password dlm table betul mengikut emial, tapi tak isi data lagi
                $query = "UPDATE $table_name SET $pwd_column = ? WHERE $email_column = ?";
                $stmt = mysqli_prepare($conn, $query);

                //isi password baru yang dah dihash and email user ke dalam query update yang dah disediakan 
                if ($stmt) {
                    mysqli_stmt_bind_param($stmt, "ss", $hashed_password, $email);
                    
                    //utkmessage update password ttu berjaya ke tak
                    if (mysqli_stmt_execute($stmt)) {
                        $message = "Password has been reset successfully for " . strtoupper(str_replace('_', ' ', $table_name)) . "!";
                        $message_color = "green";
                    } else {
                        $message = "Failed to update password: " . mysqli_stmt_error($stmt);
                    }
                    mysqli_stmt_close($stmt);
                } else {
                    $message = "Database query error.";
                }
            }
            //kalau email takde akan keluar error message
            else {
                $message = "Email address not found in our system!";
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

body 
{
    font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
    background: radial-gradient(circle at center, #7d0552 0%, #520131 70%, #2b0019 100%); 
    height: 100vh; 
    width: 100vw;
    display: flex;
    flex-direction: column; 
    align-items: center; 
    justify-content: center;
    overflow: hidden; 
    padding: 20px;
}

.container {
    text-align: center;
    width: 100%;
    max-width: 580px; 
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.logo-container {
    margin-bottom: 8px;
    width: 100%;
    animation: fadeInDown 0.8s ease-out;
}

.logo {
    width: 100%;
    max-width: 220px; 
    height: auto;
    display: block;
    margin: 0 auto;
    filter: drop-shadow(0px 4px 8px #61033f);
}

.title {
    color: #ffffff;
    font-size: 26px; 
    margin: 5px 0 25px 0; 
    font-weight: 600;
    letter-spacing: 0.8px;
    text-shadow: 0 2px 10px #2b0019;
    animation: fadeInDown 0.8s ease-out;
}

.form-card {
    background-color: #DCDCDC; 
    border: 1px solid #DCDCDC; 
    border-radius: 24px; 
    padding: 35px 45px; 
    text-align: left; 
    width: 100%;
    animation: fadeInUp 0.8s cubic-bezier(0.16, 1, 0.3, 1);
}

.instruction {
    color: #666666; 
    font-size: 15px;
    text-align: center;
    margin-bottom: 25px;
    font-weight: normal;
}

.form-group {
    margin-bottom: 18px;
}

.form-group label {
    display: block;
    font-size: 13.5px; 
    color: #2c2c2c;
    margin-bottom: 4px; 
    font-weight: bold;
    letter-spacing: 0.3px;
}

.password-hint {
    font-size: 11px;
    color: #710349;
    margin-bottom: 6px;
    display: block;
    font-style: italic;
}

.form-group input {
    width: 100%;
    padding: 13px 16px; 
    border: 1.5px solid #e2e8f0; 
    border-radius: 10px; 
    font-size: 14.5px; 
    color: #1a1a1a;
    background-color: #f8fafc; 
    outline: none;
    font-family: inherit;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}

.form-group input:focus {
    border-color: #710349;
    background-color: #ffffff;
    box-shadow: 0 0 0 4px #f1e6ed; 
}

.form-group input::placeholder {
    color: #94a3b8;
    font-size: 13.5px;
}

.btn-reset {
    width: 100%;
    background-color: #710349;
    color: white;
    border: none;
    border-radius: 10px; 
    padding: 14px; 
    font-size: 15px;
    cursor: pointer;
    text-transform: uppercase;
    margin-top: 10px;
    margin-bottom: 18px; 
    letter-spacing: 2px;
    font-weight: bold;
    box-shadow: 0 4px 12px #ceb3c5;
    transition: all 0.2s ease;
}

.btn-reset:hover {
    background-color: #540236;
    box-shadow: #c69fb7 0px 6px 20px;
    transform: translateY(-1px);
}

.btn-reset:active {
    transform: translateY(1px);
    box-shadow: 0 2px 8px #c69fb7;
}

.login-link {
    text-align: center;
    font-size: 13.5px;
    color: #64748b;
}

.login-link a {
    color: #710349;
    text-decoration: none;
    font-weight: bold;
    transition: color 0.2s ease;
}

.login-link a:hover {
    color: #4a002a;
    text-decoration: underline;
}

.alert-message {
    padding: 12px 20px;
    border-radius: 10px;
    margin-bottom: 15px;
    font-weight: bold;
    text-align: center;
    width: 100%;
    max-width: 580px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

@keyframes fadeInDown {
    from { opacity: 0; transform: translateY(-15px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>

</head>
<body>

    <?php if (!empty($message)): ?>
        <div class="alert-message" style="color: <?php echo $message_color; ?>; background-color: <?php echo $message_color == 'green' ? '#e6f4ea' : '#fce8e6'; ?>;">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <div class="container">
        <div class="logo-container">
            <img src="wedding_logo.png" alt="Wedding Logo" class="logo">
        </div>

        <h2 class="title">Reset Password</h2>

        <div class="form-card">
            <p class="instruction">Please enter your email and choose a new password.</p>

            <form action="" method="POST">
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        placeholder="Enter your registered email" 
                        required>
                </div>

                <div class="form-group">
                    <label for="new-password">New Password</label>
                    <span class="password-hint">* Must be at least 6 characters with uppercase, lowercase, and a symbol.</span>
                    <input 
                        type="password" 
                        id="new-password" 
                        name="new_password" 
                        placeholder="Enter New Password" 
                        pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*[!@#$%^&*()_+{}\[\]:;<>,.?~\\/-]).{6,}$"
                        title="Password must be at least 6 characters long and include at least one uppercase letter, one lowercase letter, and one symbol."
                        required>
                </div>

                <div class="form-group">
                    <label for="confirm-password">Confirm New Password</label>
                    <input 
                        type="password" 
                        id="confirm-password" 
                        name="confirm_password" 
                        placeholder="Confirm Your New Password" 
                        required>
                </div>

                <button type="submit" class="btn-reset">Reset Password</button>

                <div class="login-link">
                    Already have an account? <a href="login.html">Login here</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>