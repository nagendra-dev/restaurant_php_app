<?php
session_start(); // Ensure session is started
?>
<?php
// Include config file
require_once "../../config.php";
?>
<head>
 <meta charset="UTF-8">
 <title>Create New Item</title>
 <link rel="stylesheet" href="../CSS/styles.css">

</head>
<body style="margin:103px;">
 <h3>Create New Item</h1>
 <p>Please fill Items Information Properly </p>

<form method="POST" action="verify_create.php" class="createform" >

 <div class="form-group">
 <label for="item_id" class="form-label">Item ID :</label>
 <input type="text" name="item_id" class="form-control" id="item_id" required
item_id="item_id" placeholder="H88" ><br>
 <div id="validationServerFeedback" class="invalid-feedback">
 Please provide a valid item_id.
 </div>
 </div>

 <div class="form-group">
 <label for="item_name">Item Name :</label>
 <input type="text" name="item_name" id="item_name" placeholder="Spaghetti"
required class="form-control " ><br>
 <span class="invalid-feedback"></span>
 </div>

 <div class="form-group">
 <label for="item_type">Item Type:</label>
 <select name="item_type" id="item_type" class="form-control" placeholder="Select
Item Type" required>
 <option value="">Select Item Type</option>
 <option value="soup">Soup</option>
 <option value="fried rice">Fried rice</option>
 <option value="noodles">Noodles</option>
 <option value="Sundae">Sundae</option>
 <option value="Mojito">Mojito</option>
 <option value="Indian rice">Indian Rice</option>
 <option value="Indian gravy">Indian Gravy</option>
 <option value="Starters">Starters</option>
 <option value="Ice cream">Ice cream</option>
 <option value="Fresh juice">Fresh juice</option>

 </select>
 <span class="invalid-feedback"></span>
 </div><br>


 </div><br>

 <div class="form-group">
 <label for="item_price">Item Price :</label>
 <input min='0.01' type="number" name="item_price" id="item_price"
placeholder="12.34" step="0.01" required class="form-control " ><br/><br/>
 <span class="invalid-feedback"></span>
 </div>

 <!-- <div class="form-group">
 <label for="item_description">Item Description :</label>
 <textarea name="item_description" id="item_description" rows="4"
placeholder="The dish...." required class="form-control " ></textarea><br>
 <span class="invalid-feedback"></span>
 </div>-->

 <div class="form-group">
 <input type="submit" class="btn btn-dark" value="Create Item">
 <a href="../menu.php" class="btn btn-dark">Back</a>
 </div>





</form>
</body>
