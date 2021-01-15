<?php 
class Message {
	private $user_obj;
	private $con;

	public function __construct($con, $user) {
		$this->con = $con;
		$this->user_obj = new User($con, $user); //within this class, an instance of the User class is created

		//constuctor is called as soon as the user creates an object of the User class

		//$user_obj = new User($con, "john_zoidberg");
		//$user_obj = new User($con, $userLoggedIn);
	}

	public function getMostRecentUser() { //shows the most recent interaction the user has had. If there hasn't been one, then the function will return false
		$userLoggedIn = $this->user_obj->getUsername();

		$query = mysqli_query($this->con, "SELECT UserTo, UserFrom FROM messages WHERE UserTo='$userLoggedIn' OR UserFrom='$userLoggedIn' ORDER BY MessageID DESC LIMIT 1"); //only get one, aka the most recent message
		//will retrieve just one result from the messages table where the userloggedin is the person who the message was to or the message was from
		//don't know what the user will be in

		if(mysqli_num_rows($query) == 0) {
			return false; //didn't find any recent user, so new message
		}

		$row = mysqli_fetch_array($query);
		$user_to = $row['UserTo'];
		$user_from = $row['UserFrom'];

		//return which one of them is not the user logged in
		if($user_to != $userLoggedIn) {
			return $user_to;
		} 

		else {
			return $user_from;
		}


	}

	public function sendMessage($user_to, $body, $date) {
		if($body != "") {
			$userLoggedIn = $this->user_obj->getUsername(); //returns username for the user currently logged in
			$query = mysqli_query($this->con, "INSERT INTO messages VALUES('', '$user_to', '$userLoggedIn', '$body', '$date', 'no', 'no', 'no')");
		}
	}

	public function sendThanks($date) {

		$userLoggedIn = $this->user_obj->getUsername();

		$url = $_SERVER['REQUEST_URI'];


		//if the current user is sending messages on the Message sub-page
		if(strpos($_SERVER['REQUEST_URI'], 'send_messages') !== false) {
			$user_to = substr($url, strpos($url, "=") + 1);
		}
		else { //if the current user is sending messages on another user's profile page
			$array = explode('/', $_SERVER['REQUEST_URI']);
			$user_to = $array[count($array) - 1];
		}


		//if current user is not sending a message to themselves
		if($userLoggedIn != $user_to) {
				$userLoggedIn_details = mysqli_query($this->con, "SELECT * FROM users WHERE UserName='$userLoggedIn'");
				$row = mysqli_fetch_array($userLoggedIn_details);
				$userLoggedIn_first_name = $row['FirstName'];
				$userLoggedIn_thanks = $row['NumThanks'];
				$userLoggedIn_total_thanks = $row['TotalNumThanks'];

				$user_to_details = mysqli_query($this->con, "SELECT * FROM users WHERE UserName='$user_to'");
				$row = mysqli_fetch_array($user_to_details);
				$user_to_first_name = $row['FirstName'];
				$user_to_thanks = $row['NumThanks'];
				$user_to_total_thanks = $row['TotalNumThanks'];

				if($userLoggedIn_thanks > 0) {
					$userLoggedIn_thanks--;
					$userLoggedIn_total_thanks--;
					$user_to_thanks++;
					$user_to_total_thanks++;
					$message_query = mysqli_query($this->con, "INSERT INTO messages VALUES('', '$user_to', '$userLoggedIn', '$user_to_first_name has been given a Thank!', '$date', 'no', 'no', 'no')");
					$userLoggedIn_query = mysqli_query($this->con, "UPDATE users SET NumThanks='$userLoggedIn_thanks', TotalNumThanks='$userLoggedIn_total_thanks' WHERE UserName='$userLoggedIn'");
					$user_to_query = mysqli_query($this->con, "UPDATE users SET NumThanks='$user_to_thanks', TotalNumThanks='$user_to_total_thanks' WHERE UserName='$user_to'");

				} else { 
					echo "You do not have any Thanks to give.";
				}

		} else {
			echo "You can not give yourself any Thanks.";
		}

		
		
	}

	public function getMessages($otherUser) {
		$userLoggedIn = $this->user_obj->getUsername();
		$data = "";

		$query = mysqli_query($this->con, "UPDATE messages SET IsOpened='yes' WHERE UserTo='$userLoggedIn' AND UserFrom='$otherUser'");

		$get_messages_query = mysqli_query($this->con, "SELECT * FROM messages WHERE (UserTo='$userLoggedIn' AND UserFrom='$otherUser') OR (UserfROM='$userLoggedIn' AND UserTo='$otherUser')");

		while($row = mysqli_fetch_array($get_messages_query)) {
			$user_to = $row['UserTo'];
			$user_from = $row['UserFrom'];
			$body = $row['MessageBody'];
			$id = $row['MessageID'];

			$div_top = ($user_to == $userLoggedIn) ? "<div class='message' id='green'>" : "<div class='message' id='blue'>";
			$data = $data . $div_top . $body . "</div><br><br><br>";
		}
		return $data;
	}

