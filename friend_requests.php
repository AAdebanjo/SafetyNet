<?php 
include("includes/header.php");
?>

<div class="main_column column" id="main_column">
	<h4>Friend Requests</h4>

	<?php

	$query = mysqli_query($con, "SELECT * FROM friend_requests WHERE UserTo='$userLoggedIn'");
	if(mysqli_num_rows($query) == 0) { //zero friend requests
		echo "You have no friend requests at this time.";
	} 



	else {
		while($row = mysqli_fetch_array($query)) {
			$user_from = $row['UserFrom'];
			$user_from_obj = new User($con, $user_from);

			echo $user_from_obj->getFirstAndLastName() . " sent you a friend request.";

			$user_from_friend_array = $user_from_obj->getFriendArray();

			if(isset($_POST['accept_request' . $user_from])) { //need to be different for every friend request the user has
				$add_friend_query = mysqli_query($con, "UPDATE users SET FriendArray=CONCAT(FriendArray, '$user_from,') WHERE UserName='$userLoggedIn'");
				$add_friend_query = mysqli_query($con, "UPDATE users SET FriendArray=CONCAT(FriendArray, '$userLoggedIn,') WHERE UserName='$user_from'");

				$delete_query = mysqli_query($con, "DELETE FROM friend_requests WHERE UserTo='$userLoggedIn' AND UserFrom='$user_from'");
				echo "You are now friends.";
				header("Location: friend_requests.php");

			}

			if(isset($_POST['ignore_request' . $user_from])) { //every time the loop is gone over,  a unique friend request is created
				$delete_query = mysqli_query($con, "DELETE FROM friend_requests WHERE UserTo='$userLoggedIn' AND UserFrom='$user_from'");
				echo "Request ignored.";
				header("Location: friend_requests.php");
			}

			?>
			<form action="friend_requests.php" method="POST">
				<input type="submit" name="accept_request<?php echo $user_from; ?>" id="accept_button" value="Accept">
				<input type="submit" name="ignore_request<?php echo $user_from; ?>" id="ignore_button" value="Ignore">
			</form>
			<?php 
		}
	}
	?>


	

</div>
