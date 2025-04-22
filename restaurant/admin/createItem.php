<?php
require_once '../config.php';

$error = '';
$success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_item'])) {
    $item_id = trim($_POST['item_id']);
    $item_name = trim($_POST['item_name']);
    $item_type = trim($_POST['item_type']);
    $item_price = trim($_POST['item_price']);

    // Check for duplicate item_id
    $check = $link->prepare("SELECT item_id FROM Menu WHERE item_id = ?");
    $check->bind_param('s', $item_id);
    $check->execute();
    $check->store_result();
    if ($check->num_rows > 0) {
        $error = "Item ID already exists!";
    } else {
        $stmt = $link->prepare("INSERT INTO Menu (item_id, item_name, item_type, item_price) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('sssd', $item_id, $item_name, $item_type, $item_price);
        $stmt->execute();
        $stmt->close();
        $success = true;
    }
    $check->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Menu Item</title>
    <link rel="stylesheet" href="CSS/styles.css">
</head>
<body>
    <div class="container">
        <h2>Add Menu Item</h2>
        <?php if ($error): ?>
            <div style="color:red; margin-bottom:10px;"><?= htmlspecialchars($error) ?></div>
        <?php elseif ($success): ?>
            <div style="color:green; margin-bottom:10px;">Item added successfully!</div>
        <?php endif; ?>
        <form method="post" action="">
            <label>Item ID:</label><br>
            <input type="text" name="item_id" maxlength="6" required><br><br>
            <label>Item Name:</label><br>
            <input type="text" name="item_name" maxlength="255" required><br><br>
            <label>Item Type:</label><br>
            <input type="text" name="item_type" maxlength="255" required><br><br>
            <label>Item Price:</label><br>
            <input type="number" name="item_price" min="0" step="0.01" required><br><br>
            <button type="submit" name="add_item">Add Item</button>
        </form>
    </div>
</body>
</html>
