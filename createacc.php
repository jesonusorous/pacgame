<?php
session_start();
include 'db.php';

$message = "";
$success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $confirm_password = mysqli_real_escape_string($conn, $_POST['confirm_password']);

    // Check password match
    if ($password !== $confirm_password) {
        $message = "Passwords do not match.";
    } else {
        // Check if username exists
        $checkQuery = "SELECT * FROM users WHERE username='$username' LIMIT 1";
        $checkResult = mysqli_query($conn, $checkQuery);

        if (mysqli_num_rows($checkResult) > 0) {
            $message = "Username already exists.";
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert user
            $insertQuery = "INSERT INTO users (username, password, role) VALUES ('$username', '$hashed_password', 'user')";
            if (mysqli_query($conn, $insertQuery)) {
                $message = "Account created successfully! You can now login.";
                $success = true;
            } else {
                $message = "Error creating account: " . mysqli_error($conn);
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
<title>Create Account</title>
<style>
body {
    margin: 0;
    padding: 0;
    font-family: Arial, Helvetica, sans-serif;
    background: #020d1f;
    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
}

.login-box {
    width: 380px;
    padding: 25px;
    background: rgba(0, 20, 50, 0.85);
    border-radius: 15px;
    box-shadow: 0 0 20px #0af;
    backdrop-filter: blur(4px);
    text-align: center;
}

.login-box h2 {
    color: #58c2ff;
    margin-bottom: 20px;
    font-size: 26px;
    text-shadow: 0 0 10px #00aaff;
}

.input-group {
    margin-bottom: 15px;
    padding: 5px;
    text-align: left;
}

.input-group input {
    width: 100%;
    padding: 10px;
    margin-top: 5px;
    border-radius: 8px;
    border: none;
    outline: none;
    background: #062544;
    color: white;
    font-size: 14px;
    box-shadow: inset 0 0 6px #00aaff;
}

.btn-login {
    width: 100%;
    padding: 10px;
    background: #007bff;
    border: none;
    border-radius: 8px;
    color: white;
    font-size: 16px;
    cursor: pointer;
    box-shadow: 0 0 12px #00aaff;
    transition: 0.25s;
}

.btn-login:hover {
    background: #005fcc;
    transform: scale(1.03);
}

.error {
    color: #ff4d4d;
    margin-top: 10px;
}

/* Modal popup styling */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background: rgba(0,0,0,0.6);
    justify-content: center;
    align-items: center;
}

.modal-content {
    background-color: #0a2345;
    padding: 20px 30px;
    border-radius: 12px;
    text-align: center;
    color: #4dff4d;
    font-size: 18px;
    box-shadow: 0 0 20px #00aaff;
    animation: popup 0.3s ease;
}

.modal-content button {
    margin-top: 15px;
    padding: 8px 16px;
    border: none;
    border-radius: 8px;
    background-color: #007bff;
    color: white;
    cursor: pointer;
    font-size: 14px;
}

.modal-content button:hover {
    background-color: #005fcc;
}

@keyframes popup {
    from { transform: scale(0.5); opacity: 0; }
    to { transform: scale(1); opacity: 1; }
}
</style>
</head>
<body>

<div class="login-box">
    <h2>Create Account</h2>
    <form method="POST">
        <div class="input-group">
            <input type="text" name="username" placeholder="Username" autocomplete="off" required>
        </div>

        <div class="input-group">
            <input type="password" name="password" placeholder="Password" autocomplete="off" required>
        </div>

        <div class="input-group">
            <input type="password" name="confirm_password" placeholder="Confirm Password" autocomplete="off" required>
        </div>

        <button type="submit" class="btn-login">Register</button>

        <?php if (!$success && $message != ""): ?>
            <p class="error"><?php echo $message; ?></p>
        <?php endif; ?>
    </form>

    <p style="margin-top:15px; font-size:14px; color:#9bd8ff;">
        Already have an account? <a href="index.php" style="color:#00aaff;">Login</a>
    </p>
</div>

<?php if ($success): ?>
<!-- Modal -->
<div id="successModal" class="modal">
    <div class="modal-content">
        <?php echo $message; ?>
        <br>
        <button onclick="closeModal()">OK</button>
    </div>
</div>

<script>
    // Show modal
    document.getElementById('successModal').style.display = 'flex';

    function closeModal() {
        document.getElementById('successModal').style.display = 'none';
        window.location.href = 'index.php'; // Redirect after closing
    }
</script>
<?php endif; ?>

</body>
</html>
