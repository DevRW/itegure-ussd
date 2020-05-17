<?php
// *************** USSD DATABASE CONFIGURATION ******************
$server="localhost";
$user="";
$pass="";
$db="";
$backend_url="";
$conn = new mysqli($server, $user, $pass, $db);
if ($conn->connect_error) {
	die("Connection failed: " . $conn->connect_error);
}
// *************** END USSD DATABASE CONFIGURATION ***************

require("functions.php")
?>