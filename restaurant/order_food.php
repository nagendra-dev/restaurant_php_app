<?php
session_start();
require_once 'config.php';

// Initialize variables for user's pending order
$user_pending_order_id = null;
$user_pending_order_total = 0;
$page_message = ''; // For error or success messages

// Check if a user is logged in and if they have an unpaid order
if (isset($_SESSION['username'])) {
    $current_username = $_SESSION['username'];
    $stmt_check = $link->prepare("SELECT order_id, total_price FROM orders WHERE username = ? AND is_paid = FALSE ORDER BY order_id DESC LIMIT 1");
    if ($stmt_check) {
        $stmt_check->bind_param("s", $current_username);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        if ($row_check = $result_check->fetch_assoc()) {
            $user_pending_order_id = $row_check['order_id'];
            $user_pending_order_total = $row_check['total_price'];
        }
        $stmt_check->close();
    } else {
        // Handle prepare statement error if necessary
        $page_message = '<div style="background:#f8d7da;padding:10px;margin:10px 0;">Error checking for pending orders.</div>';
    }
}

// Fetch menu items from the database for display
$sql_menu = "SELECT * FROM menu ORDER BY item_id;";
$result_menu = mysqli_query($link, $sql_menu);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order'])) {
    // Check again if the logged-in user has a pending order before processing a new one
    // This is a repeat check, but good for robustness if $user_pending_order_id was somehow not set before form display
    $can_place_new_order = true;
    if (isset($_SESSION['username'])) {
        $stmt_recheck = $link->prepare("SELECT order_id FROM orders WHERE username = ? AND is_paid = FALSE ORDER BY order_id DESC LIMIT 1");
        if($stmt_recheck) {
            $stmt_recheck->bind_param("s", $_SESSION['username']);
            $stmt_recheck->execute();
            $result_recheck = $stmt_recheck->get_result();
            if ($result_recheck->num_rows > 0) {
                $existing_pending_order = $result_recheck->fetch_assoc();
                $user_pending_order_id = $existing_pending_order['order_id']; // Update with the most current one
                $page_message = '<div style="background:#f8d7da;padding:10px;margin:10px 0;">You already have a pending order (ID: ' . htmlspecialchars($user_pending_order_id) . '). Please pay it before placing a new one.</div>';
                $can_place_new_order = false;
            }
            $stmt_recheck->close();
        } else {
            $page_message = '<div style="background:#f8d7da;padding:10px;margin:10px 0;">Error re-checking for pending orders.</div>';
            $can_place_new_order = false;
        }
    } // For guests, $user_pending_order_id would be null from the start, so they can always attempt to place an order.

    if ($can_place_new_order) {
        $ordered_items_data = [];
        if (!empty($_POST['quantity'])) {
            foreach ($_POST['quantity'] as $item_id_posted => $qty_posted) {
                $qty_posted = intval($qty_posted);
                if ($qty_posted > 0) {
                    $ordered_items_data[] = [
                        'item_id' => $item_id_posted,
                        'quantity' => $qty_posted
                    ];
                }
            }
        }

        if (!empty($ordered_items_data)) {
            $items_str_for_db = [];
            $total_price_for_new_order = 0;
            foreach ($ordered_items_data as $order_item) {
                $item_id_db = $order_item['item_id'];
                $qty_db = $order_item['quantity'];
                $item_res_db = mysqli_query($link, "SELECT item_name, item_price FROM menu WHERE item_id='$item_id_db'");
                $item_row_db = mysqli_fetch_assoc($item_res_db);
                $item_name_db = $item_row_db['item_name'];
                $item_price_db = $item_row_db['item_price'];
                $items_str_for_db[] = $item_name_db . ' x ' . $qty_db;
                $total_price_for_new_order += $item_price_db * $qty_db;
            }
            $items_ordered_for_db = implode(', ', $items_str_for_db);
            $username_for_db = isset($_SESSION['username']) ? $_SESSION['username'] : 'guest';
            
            // Insert new order with is_paid = FALSE
            $stmt_insert = $link->prepare("INSERT INTO orders (username, items_ordered, total_price, is_paid) VALUES (?, ?, ?, FALSE)");
            if ($stmt_insert) {
                $stmt_insert->bind_param("ssd", $username_for_db, $items_ordered_for_db, $total_price_for_new_order);
                $stmt_insert->execute();
                $new_inserted_order_id = $stmt_insert->insert_id;
                $stmt_insert->close();

                if ($new_inserted_order_id) {
                    // Update page state to reflect this new pending order
                    $user_pending_order_id = $new_inserted_order_id;
                    $user_pending_order_total = $total_price_for_new_order;
                    $page_message = '<div style="background:#d4edda;padding:10px;margin:10px 0;">Order (ID: ' . htmlspecialchars($user_pending_order_id) . ') placed successfully! Total: ₹' . number_format($user_pending_order_total, 2) . '</div>';
                } else {
                    $page_message = '<div style="background:#f8d7da;padding:10px;margin:10px 0;">Failed to place the order. Please try again.</div>';
                }
            } else {
                 $page_message = '<div style="background:#f8d7da;padding:10px;margin:10px 0;">Error preparing to save order.</div>';
            }
        } else {
            $page_message = '<div style="background:#f8d7da;padding:10px;margin:10px 0;">Please select at least one item to order.</div>';
        }
    } // else: $page_message already set if cannot place new order
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Order Food</title>
    <link rel="stylesheet" href="admin/CSS/menustyle.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        .order-table {width: 100%; border-collapse: collapse; margin-top: 20px;}
        .order-table th, .order-table td {padding: 10px; border: 1px solid #ccc; text-align: center;}
        .order-btn {margin-top: 20px; padding: 10px 20px; background: #28a745; color: #fff; border: none; cursor: pointer; border-radius: 4px;}
    </style>
</head>
<body>
    <?php 
    // Display any messages from POST handling or initial checks
    if (!empty($page_message)) {
        echo $page_message;
    }

    // Check if there is a pending order for the user (either pre-existing or just placed)
    if ($user_pending_order_id !== null): 
    ?>
        <div class="menucontainer" style="text-align: center;">
            <h2>Order Awaiting Payment</h2>
            <p style="margin-bottom: 20px; font-size: 1.1em;">
                You have an order (ID: <?= htmlspecialchars($user_pending_order_id) ?>) awaiting payment. <br>
                Total Amount: ₹<?= number_format($user_pending_order_total, 2) ?><br>
                Please complete the payment before placing a new order.
            </p>
            <a href="admin/payment.php?order_id=<?= $user_pending_order_id ?>" 
               style="display: inline-block; padding: 10px 20px; background: #28a745; color: #fff; text-decoration: none; border-radius: 4px; font-size: 1.1em; margin-bottom: 20px;">
               <i class="fa fa-credit-card"></i> Pay Bill for Order #<?= htmlspecialchars($user_pending_order_id) ?>
            </a>
        </div>
    <?php else: // No pending order, show the normal order form ?>
    <div class="menucontainer">
        <h2>Order Food</h2>
        <form method="POST">
            <table class="order-table">
                <thead>
                    <tr>
                        <th>Item Name</th>
                        <th>Type</th>
                        <th>Price</th>
                        <th>Quantity</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($result_menu && mysqli_num_rows($result_menu) > 0): ?>
                    <?php while ($row_menu = mysqli_fetch_assoc($result_menu)): ?>
                        <tr>
                            <td><?= htmlspecialchars(ucfirst($row_menu['item_name'])) ?></td>
                            <td><?= htmlspecialchars(ucfirst($row_menu['item_type'])) ?></td>
                            <td><?= htmlspecialchars($row_menu['item_price']) ?></td>
                            <td><input type="number" name="quantity[<?= $row_menu['item_id'] ?>]" min="0" max="20" value="0" style="width:60px;"></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="4">No menu items available.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
            <button type="submit" name="order" class="order-btn"><i class="fa fa-shopping-cart"></i> Place Order</button>
        </form>
    </div>
    <?php endif; // End of pending order check ?>
</body>
</html>
<?php
// Close connection
mysqli_close($link);
?>
