<?php 
include("../../config/config.php"); //database configuration file that allows us to access the database
include("../classes/User.php");
include("../classes/Notification.php");

$limit = 5; //number of messages to load 

$notification = new Notification($con, $_REQUEST['userLoggedIn']);
echo $notification->getNotificationsDropdown($_REQUEST, $limit);
?>