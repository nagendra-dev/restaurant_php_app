<?php
// Connect to your database
include '../config.php'; // Adjust path as needed

// Query all tables and their availability
$query = "SELECT table_id, capacity, is_available FROM restaurant_tables ORDER BY table_id ASC";
$result = mysqli_query($link, $query);

$tables = [];
while ($row = mysqli_fetch_assoc($result)) {
    $tables[] = $row;
}

// Status color mapping based on is_available
// 1 = Available, 0 = Occupied/Unavailable
$status_classes = [
    1 => 'table-available',
    0 => 'table-occupied'
];
$status_labels = [
    1 => 'Available',
    0 => 'Occupied'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>POS Table View</title>
    <link rel="stylesheet" href="./CSS/posTable.css">
</head>
<body>
    <div class="header-bar">
        NP RESTO
        <nav class="navbar">
            <a href="menu.php">Home</a>
            <a href="menu.php">Menu</a>
            <a href="reservation-panel.php">Table reservation</a>
            <a href="posTable.php">Table</a>
            <a href="bill.php">Bill</a>
        </nav>
    </div>
    <div class="table-layout">
        <?php foreach ($tables as $table): 
            $is_available = (int)$table['is_available'];
            $class = isset($status_classes[$is_available]) ? $status_classes[$is_available] : 'table-available';
        ?>
            <div class="table-card <?= $class ?>">
                Table: <?= $table['table_id'] ?><br>
                Capacity: <?= $table['capacity'] ?><br>
                <span class="table-status"><?= $status_labels[$is_available] ?></span>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="legend">
        <div class="legend-item table-available">Available</div>
        <div class="legend-item table-occupied">Occupied</div>
    </div>
</body>
</html>