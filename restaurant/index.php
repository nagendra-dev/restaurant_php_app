<?php
session_start(); // Start the session at the very beginning
?>
<html>
 <head>
 <link rel="stylesheet" href="stylelog.css">
 <title>login page</title>
 </head>
 <body>
 <div class="login-container">
 <div class="wrapper">
 <form method="post" >
 <h2>Login</h2>
 <div class="input-box">
 <input type="text" name="username" placeholder="USERNAME" required/>
 </div>
 <div class="input-box">
 <input type="password" name="password" placeholder="PASSWORD" required/>
 </div>
 <button type="submit" name="submit">Login</button>
 <div class="register-link" style="text-align: center; margin-top: 15px;">
 <p style="color: #fff;">Don't have an account? <a href="register.php" style="color: #0ef;">Register here</a></p>
 </div>
 </form>
 <?php
require_once "./config.php";
if($_SERVER["REQUEST_METHOD"]=="POST")
{
    $username_posted = trim($_POST['username']);
    $password_posted = $_POST['password'];

    if(empty($username_posted) || empty($password_posted)){
        echo "<center><p style='color:white;font-weight:bolder;font-family: sans-serif;'>Please enter username and password.</p></center>";
    } else {
        $sql = "SELECT id, username, password, role FROM login WHERE username = ?";
        
        if($stmt = $link->prepare($sql)){
            $stmt->bind_param("s", $param_username);
            $param_username = $username_posted;
            
            if($stmt->execute()){
                $stmt->store_result();
                
                if($stmt->num_rows == 1){
                    $stmt->bind_result($id, $username_db, $hashed_password_db, $role_db);
                    if($stmt->fetch()){
                        if(password_verify($password_posted, $hashed_password_db)){
                            // Password is correct, so start a new session
                            // session_start(); // Already started at the top
                            
                            // Store data in session variables
                            $_SESSION['loggedin'] = true; // Optional: flag for logged in state
                            $_SESSION['id'] = $id; // Optional: store user id
                            $_SESSION['username'] = $username_db;
                            $_SESSION['role'] = $role_db; // Store the role from database
                            
                            // Redirect user to home page (or admin dashboard if admin)
                            if ($role_db === 'admin') {
                                header("location: admin/home.php");
                            } else {
                                header("location: admin/home.php"); // Or a user-specific home page e.g., home.php or order_food.php
                            }
                            exit; // Ensure no further code is executed after redirect
                        } else{
                            // Password is not valid
                            echo "<center><p style='color:white;font-weight:bolder;font-family: sans-serif;'>Invalid username or password.</p></center>";
                        }
                    }
                } else{
                    // Username doesn't exist
                    echo "<center><p style='color:white;font-weight:bolder;font-family: sans-serif;'>Invalid username or password.</p></center>";
                }
            } else{
                echo "<center><p style='color:white;font-weight:bolder;font-family: sans-serif;'>Oops! Something went wrong. Please try again later.</p></center>";
            }
            $stmt->close();
        } else {
            echo "<center><p style='color:white;font-weight:bolder;font-family: sans-serif;'>Database error. Please try again later.</p></center>";
        }
    }
    $link->close();
}
?>
 </div>
 </div>
 </body>
</html>