<?php 
class Notification {
	private $user_obj;
	private $con;

	public function __construct($con, $user) {
		$this->con = $con;
		$this->user_obj = new User($con, $user); //within this class, an instance of the User class is created

		//constuctor is called as soon as the user creates an object of the User class
	}

	public function getUnreadNumber() {
		$userLoggedIn = $this->user_obj->getUsername();
		$query = mysqli_query($this->con, "SELECT * FROM notifications WHERE IsViewed='no' AND UserTo='$userLoggedIn'");
		return mysqli_num_rows($query); //number of results returned by this query
	}

	public function getNotificationsDropDown($data, $limit) {

		$page = $data['page'];
		$userLoggedIn = $this->user_obj->getUsername();
		$return_string = "";

		if($page == 1) {
			$start = 0;
		} else {
			$start = ($page - 1) * $limit; //will calculate where to start loading the posts/messages from. This is for when we scroll to the bottom of the dropdown list, needs to know exactly when it will need to load new messages
		}

		$set_viewed_query = mysqli_query($this->con, "UPDATE notifications SET IsViewed='yes' WHERE UserTo='$userLoggedIn'");

		$query = mysqli_query($this->con, "SELECT * FROM notifications WHERE UserTo='$userLoggedIn' ORDER BY NotificationID DESC");

		if(mysqli_num_rows($query) == 0) {
			echo "You have no notifications";
			return; //leave the function
		}

		$num_iterations = 0; //number of messages checked/seen(not necessarily posted/opened)
		$count = 1; //number of messages posted

		while($row = mysqli_fetch_array($query)) {


			if($num_iterations++ < $start) { //if it hasn't reached the start point yet
			continue;
		}

		if($count > $limit) {
			break;
		} else {
			$count++;
		}

		$user_from = $row['UserFrom'];
			$user_data_query = mysqli_query($this->con, "SELECT * FROM users WHERE UserName='$user_from'"); //getting data for the user that the notification is from

			$user_data = mysqli_fetch_array($user_data_query);


				//Timeframe
			$date_time_now = date("Y-m-d H:i:s");
				$start_date = new DateTime($row['DateTime']); //time post was made
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
						$time_message = $interval->m . " month" . $days;
					}
					else {
						$time_message = $interval->m . " months" . $days;
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


			$opened = $row['IsOpened'];
			$style = (isset($row['IsOpened']) && $row['IsOpened'] == 'no') ? "background-color: #DDEDFF;" : ""; //checks if the 'opened' variable exists before the conditional statement starts


			//returns the path to the user's profile picture
			$return_string .="<a href='" . $row['NotificationLink'] . "'>
			<div class='resultDisplay resultDisplayNotification' style='" . $style . "'>
			<div class='notificationsProfilePicture'>
			<img src='" . $user_data['ProfilePicture'] .  "'>
			</div>
			<p class='timestamp_smaller' id='grey'>" . $time_message . "</p>" . $row['Message'] . "
			</div>
			</a>"; 
		}

		//if posts were loaded

		if($count > $limit) { //if limit has been reached
			$return_string .= "<input type='hidden' class='nextPageDropdownData' value='" . ($page + 1) .  "'><input type='hidden' class='noMoreDropdownData' value='false'>";
		}

		else { //if there are more posts to load
			$return_string .= "<input type='hidden' class='noMoreDropdownData' value='true'> <p style='text-align: center;'>No more notifications to load</p>";
		}
		return $return_string;

	}

	public function insertNotification($post_id, $user_to, $type) {
		$userLoggedIn = $this->user_obj->getUsername();
		$userLoggedInName = $this->user_obj->getFirstAndLastName();

		$date_time = date("Y:m:d H:i:s");

		switch($type) {
			case 'comment':
			$message = $userLoggedInName . " commented on your post.";
			break;
			case 'like':
			$message = $userLoggedInName . " gave you some thanks for your post.";
			break;
			case 'profile_post':
			$message = $userLoggedInName . " posted on your profile.";
			break;
			case 'comment_non_owner':
			$message = $userLoggedInName . " commented on a post that you commented on.";
			break;
			case 'profile_comment':
			$message = $userLoggedInName . " commented on your profile post.";
			break;
		}


		$link = "user_post.php?id=" . $post_id;

		$insert_query = mysqli_query($this->con, "INSERT INTO notifications VALUES('', '$user_to', '$userLoggedIn', '$message', '$link', '$date_time', 'no', 'no')");
	}

}

?>