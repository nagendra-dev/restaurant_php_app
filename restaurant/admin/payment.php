<?php
session_start();
require_once '../config.php';

// Common variables for both modes
$page_error_message = '';
$page_success_message = '';
$paid_this_request = false;
$change_this_request = 0;
$payment_amount_entered = '';
$current_grand_total = 0; 
$payment_mode = ''; // 'order' or 'session_cart'

// Determine payment mode
$order_id_from_url = isset($_GET['order_id']) ? intval($_GET['order_id']) : null;

// Order-specific variables (only used if $payment_mode === 'order')
$items_ordered_str = '';
$order_total_price = 0;
$order_username = '';
$order_is_paid = true; // Default to true for safety, set to false if order is found and unpaid

// Session cart-specific variables (only used if $payment_mode === 'session_cart')
$session_cart_items_details = [];
$session_cart_total = 0;

if ($order_id_from_url) {
    $payment_mode = 'order';
    
    $stmt_order = $link->prepare("SELECT username, items_ordered, total_price, is_paid FROM orders WHERE order_id = ?");
    if ($stmt_order) {
        $stmt_order->bind_param("i", $order_id_from_url);
        $stmt_order->execute();
        $result_order = $stmt_order->get_result();
        if ($order_data = $result_order->fetch_assoc()) {
            if ($order_data['is_paid']) {
                $page_error_message = "Order #{$order_id_from_url} has already been paid.";
                $order_is_paid = true;
            } else {
                $order_username = $order_data['username'];
                $items_ordered_str = $order_data['items_ordered'];
                $order_total_price = $order_data['total_price'];
                $order_is_paid = false; 
                $current_grand_total = $order_total_price;
            }
        } else {
            $page_error_message = "Order #{$order_id_from_url} not found.";
        }
        $stmt_order->close();
    } else {
        $page_error_message = "Database error: Could not prepare to fetch order details.";
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['payment_amount'], $_POST['order_id'])) {
        $order_id_posted = intval($_POST['order_id']);
        $payment_amount_entered = floatval($_POST['payment_amount']);

        if ($order_id_posted == $order_id_from_url && !$order_is_paid) { 
            if ($payment_amount_entered >= $order_total_price) {
                $stmt_update_order = $link->prepare("UPDATE orders SET is_paid = TRUE WHERE order_id = ?");
                if ($stmt_update_order) {
                    $stmt_update_order->bind_param("i", $order_id_posted);
                    if ($stmt_update_order->execute()) {
                        $paid_this_request = true;
                        $change_this_request = $payment_amount_entered - $order_total_price;
                        $page_success_message = "Bill for Order #{$order_id_posted} successfully Paid!";
                        $order_is_paid = true; 
                    } else {
                        $page_error_message = "Payment processed, but failed to update order status. Please contact support.";
                    }
                    $stmt_update_order->close();
                } else {
                    $page_error_message = "Database error: Could not prepare to update order status.";
                }
            } else {
                $page_error_message = "Insufficient payment amount.";
            }
        } else if ($order_is_paid && $order_id_posted == $order_id_from_url) {
            $page_error_message = "This order (#{$order_id_posted}) has already been marked as paid.";
        } else if ($order_id_posted != $order_id_from_url) {
             $page_error_message = "Order ID mismatch during payment processing.";
        }
    }
} else { // No order_id in URL, so assume admin POS session cart mode
    $payment_mode = 'session_cart';
    $session_cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];

    if (!empty($session_cart)) {
        $item_codes = array_keys($session_cart);
        $placeholders = implode(',', array_fill(0, count($item_codes), '?'));
        $stmt_cart = $link->prepare("SELECT item_id, item_name, item_price FROM Menu WHERE item_id IN ($placeholders)");
        if ($stmt_cart) {
            $types = str_repeat('s', count($item_codes));
            $stmt_cart->bind_param($types, ...$item_codes);
            $stmt_cart->execute();
            $result_cart = $stmt_cart->get_result();
            while ($row = $result_cart->fetch_assoc()) {
                $item_id = $row['item_id'];
                if(isset($session_cart[$item_id])) { // Ensure item is still in cart (robustness)
                    $qty_in_cart = $session_cart[$item_id];
                    $session_cart_items_details[] = [
                        'id' => $item_id,
                        'name' => $row['item_name'],
                        'price' => $row['item_price'],
                        'qty' => $qty_in_cart,
                        'subtotal' => $row['item_price'] * $qty_in_cart
                    ];
                    $session_cart_total += ($row['item_price'] * $qty_in_cart);
                }
            }
            $stmt_cart->close();
            $current_grand_total = $session_cart_total;
        } else {
            $page_error_message = "Database error preparing to fetch cart item details.";
        }
    } else {
        if (!isset($_POST['payment_amount'])) { // Only show if not a payment attempt for an empty cart
             $page_error_message = "Admin cart is empty. Please add items via POS.";
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['payment_amount']) && !isset($_POST['order_id'])) {
        $payment_amount_entered = floatval($_POST['payment_amount']);
        if (empty($session_cart)) {
            $page_error_message = "Cannot process payment for an empty cart.";
        } elseif ($payment_amount_entered >= $session_cart_total) {
            $paid_this_request = true;
            $change_this_request = $payment_amount_entered - $session_cart_total;
            $page_success_message = "Admin POS Cart payment successful!";
            unset($_SESSION['cart']); 
            // $session_cart_items_details = []; // Clear details for display after payment
            // $current_grand_total = 0;
        } else {
            $page_error_message = "Insufficient payment amount for the cart.";
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Bill (Cash Payment)<?php 
        if ($payment_mode === 'order' && $order_id_from_url) { 
            echo " - Order #" . htmlspecialchars($order_id_from_url); 
        } elseif ($payment_mode === 'session_cart') { 
            echo " - Admin POS Sale"; 
        }
    ?></title>
    <link rel="stylesheet" href="./CSS/posCart.css">
    <style>
        body { background:#f8f9fa; font-family: Arial, sans-serif; }
        .bill-box { margin: 30px auto; max-width: 600px; background: #fff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); padding: 30px; }
        .bill-title { font-size: 1.6em; font-weight: bold; margin-bottom: 15px; color: #333; }
        .order-details-box { border: 1px solid #eee; padding: 15px; margin-bottom: 20px; border-radius: 5px; background-color: #f9f9f9;}
        .order-details-box p { margin: 5px 0; font-size: 1em; }
        .order-details-box strong { color: #555; }
        .order-details-box table th, .order-details-box table td { padding:8px 5px; border-bottom: 1px dashed #eee; }
        .order-details-box table th { text-align:left; background-color: #f0f0f0;}
        .order-details-box table tr:last-child td { border-bottom:none; }
        .summary-total { text-align:right; margin-bottom:20px; font-size: 1.2em; font-weight: bold; }
        .cash-section { margin-top: 20px; }
        .message-area div { padding: 12px; border-radius: 5px; margin-bottom: 15px; font-size: 1em; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb;}
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .pay-btn, .back-btn, .receipt-btn { padding: 10px 20px; border: none; border-radius: 4px; margin: 5px 3px; font-size: 1em; cursor:pointer; text-decoration: none; display: inline-block;}
        .pay-btn { background: #007bff; color: #fff; }
        .pay-btn:hover { background: #0056b3; }
        .back-btn { background: #6c757d; color: #fff; }
        .back-btn:hover { background: #545b62; }
        .receipt-btn { background: #28a745; color: #fff; }
        .receipt-btn:hover { background: #1e7e34; }
        .change-box { background: #e2e3e5; color: #383d41; padding: 10px; border-radius: 5px; margin-bottom: 10px; text-align: center; font-weight:bold;}
        input[type="number"] { padding:8px 12px; width:180px; margin:10px 0; border:1px solid #ccc; border-radius:4px;}
    </style>
</head>
<body>
    <div class="bill-box">
        <div class="bill-title">Bill (Cash Payment)</div>

        <div class="message-area">
            <?php if (!empty($page_error_message)): ?>
                <div class="error"><?= htmlspecialchars($page_error_message) ?></div>
            <?php endif; ?>
            <?php if (!empty($page_success_message)): ?>
                <div class="success"><?= htmlspecialchars($page_success_message) ?></div>
            <?php endif; ?>
        </div>

        <?php if ($payment_mode === 'order'): ?>
            <?php if ($order_id_from_url && (empty($page_error_message) || $paid_this_request || $order_is_paid)): ?>
                <div style="margin-bottom:10px;">Order ID: <b><?= htmlspecialchars($order_id_from_url) ?></b></div>
                <?php if (!empty($order_username) && $order_username !== 'guest'): ?>
                     <div style="margin-bottom:10px;">Customer: <b><?= htmlspecialchars($order_username) ?></b></div>
                <?php endif; ?>
                <div class="order-details-box">
                    <p><strong>Items Ordered:</strong></p>
                    <p><?= nl2br(htmlspecialchars($items_ordered_str)) ?></p>
                </div>
            <?php endif; ?>
        <?php elseif ($payment_mode === 'session_cart'): ?>
            <?php if (!empty($session_cart_items_details) && !$paid_this_request): ?>
                <div class="order-details-box">
                    <p style="margin-bottom:10px;"><strong>Admin Cart Items:</strong></p>
                    <table style="width:100%; border-collapse: collapse;">
                        <thead><tr><th>Item</th><th style="text-align:right;">Qty</th><th style="text-align:right;">Price</th><th style="text-align:right;">Total</th></tr></thead>
                        <tbody>
                        <?php foreach ($session_cart_items_details as $item): ?>
                            <tr>
                                <td><?= htmlspecialchars($item['name']) ?></td>
                                <td style="text-align:right;"><?= $item['qty'] ?></td>
                                <td style="text-align:right;">RM <?= number_format($item['price'], 2) ?></td>
                                <td style="text-align:right;">RM <?= number_format($item['subtotal'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php elseif (empty($session_cart_items_details) && !$paid_this_request && empty($page_error_message) && empty($page_success_message) ): ?>
                <p>Admin Cart is empty. Add items via POS.</p>
            <?php endif; ?>
        <?php endif; ?>

        <?php 
        $show_total_and_form = false;
        if ($payment_mode === 'order' && !$order_is_paid && !$paid_this_request) {
            $show_total_and_form = true;
        } elseif ($payment_mode === 'session_cart' && !empty($session_cart_items_details) && !$paid_this_request) {
            $show_total_and_form = true;
        } elseif ($payment_mode === 'order' && $order_is_paid && !$paid_this_request && !empty($page_error_message)) {
             // This case is for when order is already paid but we still want to show its total if an error message is also present.
             // However, the form should not show.
             // No, if order is_paid, $page_error_message is "Order already paid", total shown via $current_grand_total check below.
        }
        
        // Show grand total if it's calculated, or if it's an order that's being displayed (even if paid)
        if ($current_grand_total > 0 || ($payment_mode === 'order' && $order_id_from_url)):
        ?>
        <div class="summary-total">
            Grand Total: RM <?= number_format($current_grand_total, 2) ?>
        </div>
        <?php endif; ?>
        
        <?php if ($show_total_and_form): ?>
        <form method="post" class="cash-section" id="paymentForm">
            <h2 style="font-size:1.2em;">Cash Payment</h2>
            <?php if ($payment_mode === 'order'): ?>
            <input type="hidden" name="order_id" value="<?= htmlspecialchars($order_id_from_url) ?>">
            <?php endif; ?>
            <label for="payment_amount_id">Payment Amount (RM)</label><br>
            <input type="number" id="payment_amount_id" name="payment_amount" min="0" step="0.01" value="<?= htmlspecialchars($payment_amount_entered) ?>" required>
            <div id="paymentInputError" style="color: red; font-size: 0.9em; height: 1.2em; margin-top: 3px;"></div>
            <button type="submit" id="payButton" class="pay-btn">Pay</button>
        </form>
        <?php endif; ?>

        <?php if ($paid_this_request): ?>
            <div class="change-box">Change is RM<?= number_format($change_this_request, 2) ?></div>
        <?php endif; ?>

        <div style="margin-top:20px; text-align:center;">
            <?php
            $back_link_href = '#'; // Default
            $is_admin_logged_in = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'); // Assuming 'admin' is the specific role name

            if ($payment_mode === 'order') {
                $back_link_href = $is_admin_logged_in ? 'menu.php' : '../order_food.php'; 
            } elseif ($payment_mode === 'session_cart') {
                $back_link_href = 'posCart.php'; // Admin back from POS payment to POS cart
            } else { // Fallback if payment_mode is somehow not set but page is rendered
                $back_link_href = $is_admin_logged_in ? 'menu.php' : '../home.php';
            }
            echo '<a href="' . htmlspecialchars($back_link_href) . '" class="back-btn">Back</a>';
            
            $can_print_receipt = false;
            if ($payment_mode === 'order' && ($order_is_paid || $paid_this_request)) {
                $can_print_receipt = true;
            } elseif ($payment_mode === 'session_cart' && $paid_this_request) {
                $can_print_receipt = true;
            }
            ?>
            <?php if ($can_print_receipt): ?>
            <a href="#" onclick="window.print()" class="receipt-btn">Print Receipt &#128438;</a>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($show_total_and_form): // JS should only be active if payment form is shown ?>
    <script>
        const paymentAmountInput = document.getElementById('payment_amount_id');
        const payButton = document.getElementById('payButton');
        const paymentForm = document.getElementById('paymentForm');
        const paymentWarningElement = document.getElementById('paymentInputError');
        const orderTotalForJs = parseFloat(<?= $current_grand_total ?>) || 0;

        function showPaymentWarning(message) {
            if (paymentWarningElement) {
                paymentWarningElement.textContent = message;
            }
        }

        function clearPaymentWarning() {
            if (paymentWarningElement) {
                paymentWarningElement.textContent = '';
            }
        }

        function validateAmountOnInput() {
            const enteredAmount = parseFloat(paymentAmountInput.value) || 0;
            if (enteredAmount > 0 && enteredAmount < orderTotalForJs) {
                showPaymentWarning('Amount is less than total (RM ' + orderTotalForJs.toFixed(2) + ')');
            } else {
                clearPaymentWarning();
            }
        }

        if (paymentAmountInput && payButton && paymentForm && paymentWarningElement) {
            paymentAmountInput.addEventListener('input', validateAmountOnInput);
            payButton.addEventListener('click', function(event) {
                const enteredAmount = parseFloat(paymentAmountInput.value) || 0;
                if (enteredAmount < orderTotalForJs) {
                    event.preventDefault();
                    showPaymentWarning('Insufficient payment. Minimum RM ' + orderTotalForJs.toFixed(2) + ' required.');
                } else {
                    clearPaymentWarning();
                }
            });
            validateAmountOnInput(); 
        }
    </script>
    <?php endif; ?>
</body>
</html>
