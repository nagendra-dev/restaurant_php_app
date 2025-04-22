<?php
define('DB_HOST','localhost');
define('DB_USER','root');
define('DB_PASS','');
define('DB_NAME','resto');
//Create Connection
$link = new mysqli(DB_HOST,DB_USER,DB_PASS,DB_NAME);
//Check Connection
if($link->connect_error){
die('Connection Failed'.$link->connect_error);
}
?>