<?php
require_once "../../config.php";
// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
 // Get the values from the form
 $item_id =strtoupper( $_POST["item_id"]);
 $item_name = $_POST["item_name"];
 $item_type = $_POST["item_type"];
 $item_price = $_POST["item_price"];
 $conn = $link;
 // Prepare the SQL query to check if the item_id already exists
 $check_query = "SELECT item_id FROM Menu WHERE item_id = ?";
 $check_stmt = $conn->prepare($check_query);
 $check_stmt->bind_param("s", $item_id);
 $check_stmt->execute();
 $check_result = $check_stmt->get_result();
 // Check if the item_id already exists
 if ($check_result->num_rows > 0) {
 $message = "The item_id is already in use.<br>Please try again to choose a different
item_id.";
 $iconClass = "fa-times-circle";
 $cardClass = "alert-danger";
 $bgColor = "#FFA7A7";
 } else {

 $insert_query = "INSERT INTO Menu (item_id, item_name, item_type, item_price)
 VALUES (?, ?, ?, ?)";
 $stmt = $conn->prepare($insert_query);
 // Bind the parameters
 $stmt->bind_param("sssd", $item_id, $item_name, $item_type, $item_price);
 // Execute the query
 if ($stmt->execute()) {
 $message = "Item created successfully.";
 $iconClass = "fa-check-circle";
 $cardClass = "alert-success";
 $bgColor = "#D4F4DD";
 } else {
 $message = "Error: " . $insert_query . "<br>" . $conn->error;
 $iconClass = "fa-times-circle";
 $cardClass = "alert-danger";
 $bgColor = "#FFA7A7";
 }
 $stmt->close();
 }
 $check_stmt->close();
 $conn->close();
}
?>
<!DOCTYPE html>
<html>
<head>
 <link
href="https://fonts.googleapis.com/css?family=Nunito+Sans:400,400i,700,900&display=swa
p" rel="stylesheet">
 <style>
 body {
 text-align: center;
 padding: 40px 0;
 background: #EBF0F5;
 }
 h1 {
 color: #88B04B;
 font-family: "Nunito Sans", "Helvetica Neue", sans-serif;
 font-weight: 900;
 font-size: 40px;
 margin-bottom: 10px;
 }
 p {
 color: #404F5E;
 font-family: "Nunito Sans", "Helvetica Neue", sans-serif;
 font-size: 20px;
 margin: 0;
 }
 i.checkmark {
 color: #9ABC66;
 font-size: 100px;
 line-height: 200px;
 margin-left: -15px;
 }
 .card {
 background: white;
 padding: 60px;
 border-radius: 4px;
 box-shadow: 0 2px 3px #C8D0D8;
 display: inline-block;
 margin: 0 auto;
 }
 .alert-success {
 background-color: <?php echo $bgColor; ?>;
 }
 .alert-success i {
 color: #5DBE6F;
 }
 .alert-danger {

 background-color: #FFA7A7;
 }
 .alert-danger i {
 color: #F25454;
 }
 .custom-x {
 color: #F25454;
 font-size: 100px;
 line-height: 200px;
 }
 .alert-box {
 max-width: 300px;
 margin: 0 auto;
 }
 .alert-icon {
 padding-bottom: 20px;
 }

 </style>
</head>
<body>
 <div class="card <?php echo $cardClass; ?>" style="display: none;">
 <div style="border-radius: 200px; height: 200px; width: 200px; background: #F8FAF5;
margin: 0 auto;">
 <?php if ($iconClass === 'fa-check-circle'): ?>
 <i class="checkmark">✓</i>
 <?php else: ?>
 <i class="custom-x" style="font-size: 100px; line-height: 200px;">✘</i>
 <?php endif; ?>
 </div>
 <h1><?php echo ($cardClass === 'alert-success') ? 'Success' : 'Error'; ?></h1>
 <p><?php echo $message; ?></p>
 </div>
 <div style="text-align: center; margin-top: 20px;">Redirecting back in <span
id="countdown">3</span></div>
 <script>
 function showPopup() {
 var messageCard = document.querySelector(".card");
 messageCard.style.display = "block";
 var i = 3;
 var countdownElement = document.getElementById("countdown");
 var countdownInterval = setInterval(function() {
 i--;
 countdownElement.textContent = i;
 if (i <= 0) {
 clearInterval(countdownInterval);
 window.location.href = "createItem.php";
 }
 }, 1000); // 1000 milliseconds = 1 second
 }

 window.onload = showPopup;
 function hidePopup() {
 var messageCard = document.querySelector(".card");
 messageCard.style.display = "none";

 setTimeout(function () {
 window.location.href = "createItem.php";
 }, 3000); // 3000 milliseconds = 3 seconds
 }
 setTimeout(hidePopup, 3000);
 </script>
</body>
</html>
