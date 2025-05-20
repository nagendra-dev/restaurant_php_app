<html>
<head>
    <link rel="stylesheet" href="CSS/headingstyle.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" 
      integrity="sha512-Fo3rlrZj/k7ujTnHg4CGR2D7kSs0v4LLanw2qksYuRlEzO+tcaEPQogQ0KaoGN26/zrn20ImR1DfuLWnOo7aBA==" 
      crossorigin="anonymous" 
      referrerpolicy="no-referrer" />
</head>
<body>
<div class="top">
    <div class="navbar-brand">NP RESTO</div>
    <div class="navi">
    <?php
    session_start();
    $role = isset($_SESSION['role']) ? $_SESSION['role'] : 'user';
    if ($role === 'admin') {
        // Admin menu
        echo '<a href="home.php" class="homelink">Home</a>';
        echo '<a href="menu.php" class="homelink">Menu</a>';
        echo '<a href="reservation-panel.php" class="homelink">Table reservation</a>';
        echo '<a href="table_update.php" class="homelink">Table</a>';
        echo '<a href="posCart.php" class="homelink">Bill</a>';
    } else {
        // Normal user menu
        echo '<a href="home.php" class="homelink">Home</a>';
        echo '<a href="../order_food.php" class="homelink">Place Order</a>';
    }
    ?>
</div>
    <div class="menu-toggle">
        <button onClick="toggleForm('homeform')" class="fa-solid fa-bars"></button>
        <div id="homeform" style="display:none;">
            <a href="../index.php" class="homelink">logout</a>
        </div>
    </div>
</div>
<script>
function toggleForm(formId) {
    var form = document.getElementById(formId);
    if (form.style.display === "none" || form.style.display === "") {
        form.style.display = "block";
    } else {
        form.style.display = "none";
    }
}
</script>
</body>
</html>