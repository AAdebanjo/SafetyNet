<?php
require 'config/config.php';
include("includes/classes/User.php");
include("includes/classes/Post.php");
include("includes/classes/Notification.php");


if(isset($_SESSION['username'])) {
	$userLoggedIn = $_SESSION['username']; //if the session variable is set, make the session variable equal to that
	//username will be accessible anywhere
	//if not set, the user is not logged in
	$user_details_query = mysqli_query($con, "SELECT * FROM users WHERE UserName='$userLoggedIn'");
	$user = mysqli_fetch_array($user_details_query); //returns all the values for this user
}

else {
	header("Location: registration.php"); //takes the user back to the register page if they are not logged in
}

//$query = mysqli_query($con, "INSERT INTO test VALUES(NULL, 'Homer')"); //have to specify the connection variable - first parameter is the connection to the database used to execute query
//can use single quotes or null
//auto-increment

?>

<!DOCTYPE html>
<html>
<head>
	<title></title>
	<link rel="stylesheet" type="text/css" href="assets/css/style.css">
</head>
<body>

	<style type="text/css">
		* {
			font-size: 12px;
			font-family: Arial, Helvetica, sans-serif;
		}
	</style>



	<script>
		function toggle() {
			var element = document.getElementById("comment_section");

			if(element.style.display == "block") {
				element.style.display = "none";
			} else {
			element.style.display = "block"; //if it's not showing, show it
		}
	}
</script>


<?php
//Get id of post
if(isset($_GET['post_id'])) {
	$post_id = $_GET['post_id'];
	
}

$user_query = mysqli_query($con, "SELECT AddedBy, UserTo FROM posts WHERE PostID='$post_id'");
$row = mysqli_fetch_array($user_query);

$posted_to = $row['AddedBy'];
$user_to = $row['UserTo'];


if(isset($_POST['postComment' . $post_id])) {
	$post_body = $_POST['post_body'];
	$post_body = mysqli_escape_string($con, $post_body);
	$date_time_now = date("Y-m-d H:i:s");
	$insert_post = mysqli_query($con, "INSERT INTO comments VALUES('', '$post_body', '$userLoggedIn', '$posted_to', '$date_time_now', 'no', '$post_id')");

	if($posted_to != $userLoggedIn) {
		$notification = new Notification($con, $userLoggedIn);
		$notification->insertNotification($post_id, $posted_to, "comment");
	}

	if($user_to != 'none' && $user_to != $userLoggedIn) { //if you're posting on somebody's profile post
	$notification = new Notification($con, $userLoggedIn);
	$notification->insertNotification($post_id, $user_to, "profile_comment");
}

$get_commenters = mysqli_query($con, "SELECT * FROM comments WHERE PostID='$post_id'");

	//notifiy everyone that has commented on a post that someone else has commented on
	$notified_users = array(); //selects all of the users that have commented on a particular post

	while($row = mysqli_fetch_array($get_commenters)) { //iterate through all the comments on the post

		if($row['PostedBy'] != $posted_to && $row['PostedBy'] != $user_to && $row['PostedBy'] != $userLoggedIn && !in_array($row['PostedBy'], $notified_users)) { //checks, for instance, if it is not who who posted the post, if $posted_to is not the owner of the post(if they are, they will get their own notification), or if the person is not the owner of the profile post
		//also checks if the username of the person who commented is not already in the array 

			$notification = new Notification($con, $userLoggedIn);
			$notification->insertNotification($post_id, $row['PostedBy'], "comment_non_owner");

			array_push($notified_users, $row['PostedBy']); //insert the username into the array
		}
	}

	echo "<p>Comment posted!</p>";
}


?>


<form action="comment_frame.php?post_id=<?php echo $post_id; ?>" id="comment_form" name="postComment<?php echo $post_id; ?>" method="POST">
	<textarea name="post_body"></textarea>
	<input type="submit" name="postComment<?php echo $post_id; ?>" value="Post"> 
</form>

<!-- Load comments -->
<?php 
$get_comments = mysqli_query($con,  "SELECT * FROM comments WHERE PostID='$post_id' ORDER BY PostCommentID ASC");
$count = mysqli_num_rows($get_comments);

if($count != 0) { //if there are posts/comments to load

	while($comment = mysqli_fetch_array($get_comments)) { //each time it goes around the variable containing the information

		$comment_body = $comment['PostBody'];
		$posted_to = $comment['PostedTo'];
		$posted_by = $comment['PostedBy'];
		$date_added = $comment['DateAdded'];
		$removed = $comment['IsRemoved'];


		//Timeframe
		$date_time_now = date("Y-m-d H:i:s");
		$start_date = new DateTime($date_added); //time post was made
		$end_date = new DateTime($date_time_now); //current time
		$interval = $start_date->diff($end_date); //difference between dates

		if($interval->y >= 1) { //if it has been at least a year since the post was made/posted
			if($interval == 1) {
				$time_message = $interval->y . "year ago"; //1 year ago
			} 
			else {
				$time_message = $interval->y . "years ago"; // more than 1 year ago
			}
		} 
		else if($interval->m >= 1) {
			if($interval->d == 0) {
				$days = " ago";
			}
			else if($interval->d == 1) {
				$days = $interval->d . " day ago";
			}
			else {
				$days = $interval->d . " days ago";
			}


			if($interval->m == 1) {
				$time_message = $interval->m . " month " . $days;
			}
			else {
				$time_message = $interval->m . " months " . $days;
			}

		} 
		else if($interval->d >= 1) {
			if($interval->d == 1) {
				$time_message = "Yesterday";
			}
			else {
				$time_message = $interval->d . " days ago";
			}

		} 
		else if($interval->h >= 1) {
			if($interval->h == 1) {
				$time_message = $interval->h . " hour ago";
			}
			else {
				$time_message = $interval->h . " hours ago";
			}
		} 
		else if($interval->i >= 1) {
			if($interval->i == 1) {
				$time_message = $interval->i . " minute ago";
			}
			else {
				$time_message = $interval->i . " minutes ago";
			}
		} 

		else {
			$time_message = "Less than a minute ago"; //make into switch statement later
		}

		$user_obj = new User($con, $posted_by); //user who posted the comment

		?> 
		<div class="comment_section">
			<a href="<?php echo $posted_by?>" target="_parent"><img src="<?php echo $user_obj->getProfilePicture(); ?>" 
				title="<?php echo $posted_by; ?>" style="float:left;" height="30"></a>
				<a href="<?php echo $posted_by?>" target="_parent"> <b> <?php echo $user_obj->getFirstAndLastName(); ?> </b></a>
				&nbsp;&nbsp;&nbsp;&nbsp; <?php echo $time_message . "<br>" . $comment_body; ?>
				<hr>
			</div>

			<?php 

		}

		
	}

	else {
		echo "<center><br><br>No comments have been posted yet.</center>";
	}

	?>



</body>
</html>