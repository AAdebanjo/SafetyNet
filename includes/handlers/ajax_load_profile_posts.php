<?php 
include("../../config/config.php");
include("../classes/User.php");
include("../classes/Post.php");

$limit = 10; //number of posts to be called per call

$posts = new Post($con, $_REQUEST['userLoggedIn']);
$posts->loadProfilePosts($_REQUEST, $limit);
?>