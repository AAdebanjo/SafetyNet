<?php 
require '../../config/config.php';

if(isset($_GET['post_id'])) {
	$post_id = $_GET['post_id'];
}

	if(isset($_POST['result'])) { //they've answered the box - the confirmaiton box appears
	if($_POST['result'] == 'true') {
		$query = mysqli_query($con, "UPDATE posts SET IsDeleted='yes' WHERE PostID='$post_id'");
	}
}


?>