	public function getLatestMessage($userLoggedIn, $user2) {
		$details_array = array();

		$query = mysqli_query($this->con, "SELECT MessageBody, UserTo, MessageDate FROM messages WHERE (UserTo='$userLoggedIn' AND UserFrom='$user2') OR (UserTo='$user2' AND UserFrom='$userLoggedIn') ORDER BY MessageID DESC LIMIT 1"); //only need one message, the most recent one

		$row = mysqli_fetch_array($query);
		$sent_by = ($row['UserTo'] == $userLoggedIn) ? "They said: " : "You said: ";

		$date_time_now = date("Y-m-d H:i:s");
				$start_date = new DateTime($row['MessageDate']); //time post was made
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

				array_push($details_array, $sent_by);
				array_push($details_array, $row['MessageBody']);
				array_push($details_array, $time_message);

				return $details_array;

			}
			public function getConversations() {
				$userLoggedIn = $this->user_obj->getUsername();
				$return_string = "";
				$convos = array();

				$query = mysqli_query($this->con, "SELECT UserTo, UserFrom FROM messages WHERE UserTo='$userLoggedIn' OR UserFrom='$userLoggedIn' ORDER BY MessageID DESC");

		while($row = mysqli_fetch_array($query)) { //add the usernames that the person is having a conversation with to the array
			$user_to_push = ($row['UserTo'] != $userLoggedIn) ? $row['UserTo'] : $row['UserFrom'];

			//check if the username is not already in the array

			if(!in_array($user_to_push, $convos)) {
				array_push($convos, $user_to_push); //add the username to the array
			} 

		}

		foreach($convos as $username) {
			$user_found_obj = new User($this->con, $username);
			$latest_message_details = $this->getLatestMessage($userLoggedIn, $username);

			$dots = (strlen($latest_message_details[1]) >= 12) ? "..." : "";
			$split = str_split($latest_message_details[1], 12); //chops the string into twelve characters
			$split = $split[0] . $dots;

			//returns the path to the user's profile picture
			$return_string .="<a href='send_messages.php?u=$username'> <div class='user_found_messages'>
			<img src='" . $user_found_obj->getProfilePicture() . "' style='border-radius: 5px; margin-right: 5px;'>" . $user_found_obj->getFirstAndLastName() . "<span class='timestamp_smaller' id='grey'> " . $latest_message_details[2/*date*/]. "</span>
			<p id='grey' style='margin: 0;'>" . $latest_message_details[0] . $split . "</p>
			</div>
			</a>"; 
		}

		return $return_string;
		
	}

	public function getConversationsDropDown($data, $limit) {

		$page = $data['page'];
		$userLoggedIn = $this->user_obj->getUsername();
		$return_string = "";
		$convos = array();

		if($page == 1) {
			$start = 0;
		} else {
			$start = ($page - 1) * $limit; //will calculate where to start loading the posts/messages from. This for when we scroll to the bottom of the dropdown list, needs to know exactly when it will need to load new messages
		}

		$set_viewed_query = mysqli_query($this->con, "UPDATE messages SET IsViewed='yes' WHERE UserTo='$userLoggedIn'");

		$query = mysqli_query($this->con, "SELECT UserTo, UserFrom FROM messages WHERE UserTo='$userLoggedIn' OR UserFrom='$userLoggedIn' ORDER BY MessageID DESC");

		while($row = mysqli_fetch_array($query)) { //add the usernames that the person is having a conversation with to the array
			$user_to_push = ($row['UserTo'] != $userLoggedIn) ? $row['UserTo'] : $row['UserFrom'];

			//check if the username is not already in the array

			if(!in_array($user_to_push, $convos)) {
				array_push($convos, $user_to_push); //add the username to the array
			} 

		}

		$num_iterations = 0; //number of messages checked/seen(not necessarily posted/opened)
		$count = 1; //number of messages posted

		foreach($convos as $username) {


			if($num_iterations++ < $start) { //if it hasn't reached the start point yet
			continue;
		}

		if($count > $limit) {
			break;
		} else {
			$count++;
		}

		$is_unread_query = mysqli_query($this->con, "SELECT IsOpened FROM messages WHERE UserTo='$userLoggedIn' AND UserFrom='$username' ORDER BY MessageID DESC");
			$row = mysqli_fetch_array($is_unread_query); //only want to do this once
			$style = (isset($row['IsOpened']) && $row['IsOpened'] == 'no') ? "background-color: #DDEDFF;" : ""; //checks if the 'opened' variable exists before the conditional statement starts

			$user_found_obj = new User($this->con, $username);
			$latest_message_details = $this->getLatestMessage($userLoggedIn, $username);

			$dots = (strlen($latest_message_details[1]) >= 12) ? "..." : "";
			$split = str_split($latest_message_details[1], 12); //chops the string into twelve characters
			$split = $split[0] . $dots;

			//returns the path to the user's profile picture
			$return_string .="<a href='send_messages.php?u=$username'> <div class='user_found_messages' style='" . $style . "'>
			<img src='" . $user_found_obj->getProfilePicture() . "' style='border-radius: 5px; margin-right: 5px;'>" . $user_found_obj->getFirstAndLastName() . "<span class='timestamp_smaller' id='grey'> " . $latest_message_details[2/*date*/]. "</span>
			<p id='grey' style='margin: 0;'>" . $latest_message_details[0] . $split . "</p>
			</div>
			</a>"; 
		}

		//if posts were loaded

		if($count > $limit) { //if limit has been reached
			$return_string .= "<input type='hidden' class='nextPageDropdownData' value='" . ($page + 1) .  "'><input type='hidden' class='noMoreDropdownData' value='false'>";
		}

		else { //if there are more posts to load
			$return_string .= "<input type='hidden' class='noMoreDropdownData' value='true'> <p style='text-align: center;'>No more messages to load</p>";
		}
		return $return_string;

	}

	public function getUnreadNumber() {
		$userLoggedIn = $this->user_obj->getUsername();
		$query = mysqli_query($this->con, "SELECT * FROM messages WHERE IsViewed='no' AND UserTo='$userLoggedIn'");
		return mysqli_num_rows($query); //number of results returned by this query
	}
}




?>