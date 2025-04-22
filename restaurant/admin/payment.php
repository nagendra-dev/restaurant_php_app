<?php
session_start();
require_once '../config.php';

// Simulate getting cart and bill info (in real use, fetch from DB or session)
$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
$menu_items = [];
$grand_total = 0;
$tax_rate = 0.10;

if (!empty($cart)) {
    $codes = array_keys($cart);
    $placeholders = implode(',', array_fill(0, count($codes), '?'));
    $stmt = $link->prepare("SELECT item_id, item_name, item_price FROM Menu WHERE item_id IN ($placeholders)");
    $types = str_repeat('s', count($codes));
    $stmt->bind_param($types, ...$codes);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $menu_items[$row['item_id']] = $row;
    }
    $stmt->close();
}

// Calculate totals
$total = 0;
foreach ($cart as $code => $qty) {
    if (isset($menu_items[$code])) {
        $price = $menu_items[$code]['item_price'];
        $total += $price * $qty;
    }
}
$tax = $total * $tax_rate;
$grand_total = $total + $tax;

// Handle payment logic
$paid = false;
$change = 0;
$payment_amount = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['payment_amount'])) {
    $payment_amount = floatval($_POST['payment_amount']);
    if ($payment_amount >= $grand_total) {
        $paid = true;
        $change = $payment_amount - $grand_total;
        // Optionally, clear cart/session here
        unset($_SESSION['cart']);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Bill (Cash Payment)</title>
    <link rel="stylesheet" href="./CSS/posCart.css">
    <style>
        .bill-box { margin: 30px auto; max-width: 600px; background: #fff; border-radius: 8px; box-shadow: 0 2px 8px #ccc; padding: 30px; }
        .bill-title { font-size: 1.4em; font-weight: bold; margin-bottom: 10px; }
        .bill-table { width: 100%; margin-bottom: 20px; border-collapse: collapse; }
        .bill-table th, .bill-table td { border: 1px solid #e0e0e0; padding: 8px 12px; text-align: center; }
        .bill-table th { background: #f8f8f8; }
        .summary-row td { font-weight: bold; }
        .cash-section { margin-top: 30px; }
        .success { background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 10px; }
        .pay-btn, .back-btn, .receipt-btn { padding: 8px 18px; border: none; border-radius: 4px; margin: 5px 3px; font-size: 1em; }
        .pay-btn { background: #007bff; color: #fff; }
        .back-btn { background: #343a40; color: #fff; }
        .receipt-btn { background: #28a745; color: #fff; }
        .change-box { background: #e2e3e5; color: #383d41; padding: 10px; border-radius: 5px; margin-bottom: 10px; }
    </style>
</head>
<body style="background:#f8f9fa;">
    <div class="bill-box">
        <div class="bill-title">Bill (Cash Payment)</div>
        <div style="margin-bottom:10px;">Bill ID: <b>6</b></div>
        <table class="bill-table">
            <thead>
                <tr><th>Item ID</th><th>Item Name</th><th>Price</th><th>Quantity</th><th>Total</th></tr>
            </thead>
            <tbody>
                <?php foreach ($cart as $code => $qty): if (isset($menu_items[$code])): ?>
                <tr>
                    <td><?= htmlspecialchars($code) ?></td>
                    <td><?= htmlspecialchars($menu_items[$code]['item_name']) ?></td>
                    <td>RM <?= number_format($menu_items[$code]['item_price'],2) ?></td>
                    <td><?= $qty ?></td>
                    <td>RM <?= number_format($menu_items[$code]['item_price'] * $qty,2) ?></td>
                </tr>
                <?php endif; endforeach; ?>
            </tbody>
        </table>
        <div style="text-align:right; margin-bottom:10px;">
            <div>Total: RM <?= number_format($total,2) ?></div>
            <div>Tax (10%): RM <?= number_format($tax,2) ?></div>
            <div><b>Grand Total: RM <?= number_format($grand_total,2) ?></b></div>
        </div>
        <form method="post" class="cash-section">
            <h2 style="font-size:1.2em;">Cash Payment</h2>
            <label>Payment Amount</label><br>
            <input type="number" name="payment_amount" min="0" step="0.01" value="<?= htmlspecialchars($payment_amount) ?>" required style="padding:5px 12px; width:180px; margin:10px 0;">
            <button type="submit" class="pay-btn">Pay</button>
        </form>
        <?php if ($paid): ?>
            <div class="success">Bill successfully Paid!</div>
            <div class="change-box">Change is RM<?= number_format($change,2) ?></div>
        <?php elseif ($payment_amount !== '' && !$paid): ?>
            <div class="change-box" style="background:#f8d7da; color:#721c24;">Insufficient payment.</div>
        <?php endif; ?>
        <div style="margin-top:10px;">
            <a href="menu.php" class="back-btn">Back</a>
            <a href="#" onclick="window.print()" class="receipt-btn">Print Receipt &#128438;</a>
        </div>
    </div>
</body>
</html>
