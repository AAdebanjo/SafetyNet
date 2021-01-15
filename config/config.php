<?php
ob_start();
if( !headers_sent() && '' == session_id() ) {
    session_start();
}
$timezone = date_default_timezone_set("Europe/London");
$con = mysqli_connect("localhost:3306", "safetzb6_login", "Flo_And_Deb", "safetzb6_demo"); //Connection variable
if(mysqli_connect_errno()) 
{
	echo "Unable to connect: " . mysqli_connect_errno();
}
?>