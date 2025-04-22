<?php
session_start();
require_once "../config.php";
?>
<?php include './heading.php'; ?>

<link rel="stylesheet" href="CSS/menustyle.css">
<link rel="stylesheet" href="[https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"](https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css") crossorigin="anonymous" referrerpolicy="no-referrer" />

<div class="menucontainer">
    <h2 class="pull-left">Menu Panel</h2>
    <div class="additem">
        <a href="Menucard/createItem.php" class="btnitem"><i class="fa fa-plus"></i> Add Item</a>
    </div>
    <form method="POST">
        <div class="row">
            <select name="search" id="search" class="form-control">
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
        </div>
        <div class="colmd">
            <button type="submit" class="btn btn-dark">Search</button>
        </div>
    </form>

    <?php
    // Search logic
    if (isset($_POST['search']) && !empty($_POST['search'])) {
        $search = $_POST['search'];
        $sql = "SELECT * FROM menu WHERE item_type LIKE '%$search%' OR item_name LIKE '%$search%' OR item_id LIKE '%$search%' ORDER BY item_id;";
    } else {
        $sql = "SELECT * FROM menu ORDER BY item_id;";
    }

    if ($result = mysqli_query($link, $sql)) {
        if (mysqli_num_rows($result) > 0) {
            echo '<table class="table-body" border="1">';
            echo "<thead>";
            echo "<tr>";
            echo "<th>Item ID</th>";
            echo "<th>Name</th>";
            echo "<th>Type</th>";
            echo "<th>Price</th>";
            echo "<th>Edit</th>";
            echo "<th>Delete</th>";
            echo "</tr>";
            echo "</thead>";
            echo "<tbody>";
            while ($row = mysqli_fetch_array($result)) {
                echo "<tr>";
                echo "<td>" . $row['item_id'] . "</td>";
                echo "<td>" . ucfirst($row['item_name']) . "</td>";
                echo "<td>" . ucfirst($row['item_type']) . "</td>";
                echo "<td>" . $row['item_price'] . "</td>";
                echo "<td>";
                echo '<a class="icon" href="./Menucard/update.php?id='. $row['item_id'] .'" title="Modify Record" data-toggle="tooltip" onclick="return confirm(\'Admin permission Required!\n\nAre you sure you want to Edit this Item?\')"><i class="fa-solid fa-marker"></i></a>';
                echo "</td>";
                echo "<td>";
                echo '<a class="icon" href="./Menucard/deleteItem.php?id='. $row['item_id'] .'" title="Delete Record" data-toggle="tooltip" onclick="return confirm(\'Admin permission Required!\n\nAre you sure you want to delete this Item?\n\nThis will alter other modules related to this Item!\n\nYou see unwanted changes in bills.\')"><span class="fa fa-trash text-black"></span></a>';
                echo "</td>";
                echo "</tr>";
            }
            echo "</tbody>";
            echo "</table>";
            mysqli_free_result($result);
        } else {
            echo '<div class="alert alert-danger"><em>No records were found.</em></div>';
        }
    } else {
        echo "Oops! Something went wrong. Please try again later.";
    }
    mysqli_close($link);
    ?>
</div>