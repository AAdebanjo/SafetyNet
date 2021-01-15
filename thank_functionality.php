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



//Get id of post
if(isset($_GET['post_id'])) {
	$post_id = $_GET['post_id'];
}



$get_thanks = mysqli_query($con, "SELECT * FROM posts WHERE PostID='$post_id'");
$row = mysqli_fetch_array($get_thanks);
$post_thanks = $row['NumThanks'];
$user_thanked = $row['AddedBy'];


$user_details_query = mysqli_query($con, "SELECT * FROM users WHERE UserName='$user_thanked'");
$row = mysqli_fetch_array($user_details_query);
$user_poster_thanks = $row['NumThanks'];
$user_poster_total_thanks = $row['TotalNumThanks'];


$current_user_details_query = mysqli_query($con, "SELECT * FROM users WHERE UserName='$userLoggedIn'");
$row = mysqli_fetch_array($current_user_details_query);
$current_user_thanks = $row['NumThanks'];
$current_user_total_thanks = $row['TotalNumThanks'];


//Like button
	if(isset($_POST['thank_button'])) { //code that is executed if the thank button is pressed
		if($user_thanked != $userLoggedIn) {
			if($current_user_thanks != 0) {
			$post_thanks++;
			$query = mysqli_query($con, "UPDATE posts SET NumThanks='$post_thanks' WHERE PostID='$post_id'");
			//update total number of thanks for user
			$user_poster_thanks++;
			$user_poster_total_thanks++;
			$current_user_thanks--;
			$current_user_total_thanks--;
			$user_thanks = mysqli_query($con, "UPDATE users SET NumThanks='$user_poster_thanks', TotalNumThanks='$user_poster_total_thanks' WHERE UserName='$user_thanked'");
			$current_user_thankss = mysqli_query($con, "UPDATE users SET NumThanks='$current_user_thanks', TotalNumThanks='$current_user_total_thanks' WHERE UserName='$userLoggedIn'");
			$insert_user = mysqli_query($con, "INSERT INTO thanks VALUES('', '$userLoggedIn', '$post_id')");
		
			$notification = new Notification($con, $userLoggedIn);
			$notification->insertNotification($post_id, $user_thanked, "like");
		} else {
			echo "<div class='message_error'>You do not have any Thanks to give.</div>";
		}
	} else {
		echo "<div class='message_error'>You cannot Thank your own post.</div>";
	} 
	
}


	//Check for previous thanks
	$check_query = mysqli_query($con, "SELECT * FROM thanks WHERE UserName='$userLoggedIn' AND PostID='$post_id'"); //get details on whether or not a user has thanked a post
	$num_rows = mysqli_num_rows($check_query);

	

	if($num_rows > 0) {

		echo '<form action="thank_functionality.php?post_id=' . $post_id . '" method="POST">
		<div class="thanks_image">
		<button type="submit" class="comment_like" name="thanked" value="Thanked"> 
			<img src="assets/images/icons/thanks_symbol.png" height="25" width="20">
		</button>
		</div>
		<div class="like_value">
		'. $post_thanks . ' Thanks
		</div>
		</form>
		'; //if post has been thanked
	}

	
	



	else {
		echo '<form action="thank_functionality.php?post_id=' . $post_id . '" method="POST">
		<div class="thanks_image">
		<button type="submit" class="comment_like" name="thank_button" value="Thank"> 
			<img src="assets/images/icons/thanks_symbol.png" height="25" width="20">
		</button>
		</div>
		<div class="like_value">
		'. $post_thanks . ' Thanks
		</div>
		</form>
		';
	}
?>

<!DOCTYPE html>
<html>
<head>
	<title></title>
	<link rel="stylesheet" type="text/css" href="assets/css/style.css">
</head>
<body>

	<style type="text/css">
		# {
			font-family: Arial, Helvetica, sans-serif;
		}

		body {
			background-color: #fff;
		}
		form {
			position: absolute;
			top: 0;
		}
	</style>

</body>
</html>