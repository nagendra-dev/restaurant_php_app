<?php
require_once '../config.php';

$reservation_date = $_GET['reservation_date'] ?? null;
$reservation_time = $_GET['reservation_time'] ?? null;
$head_count = $_GET['head_count'] ?? 1;

if (!$reservation_date || !$reservation_time) {
    echo "Please select both a date and a time.";
    exit;
}

// Find tables already reserved for this date and time
$reserved_table_ids = [];
$res_query = "SELECT table_id FROM reservations WHERE reservation_date = ? AND reservation_time = ?";
$stmt = mysqli_prepare($link, $res_query);
mysqli_stmt_bind_param($stmt, "ss", $reservation_date, $reservation_time);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $table_id);
while (mysqli_stmt_fetch($stmt)) {
    $reserved_table_ids[] = $table_id;
}
mysqli_stmt_close($stmt);

// Find tables that can accommodate the head count and are not reserved
$placeholders = implode(',', array_fill(0, count($reserved_table_ids), '?'));
$sql = "SELECT table_id, capacity FROM table_availability WHERE is_available = 1 AND capacity >= ?";
if (count($reserved_table_ids) > 0) {
    $sql .= " AND table_id NOT IN ($placeholders)";
}
$stmt = mysqli_prepare($link, $sql);

if (count($reserved_table_ids) > 0) {
    $types = str_repeat('i', count($reserved_table_ids));
    mysqli_stmt_bind_param($stmt, "i" . $types, $head_count, ...$reserved_table_ids);
} else {
    mysqli_stmt_bind_param($stmt, "i", $head_count);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$available_tables = [];
while ($row = mysqli_fetch_assoc($result)) {
    $available_tables[] = $row;
}
mysqli_stmt_close($stmt);

mysqli_close($link);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Available Tables</title>
    <link rel="stylesheet" href="CSS/styles.css">
</head>
<body>
    <div class="wrapper">
        <h3>Available Tables for <?= htmlspecialchars($reservation_date) ?> at <?= htmlspecialchars($reservation_time) ?></h3>
        <?php if (count($available_tables) > 0): ?>
            <form method="get" action="createReservation.php">
                <input type="hidden" name="reservation_date" value="<?= htmlspecialchars($reservation_date) ?>">
                <input type="hidden" name="reservation_time" value="<?= htmlspecialchars($reservation_time) ?>">
                <input type="hidden" name="head_count" value="<?= htmlspecialchars($head_count) ?>">
                <label for="reserved_table_id">Select a Table:</label>
                <select name="reserved_table_id" id="reserved_table_id" required>
                    <?php foreach ($available_tables as $table): ?>
                        <option value="<?= $table['table_id'] ?>">
                            Table <?= $table['table_id'] ?> (Capacity: <?= $table['capacity'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <br><br>
                <button type="submit" class="btn btn-dark">Continue Reservation</button>
            </form>
        <?php else: ?>
            <p>No tables available for the selected time and head count. Please try another time or date.</p>
            <a href="createReservation.php">Back to Reservation</a>
        <?php endif; ?>
    </div>
</body>
</html>