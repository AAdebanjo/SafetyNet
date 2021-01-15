<?php
class User {
	private $user;
	private $con;

	public function __construct($con, $user){
		$this->con = $con;
		$user_details_query = mysqli_query($con, "SELECT * FROM users WHERE UserName='$user'");
		$this->user = mysqli_fetch_array($user_details_query);
	}

	public function getUsername() {
		return $this->user['UserName'];
	}

	public function getNumberOfFriendRequests() {
		$username = $this->user['UserName'];
		$query = mysqli_query($this->con, "SELECT * FROM friend_requests WHERE UserTo='$username'");
		return mysqli_num_rows($query);
	}

	public function getNumPosts() {
		$username = $this->user['UserName'];
		$query = mysqli_query($this->con, "SELECT NumPosts FROM users WHERE UserName='$username'");
		$row = mysqli_fetch_array($query);
		return $row['NumPosts'];
	}

	public function getFirstAndLastName() {
		$username = $this->user['UserName'];
		$query = mysqli_query($this->con, "SELECT FirstName, LastName FROM users WHERE UserName='$username'");
		$row = mysqli_fetch_array($query);
		return $row['FirstName'] . " " . $row['LastName'];
	}

	public function getProfilePicture() {
		$username = $this->user['UserName'];
		$query = mysqli_query($this->con, "SELECT ProfilePicture FROM users WHERE UserName='$username'");
		$row = mysqli_fetch_array($query);
		return $row['ProfilePicture'];
	}

	public function getFriendArray() {
		$username = $this->user['UserName'];
		$query = mysqli_query($this->con, "SELECT FriendArray FROM users WHERE UserName='$username'");
		$row = mysqli_fetch_array($query);
		return $row['FriendArray'];
	}

	public function getNumThanks() {
		$username = $this->user['UserName'];
		$query = mysqli_query($this->con, "SELECT NumThanks FROM users WHERE UserName='$username'");
		$row = mysqli_fetch_array($query);
		return $row['NumThanks'];
	}

	public function getTotalThanks() {
		$username = $this->user['UserName'];
		$query = mysqli_query($this->con, "SELECT TotalNumThanks FROM users WHERE UserName='$username'");
		$row = mysqli_fetch_array($query);
		return $row['TotalNumThanks'];
	}

	public function getIsNewAccount() {
		$username = $this->user['UserName'];
		$query = mysqli_query($this->con, "SELECT IsNewAccount FROM users WHERE UserName='$username'");
		$row = mysqli_fetch_array($query);
		return $row['IsNewAccount'];
	}

	public function getLevel() {
		$username = $this->user['UserName'];
		$query = mysqli_query($this->con, "SELECT PlayerLevel FROM users WHERE UserName='$username'");
		$row = mysqli_fetch_array($query);
		return $row['PlayerLevel'];
	}
	
	public function isClosed() {
		$username = $this->user['UserName'];
		$query = mysqli_query($this->con, "SELECT IsClosed FROM users WHERE UserName='$username'");
		$row = mysqli_fetch_array($query);

		if($row['IsClosed'] == 'yes') {
			return true;
		} 

		else {
			return false;
		}
	}

	public function isFriend($username_to_check) {
		$usernameComma = "," . $username_to_check . ",";

		if(strstr($this->user['FriendArray'], $usernameComma) || $username_to_check == $this->user['UserName']) { //checks to see if the username is in the friend array OR if the username to be checked is the same as the user that is logged in, i.e., you
			return true;

		} else {
			return false;
		}
	}

	public function didRecieveRequest($user_from) {
		$user_to = $this->user['UserName'];
		$check_request_query = mysqli_query($this->con, "SELECT * FROM friend_requests WHERE UserTo='$user_to' AND UserFrom='$user_from'");

		if(mysqli_num_rows($check_request_query) > 0) {
			return true; 
		}

		else {
			return false;
		}
	}

	public function didSendRequest($user_to) {
		$user_from = $this->user['UserName'];
		$check_request_query = mysqli_query($this->con, "SELECT * FROM friend_requests WHERE UserTo='$user_to' AND UserFrom='$user_from'");

		if(mysqli_num_rows($check_request_query) > 0) {
			return true; 
		}

		else {
			return false;
		}
	}

	public function removeFriend($user_to_remove) {
		$logged_in_user = $this->user['UserName'];

		$query = mysqli_query($this->con, "SELECT FriendArray FROM users WHERE UserName='$user_to_remove'");
		$row = mysqli_fetch_array($query);
		$friend_array_username = $row['FriendArray'];

		$new_friend_array = str_replace($user_to_remove . ",", "", $this->user['FriendArray']);
		$remove_friend = mysqli_query($this->con, "UPDATE users SET FriendArray='$new_friend_array' WHERE UserName='$logged_in_user'");

		$new_friend_array = str_replace($this->user['UserName'] . ",", "", $friend_array_username); //remove information from the friend's friendarray as well
		$remove_friend = mysqli_query($this->con, "UPDATE users SET FriendArray='$new_friend_array' WHERE UserName='$user_to_remove'");
	}

	public function sendRequest($user_to) {
		$user_from = $this->user['UserName'];
		$query = mysqli_query($this->con, "INSERT INTO friend_requests VALUES('', '$user_to', '$user_from')"); 
	}

	public function getMutualFriends($user_to_check) {
		//friend array for logged in user
		$mutual_friends = 0;
		$user_array = $this->user['FriendArray'];
		$user_array_explode = explode(",", $user_array); //explode splits the string into an array at the given character

		//friend array for user to check
		$query = mysqli_query($this->con, "SELECT FriendArray FROM users WHERE UserName='$user_to_check'");
		$row = mysqli_fetch_array($query);
		$user_to_check_array = $row['FriendArray'];
		$user_to_check_array_explode = explode(",", $user_to_check_array); //explode splits the string into an array at the given character


		foreach($user_array_explode as $i) {

			foreach($user_to_check_array_explode as $j) {

				if($i == $j && $i != "") {
					$mutual_friends++;
				}

			}
		}

		return $mutual_friends;

	}




}

?>