<?php
session_start();
require_once '../config.php';
// Only clear cart if user clicks 'Clear Cart'
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['clear_cart'])) {
    unset($_SESSION['cart']);
    $_SESSION['cart'] = [];
    header("Location: posCart.php");
    exit;
}
// Add to cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['item_code'])) {
    $code = $_POST['item_code'];
    $qty = intval($_POST['qty']);
    if (isset($_SESSION['cart'][$code])) {
        $_SESSION['cart'][$code] += $qty;
    } else {
        $_SESSION['cart'][$code] = $qty;
    }
    header("Location: posCart.php");
    exit;
}
// Delete item from cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_code'])) {
    $code = $_POST['delete_code'];
    if (isset($_SESSION['cart'][$code])) {
        unset($_SESSION['cart'][$code]);
    }
    header("Location: posCart.php");
    exit;
}
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>POS Cart</title>
    <link rel="stylesheet" href="./CSS/posCart.css">
</head>
<body>
    <div class="header-bar">
        NP RESTO
        <nav class="navbar">
            <a href="menu.php">Home</a>
            <a href="menu.php">Menu</a>
            <a href="reservation-panel.php">Table reservation</a>
            <a href="posTable.php">Table</a>
            <a href="posCart.php">Bill</a>
        </nav>
    </div>
    <div class="main-content">
        <div class="food-section">
            <h2>Food & Drinks</h2>
            <div class="search-row">
                <input type="text" class="search-box" placeholder="Search Food & Drinks">
                <button class="search-btn">Search</button>
                <button class="showall-btn">Show All</button>
            </div>
            <div class="food-table-wrapper">
                <table class="food-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Item Name</th>
                            <th>Price</th>
                            <th>Add</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        require_once '../config.php';
                        $result = $link->query("SELECT item_id, item_name, item_price FROM Menu");
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['item_id']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['item_name']) . "</td>";
                            echo "<td>" . number_format($row['item_price'], 2) . "</td>";
                            echo "<td>
                                <form method='post' action=''>
                                    <input type='hidden' name='item_code' value='" . htmlspecialchars($row['item_id']) . "'>
                                    <input type='number' min='1' max='1000' value='1' name='qty' class='qty-input'>
                                    <button type='submit' class='add-btn'>Add to Cart</button>
                                </form>
                            </td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="cart-section">
            <h2>Cart</h2>
            <form method="post" action="" style="margin-bottom:10px;">
                <button type="submit" name="clear_cart" class="add-btn" style="background:#dc3545; color:white;">Clear Cart</button>
            </form>
            <?php
            if (!empty($_SESSION['cart'])) {
                $codes = array_keys($_SESSION['cart']);
                // Prepare placeholders for SQL IN clause
                $placeholders = implode(',', array_fill(0, count($codes), '?'));
                $stmt = $link->prepare("SELECT item_id, item_name, item_price FROM Menu WHERE item_id IN ($placeholders)");
                $types = str_repeat('s', count($codes));
                $stmt->bind_param($types, ...$codes);
                $stmt->execute();
                $result = $stmt->get_result();
                $menu_items = [];
                while ($row = $result->fetch_assoc()) {
                    $menu_items[$row['item_id']] = $row;
                }
                $stmt->close();
                $grand_total = 0;
                echo '<table class="table"><thead><tr><th>Item</th><th>Qty</th><th>Price</th><th>Total</th><th>Action</th></tr></thead><tbody>';
                foreach ($_SESSION['cart'] as $code => $qty) {
                    if (isset($menu_items[$code])) {
                        $name = $menu_items[$code]['item_name'];
                        $price = $menu_items[$code]['item_price'];
                        $total = $qty * $price;
                        $grand_total += $total;
                        echo "<tr><td>" . htmlspecialchars($name) . "</td><td>$qty</td><td>" . number_format($price,2) . "</td><td>" . number_format($total,2) . "</td>";
                        echo "<td><form method='post' action='' style='display:inline'><input type='hidden' name='delete_code' value='" . htmlspecialchars($code) . "'><button type='submit' class='add-btn' style='background:#dc3545;color:white;'>Delete</button></form></td></tr>";
                    }
                }
                echo "<tr><td colspan=\"4\"><strong>Grand Total</strong></td><td><strong>" . number_format($grand_total,2) . "</strong></td></tr>";
                echo "</tbody></table>";
                // Payment button
                echo '<form method="get" action="payment.php" style="margin-top:10px;text-align:right;"><button type="submit" class="add-btn" style="background:#28a745;color:white;">Proceed to Payment</button></form>';
            } else {
                echo '<p>Your cart is empty.</p>';
            }
            ?>
        </div>
    </div>
</body>
</html>