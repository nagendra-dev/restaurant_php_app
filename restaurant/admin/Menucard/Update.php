<?php
require_once "../../config.php";
// Check if 'id' is set and not empty
if (isset($_GET['id']) && !empty($_GET['id'])) {
 $item_id = $_GET['id'];
 $sql = "SELECT * FROM Menu WHERE item_id = ?";

 if ($stmt = mysqli_prepare($link, $sql)) {
 mysqli_stmt_bind_param($stmt, "s", $param_item_id);
 $param_item_id = $item_id;

 if (mysqli_stmt_execute($stmt)) {
 $result = mysqli_stmt_get_result($stmt);
 if (mysqli_num_rows($result) == 1) {
 $row = mysqli_fetch_assoc($result);
 $item_name = $row['item_name'];
 $item_type = $row['item_type'];

 $item_price = $row['item_price'];
 } else {
 echo "Item not found.";
 exit();
 }
 } else {
 echo "Error retrieving item details.";
 exit();
 }

 }
} else {
 header("Location: ../menu.php.");
 exit(); // Make sure to exit after redirect
}
if($_SERVER["REQUEST_METHOD"]=="POST")
{
 $_item_name=$_POST["item_name"];
 $item_type = trim($_POST["item_type"]);

 $item_price = floatval($_POST["item_price"]); // Convert to float

 // Update the item in the database
 $update_sql = "UPDATE Menu SET item_name='$item_name', item_type='$item_type',
item_price='$item_price' WHERE item_id='$item_id'";
 $resultItems = mysqli_query($link, $update_sql);

 if ($resultItems) {
 // Item updated successfully

 header("Location: ../menu.php");
 echo 'success';
 exit();
 } else {
 echo "Error updating item: ";
 }
}?>
<html>
 <head>
 <link rel="stylesheet" href="../CSS/styles.css">
</head>
<body>
 <div class="login-container" style="margin: 44px 47px;">
 <div class="login_wrapper">

 <div class="wrapper">
 <h2 style="text-align: center;">Update Item</h2>

 <form action="" method="post" >
 <div class="form-group">
 <label for="item_name" class="form-label" >Item Name:</label>
 <input type="text" name="item_name" id="item_name" class="form-control"
placeholder="Spaghetti" value="<?php echo ($item_name); ?>" required>
 </div>
 <div class="form-group">
 <label for="item_type" class="form-label">Item Type:</label>s
 <input type="text" name="item_type" id="item_type" class="formcontrol"placeholder="Beer, Cocktail, etc .." value="<?php echo
htmlspecialchars($item_type); ?>" required>
 </div>

 <div class="form-group" class="form-label">
 <label for="item_price">Item Price:</label>
 <input type="number" min=0.01 step="0.01" name="item_price" id="item_price"
placeholder="Enter Item Price"class="form-control" value="<?php echo
htmlspecialchars($item_price);?>" required>
 </div>
 <br>

 <button class="btn btn-light" type="submit" name="submit"
value="submit">Update</button>
 <a class="btn btn-danger" href="../menu.php" >Cancel</a>
 </form>
 </div>
 </div>
 </div>
</body>
</html>
