<?php
require_once '../config.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_table'])) {
    $table_id = trim($_POST['table_id']);
    $capacity = trim($_POST['capacity']);
    $is_available = isset($_POST['is_available']) ? 1 : 0;
    if ($table_id && $capacity) {
        // Check for duplicate table_id
        $check = $link->prepare("SELECT table_id FROM table_availability WHERE table_id = ?");
        $check->bind_param('i', $table_id);
        $check->execute();
        $check->store_result();
        if ($check->num_rows > 0) {
            $error = "Table ID already exists!";
        } else {
            $stmt = $link->prepare("INSERT INTO table_availability (table_id, capacity, is_available) VALUES (?, ?, ?)");
            $stmt->bind_param('isi', $table_id, $capacity, $is_available);
            $stmt->execute();
            $stmt->close();
            header("Location: table_update.php");
            exit;
        }
        $check->close();
    }
}
// Handle delete table
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_table_id'])) {
    $del_id = $_POST['delete_table_id'];
    $stmt = $link->prepare("DELETE FROM table_availability WHERE table_id = ?");
    $stmt->bind_param('i', $del_id);
    $stmt->execute();
    $stmt->close();
    header("Location: table_update.php");
    exit;
}
// Handle search
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$where = '';
if ($search !== '') {
    $where = "WHERE table_id LIKE CONCAT('%', ?, '%') OR capacity LIKE CONCAT('%', ?, '%')";
}

// Fetch tables
$sql = "SELECT * FROM table_availability $where ORDER BY table_id";
$stmt = $link->prepare($sql);
if ($where) {
    $stmt->bind_param('ss', $search, $search);
}
$stmt->execute();
$result = $stmt->get_result();
$tables = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Table Panel</title>
    <style>
        body { background: #f8f9fa; margin: 0; font-family: Arial, sans-serif; }
        .header-bar { background: #000; color: #fff; padding: 20px 0 10px 0; }
        .header-bar nav { float: right; margin-right: 40px; }
        .header-bar nav a { color: #fff; margin: 0 12px; text-decoration: none; font-weight: 500; }
        .header-bar h1 { margin: 0 0 0 40px; display: inline-block; font-size: 2em; letter-spacing: 2px; }
        .main-content { max-width: 800px; margin: 30px auto; background: #fff; border-radius: 8px; box-shadow: 0 2px 8px #ccc; padding: 30px 40px; }
        h2 { margin-bottom: 18px; font-size: 1.2em; }
        .search-row { margin-bottom: 20px; }
        .search-row input[type='text'] { padding: 6px 10px; width: 250px; border-radius: 4px; border: 1px solid #888; }
        .search-row button { padding: 6px 16px; background: #234; color: #fff; border: none; border-radius: 4px; margin-left: 8px; }
        .add-btn { padding: 6px 16px; background: #234; color: #fff; border: none; border-radius: 4px; margin-bottom: 10px; }
        .table-wrapper { overflow-x: auto; }
        table { border-collapse: collapse; width: 100%; margin-top: 12px; }
        th, td { border: 1px solid #222; padding: 8px 12px; text-align: center; }
        th { background: #eee; }
        .delete-btn { background: #dc3545; color: #fff; border: none; border-radius: 4px; padding: 4px 10px; cursor: pointer; }
        .delete-btn:hover { background: #a71d2a; }
        /* Modal styles */
        .modal-bg { display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.4); z-index:1000; }
        .modal { background:#fff; border-radius:8px; box-shadow:0 2px 8px #222; max-width:350px; padding:24px 28px; position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); }
        .modal input[type='text'], .modal input[type='number'] { width:100%; padding:6px 10px; margin-bottom:12px; border-radius:4px; border:1px solid #888; }
        .modal label { font-weight:600; }
        .modal-actions { text-align:right; }
        .modal-actions button { margin-left:8px; }
        .modal-error { color:red; margin-bottom:10px; }
    </style>
</head>
<body>
    <div class="header-bar">
        <h1>NP RESTO</h1>
        <nav>
            <a href="menu.php">Home</a>
            <a href="menu.php">Menu</a>
            <a href="reservation-panel.php">Table reservation</a>
            <a href="table_update.php">Table</a>
            <a href="bill.php">Bill</a>
        </nav>
    </div>
    <div class="main-content">
        <h2>Table Details</h2>
        <button class="add-btn" onclick="document.getElementById('modal-bg').style.display='block'">+ Add Table</button>
        <form class="search-row" method="get" action="">
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Enter Table ID, Capacity">
            <button type="submit">Search</button>
        </form>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Table ID</th>
                        <th>Capacity</th>
                        <th>Available</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($tables)): ?>
                        <?php foreach ($tables as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['table_id']) ?></td>
                            <td><?= htmlspecialchars($row['capacity']) ?> Persons</td>
                            <td><?= $row['is_available'] ? 'Yes' : 'No' ?></td>
                            <td>
                                <form method="post" action="" style="display:inline">
                                    <input type="hidden" name="delete_table_id" value="<?= htmlspecialchars($row['table_id']) ?>">
                                    <button type="submit" class="delete-btn">&#128465;</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="4">No tables found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <!-- Modal for Add Table -->
    <div class="modal-bg" id="modal-bg">
        <div class="modal">
            <?php if (!empty($error)): ?>
                <div class="modal-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form method="post" action="">
                <label>Table ID</label>
                <input type="number" name="table_id" min="1" required>
                <label>Capacity</label>
                <input type="number" name="capacity" min="1" max="20" required>
                <label><input type="checkbox" name="is_available" checked> Available</label>
                <div class="modal-actions">
                    <button type="button" onclick="document.getElementById('modal-bg').style.display='none'">Cancel</button>
                    <button type="submit" name="add_table" class="add-btn">Add</button>
                </div>
            </form>
        </div>
    </div>
    <script>
        // Close modal when clicking outside
        document.getElementById('modal-bg').onclick = function(e) {
            if (e.target === this) this.style.display = 'none';
        };
        // If error, show modal automatically
        <?php if (!empty($error)): ?>
        document.getElementById('modal-bg').style.display = 'block';
        <?php endif; ?>
    </script>
</body>
</html>
