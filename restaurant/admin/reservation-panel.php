<?php 
session_start(); // Ensure session is started 
//require_once '../posBackend/checkIfLoggedIn.php'; 
?> 
<script src='https://kit.fontawesome.com/a076d05399.js' crossorigin='anonymous'></script> 
 <link rel="stylesheet" href="./CSS/reservationstyle.css"> 
 <link rel="stylesheet" href="./CSS/menustyle.css"> 
 <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font
awesome/6.5.2/css/all.min.css" integrity="sha512
SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV
 7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" /> 
<?php 
include './heading.php'; 
 
?>
<html>                  
    <style> 
        .wrapper{ width: 1300px; padding-left: 200px; padding-top: 20px  } 
    </style> 
 
                <div class="rescontainer"> 
                <h2 class="pull-left">Reservation Details</h2> 
                 <div class="additem"> 
                <br/>
                <a href="createReservation.php" class="btnitem">Add Reservation</a> 
                </div> 
                    <form method="POST" action="#"> 
                        <div class="row">
                                <input required type="text" id="search" name="search" class="form
control" placeholder="Enter Reservation ID, Customer Name, Reservation Date (2023
09)"></div> 
                            <div class="colmd"> 
                                <button type="submit" class="btn btn-dark">Search</button> 
</div> 
                            <div class="col" style="text-align: right;" > 
                                <a href="reservation-panel.php" class="btn">Show All</a> 
                            </div> 
                         
                    </form> 
                 
                 
                <?php 
                // Include config file 
                require_once "../config.php"; 
                $sql = "SELECT * FROM reservations ORDER BY reservation_id;"; 
 
 
                if (isset($_POST['search'])) { 
                    if (!empty($_POST['search'])) { 
                        $search = $_POST['search']; 
 
                        $sql = "SELECT * FROM reservations WHERE  reservation_id LIKE 
'%$search%' OR customer_name LIKE '%$search%'"; 
                    } else { 
                        // Default query to fetch all reservations 
                        $sql = "SELECT * FROM reservations ORDER BY reservation_date DESC, 
reservation_time DESC;"; 
                    } 
                } else{ 
                    $sql = "SELECT * FROM reservations ORDER BY reservation_date DESC, 
reservation_time DESC;"; 
 
                } 
                 
                if ($result = mysqli_query($link, $sql)) { 
                    if (mysqli_num_rows($result) > 0) { 
                        echo '<table  border="1" class="table-body">'; 
                        echo "<thead>"; 
                        echo "<tr>"; 
                        echo "<th>Reservation ID</th>"; 
                        echo "<th>Customer Name</th>"; 
                        echo "<th>Table ID</th>"; 
                        echo "<th>Reservation Time</th>"; 
                        echo "<th>Reservation Date</th>"; 
                        echo "<th>Head Count</th>"; 
                        echo "<th>Special Request</th>"; 
                        echo "<th>Delete</th>";
                        echo "<th>Receipt</th>"; 
                        echo "</tr>"; 
                        echo "</thead>"; 
                        echo "<tbody>"; 
                        while ($row = mysqli_fetch_array($result)) { 
                            echo "<tr>"; 
                            echo "<td>" . $row['reservation_id'] . "</td>"; 
                            echo "<td>" . $row['customer_name'] . "</td>"; 
                            echo "<td>" . $row['table_id'] . "</td>"; 
                            echo "<td>" . $row['reservation_time'] . "</td>"; 
                            echo "<td>" . $row['reservation_date'] . "</td>"; 
                            echo "<td>" . $row['head_count'] . "</td>"; 
                            echo "<td>" . $row['special_request'] . "</td>"; 
                            echo "<td>"; 
                            echo '<a class="icon" href="../admin/deleteReservation.php?id='. 
$row['reservation_id'] .'" title="Delete Record" data-toggle="tooltip" ' 
                                   . 'onclick="return confirm(\'Admin permission Required!\n\nAre you 
sure you want to delete this Reservation?\n\nThis will alter other modules related to this 
Reservation!\n\')"><span class="fa fa-trash text-black"></span></a>'; 
                            echo "</td>"; 
                            echo "<td>"; 
                            echo '<a 
class="icon"href="..//admin/reservationReceipt.php?reservation_id='. $row['reservation_id'] 
.'" title="Receipt" data-toggle="tooltip"><span class="fa fa-receipt text-black"></span></a>'; 
                            echo "</td>"; 
                            echo "</tr>"; 
                        } 
                        echo "</tbody>"; 
                        echo "</table>"; 
                    } else { 
                        echo '<div class="alert alert-danger"><em>No records were 
found.</em></div>'; 
 
                    } 
                } else { 
                    echo "Oops! Something went wrong. Please try again later."; 
                } 
 
                // Close connection 
                mysqli_close($link); 
                ?>
                </div> 
            </div> 
        </div> 
    </div> 
</body> 
</html>     