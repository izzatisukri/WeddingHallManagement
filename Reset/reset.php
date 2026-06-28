<?php
$servername = "127.0.0.1";
$username = "root";
$password = "";
$dbname = "wedding_db";
$port = 3307;
$conn = new mysqli($servername, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    die(json_encode(['status' => 'error', 'message' => 'Database connection failed.']));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    $email = $conn->real_escape_string($_POST['email']);
    
    if ($_POST['action'] === 'check_email') {
        $sql = "SELECT client_id FROM client WHERE client_email = '$email'";
        $result = $conn->query($sql);
        
        if ($result->num_rows > 0) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Alamat emel tidak dijumpai.']);
        }
        exit;
    }
    
    if ($_POST['action'] === 'reset_password') {
        $new_password = $_POST['new_password'];
        
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $sql = "UPDATE client SET client_password = '$hashed_password' WHERE client_email = '$email'";
        
        if ($conn->query($sql) === TRUE) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Ralat pangkalan data. Sila cuba lagi.']);
        }
        exit;
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
    font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
    background: radial-gradient(circle at center, #7d0552 0%, #520131 70%, #2b0019 100%); 
    height: 100vh; 
    width: 100vw;
    display: flex;
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
    color: #555555; 
    font-size: 14.5px;
    text-align: center;
    margin-bottom: 25px;
    font-weight: 500;
    line-height: 1.4;
}

.form-group {
    margin-bottom: 18px;
    transition: all 0.4s ease;
}

#password-section {
    display: none;
    opacity: 0;
    transform: translateY(-10px);
    transition: all 0.4s ease;
}

.form-group label {
    display: block;
    font-size: 13.5px; 
    color: #2c2c2c;
    margin-bottom: 6px; 
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

.btn-action {
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

.btn-action:hover {
    background-color: #540236;
    box-shadow: #c69fb7 0px 6px 20px;
    transform: translateY(-1px);
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
}

.login-link a:hover {
    color: #4a002a;
    text-decoration: underline;
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
    <div class="container">
        <div class="logo-container">
            <img src="images/wedding_logo.png" alt="Wedding Logo" class="logo">
        </div>

        <h2 class="title">Reset Password</h2>

        <div class="form-card">
            <p id="instruction-text" class="instruction">Enter your registered email address to find your account.</p>

            <form id="resetForm" action="#" method="POST">
                
                <div class="form-group" id="email-section">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" placeholder="customer@gmail.com" required>
                </div>

                <div id="password-section">
                    <div class="form-group">
                        <label for="new-password">New Password</label>
                        <span class="password-hint">* Must be at least 6 characters with uppercase, lowercase, and a symbol.</span>
                        <input 
                            type="password" 
                            id="new-password" 
                            placeholder="Create a password">
                    </div>

                    <div class="form-group">
                        <label for="confirm-password">Confirm New Password</label>
                        <input type="password" id="confirm-password" placeholder="Re-enter password">
                    </div>
                </div>

                <button type="submit" id="submit-btn" class="btn-action">Next</button>

                <div class="login-link">
                    Already have an account? <a href="login.html">Login here</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        const resetForm = document.getElementById('resetForm');
        const emailSection = document.getElementById('email-section');
        const passwordSection = document.getElementById('password-section');
        const submitBtn = document.getElementById('submit-btn');
        const instructionText = document.getElementById('instruction-text');
        
        let currentStep = 1;

        resetForm.addEventListener('submit', function(event) {
            event.preventDefault();
            
            const email = document.getElementById('email').value;

            if (currentStep === 1) {
                fetch('', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'action=check_email&email=' + encodeURIComponent(email)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        emailSection.style.display = 'none';
                        passwordSection.style.display = 'block';
                        
                        instructionText.innerHTML = '<span style="color: #710349; font-weight: bold;">Account found.</span> Please enter your new password.';
                        
                        setTimeout(() => {
                            passwordSection.style.opacity = '1';
                            passwordSection.style.transform = 'translateY(0)';
                        }, 50);

                        document.getElementById('new-password').required = true;
                        document.getElementById('confirm-password').required = true;

                        submitBtn.innerText = 'Reset Password';
                        currentStep = 2;
                    } else {
                        alert(data.message);
                    }
                });

            } else if (currentStep === 2) {
                const newPassword = document.getElementById('new-password').value;
                const confirmPassword = document.getElementById('confirm-password').value;

                if (newPassword !== confirmPassword) {
                    alert('Passwords do not match! Please check again.');
                } else {
                    fetch('', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: 'action=reset_password&email=' + encodeURIComponent(email) + '&new_password=' + encodeURIComponent(newPassword)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            alert('Password has been successfully updated!');
                            window.location.href = 'login.html';
                        } else {
                            alert(data.message);
                        }
                    });
                }
            }
        });
    </script>
</body>
</html>