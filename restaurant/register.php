<?php
session_start();
require_once './config.php';

$username = "";
$password = "";
$confirm_password = "";
$error_message = "";
$success_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Basic Validations
    if (empty($username) || empty($password) || empty($confirm_password)) {
        $error_message = "Please fill in all fields.";
    } elseif ($password !== $confirm_password) {
        $error_message = "Passwords do not match.";
    } elseif (strlen($password) < 6) { // Example: Minimum password length
        $error_message = "Password must be at least 6 characters long.";
    } else {
        // Check if username already exists
        $sql_check_user = "SELECT id FROM login WHERE username = ?";
        if ($stmt_check_user = $link->prepare($sql_check_user)) {
            $stmt_check_user->bind_param("s", $param_username_check);
            $param_username_check = $username;
            if ($stmt_check_user->execute()) {
                $stmt_check_user->store_result();
                if ($stmt_check_user->num_rows == 1) {
                    $error_message = "This username is already taken.";
                } else {
                    // Username is available, proceed to insert
                    $sql_insert_user = "INSERT INTO login (username, password, role) VALUES (?, ?, ?)";
                    if ($stmt_insert_user = $link->prepare($sql_insert_user)) {
                        $stmt_insert_user->bind_param("sss", $param_username, $param_password_hash, $param_role);
                        
                        $param_username = $username;
                        $param_password_hash = password_hash($password, PASSWORD_DEFAULT); // Hash the password
                        $param_role = 'user'; // Default role for new users
                        
                        if ($stmt_insert_user->execute()) {
                            $success_message = "Registration successful! You can now <a href='index.php'>login</a>.";
                            // Clear form fields after successful registration
                            $username = ""; 
                        } else {
                            $error_message = "Something went wrong. Please try again later.";
                        }
                        $stmt_insert_user->close();
                    }
                }
            } else {
                $error_message = "Oops! Something went wrong while checking username. Please try again later.";
            }
            $stmt_check_user->close();
        } else {
            $error_message = "Database error. Please try again later.";
        }
    }
    $link->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Registration</title>
    <link rel="stylesheet" href="stylelog.css"> <!-- Assuming you use the same stylesheet -->
    <style>
        .message {
            text-align: center;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
        }
        .success-message {
            color: #155724;
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
        }
        .error-message {
            color: #721c24;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <div class="login-container"> <!-- Re-using login-container for similar styling -->
        <div class="wrapper">
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <h2>Register</h2>

                <?php if(!empty($success_message)): ?>
                    <div class="message success-message"><?php echo $success_message; ?></div>
                <?php endif; ?>
                <?php if(!empty($error_message)): ?>
                    <div class="message error-message"><?php echo $error_message; ?></div>
                <?php endif; ?>

                <?php if(empty($success_message)): // Hide form if registration was successful ?>
                <div class="input-box">
                    <input type="text" name="username" placeholder="CHOOSE A USERNAME" value="<?php echo htmlspecialchars($username); ?>" required>
                </div>
                <div class="input-box">
                    <input type="password" name="password" placeholder="CREATE A PASSWORD" required>
                </div>
                <div class="input-box">
                    <input type="password" name="confirm_password" placeholder="CONFIRM PASSWORD" required>
                </div>
                <button type="submit" name="submit">Register</button>
                <div class="register-link" style="text-align: center; margin-top: 15px;">
                    <p style="color: #fff;"><a href="index.php" style="color: #0ef;">&laquo; Back to Login</a></p>
                </div>
                <?php endif; ?>
            </form>
        </div>
    </div>
</body>
</html>
