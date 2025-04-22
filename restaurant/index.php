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
 </form>
 <?php
require_once "./config.php";
if($_SERVER["REQUEST_METHOD"]=="POST")
{
 $username=$_POST['username'];
 $password=$_POST['password'];
 $sql="SELECT *FROM login WHERE username='$username' AND password='$password'";
 $result=$link->query($sql);
 ?>
 <script>
    console.log("Login result: <?php echo json_encode($result->num_rows); ?>");
 </script>
 <?php
 if($result->num_rows==1)
 {
 echo "<center><p style='color:white;font-weight:bolder;'>login
successful</p></center>";
 header("location:admin\home.php");
 }
 else{
 echo "<center><p style='color:white;font-weight:bolder;font-family: sans-serif;'>Invalid
username or password</p></center>";
 }

}
?>
 </div>
 </div>
 </body>
</html>