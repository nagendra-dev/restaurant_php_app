<?php
session_start();
require_once '../config.php'; // Adjust path if needed

// Check if form submitted via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect and sanitize form inputs
    $customer_name = trim($_POST['customer_name'] ?? '');
    $reservation_date = $_POST['reservation_date'] ?? '';
    $reservation_time = $_POST['reservation_time'] ?? '';
    $head_count = intval($_POST['head_count'] ?? 1);
    $table_id = intval($_POST['table_id'] ?? 0);

    // Basic validation
    if (empty($customer_name) || empty($reservation_date) || empty($reservation_time) || $head_count < 1 || $table_id < 1) {
        die('Missing or invalid reservation details.');
    }

    // Prepare SQL statement
    $stmt = $link->prepare("INSERT INTO reservations (customer_name, reservation_date, reservation_time, head_count, table_id) VALUES (?, ?, ?, ?, ?)");
    if (!$stmt) {
        die('Prepare failed: ' . $link->error);
    }
    $stmt->bind_param("sssii", $customer_name, $reservation_date, $reservation_time, $head_count, $table_id);

    // Execute and check result
    if ($stmt->execute()) {
        // Success: show message and button to go to menu
        echo "<h2>Reservation successful!</h2>";
        echo '<a href="reservation-panel.php" class="btn btn-primary">Go to Menu</a>'; // Adjust path if needed
        exit;
    } else {
        // Failure: show error
        die('Reservation failed: ' . $stmt->error);
    }
} else {
    die('Invalid request method.');
}
?>