<?php 
include("../../config/config.php"); //database configuration file that allows us to access the database
include("../classes/User.php");
include("../classes/Message.php");

$limit = 5; //number of messages to load 

$message = new Message($con, $_REQUEST['userLoggedIn']);
echo $message->getConversationsDropdown($_REQUEST, $limit);
?>