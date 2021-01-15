<?php
include("includes/header.php");

if(isset($_GET['q'])) { //gets 'q' parameter from the URL
	$query = $_GET['q'];
}

else {
	$query = "";
}


if(isset($_GET['type'])) { //gets 'q' parameter from the URL
	$type = $_GET['type'];
}

else {
	$type = "";
}

?>

<div class="main_column column" id="main_column">

	<?php
	if($query == "") {
		echo "You must enter something in the search box.";
	} 
	else {


		if($type == 'username') {
			$usersReturnedQuery = mysqli_query($con, "SELECT * FROM users WHERE UserName LIKE '$query%' AND IsClosed='no' LIMIT 8"); //limit queries to 8
		}

		else {

			$names = explode(" ", $query);

			//If there are two words, assume they are first and last names respectively
			if($names === 3) {
				$usersReturnedQuery = mysqli_query($con, "SELECT * FROM users WHERE (FirstName LIKE '$names[0]%' AND LastName LIKE '$names[2]%') AND IsClosed='no'"); //limit queries to 8
				}

			//If query has one word only, search first names or last names
			else if($names === 2) {
				$usersReturnedQuery = mysqli_query($con, "SELECT * FROM users WHERE (FirstName LIKE '$names[0]%' AND LastName LIKE '$names[1]%') AND IsClosed='no'"); //limit queries to 8
				}

			else {
				$usersReturnedQuery = mysqli_query($con, "SELECT * FROM users WHERE (FirstName LIKE '$names[0]%' OR LastName LIKE '$names[0]%') AND IsClosed='no'"); //limit queries to 8
			}

		}

		//Check if results were found
		if(mysqli_num_rows($usersReturnedQuery) == 0) {
			echo "We were unable to find anyone with a " . $type . " similar to the following query: '" . $query . "'<br><br>";
		}

		else {
			echo mysqli_num_rows($usersReturnedQuery) . (mysqli_num_rows($usersReturnedQuery) > 1 ? " results" : " result") . " found: <br><br>";
		}


		

		while($row = mysqli_fetch_array($usersReturnedQuery)) {
			$user_obj = new User($con, $user['UserName']);

			$button = "";
			$mutual_friends = "";

			if($user['UserName'] != $row['UserName']) { //if we haven't found ourselves

			//Generate button depending on friend status
			if($user_obj->isFriend($row['UserName'])) {
				$button = "<input type='submit' name='" . $row['UserName'] . "' class='danger' value='Remove Friend'>"; //must give them different names
			}
			else if($user_obj->didRecieveRequest($row['UserName'])) {
				$button = "<input type='submit' name='" . $row['UserName'] . "' class='warning' value='Respond to request'>"; //must give them different names	
			}
			else if($user_obj->didSendRequest($row['UserName'])) {
				$button = "<input type='submit' class='default' value='Request sent'>"; //must give them different names
			}
			else {
				$button = "<input type='submit' name='" . $row['UserName'] . "' class='success' value='Add Friend'>"; //must give them different names
			}

			$mutual_friends = $user_obj->getMutualFriends($row['UserName']) . " mutual friends";

			//Button forms
			if(isset($_POST[$row['UserName']])) { //if username button is pressed
				if($user_obj->isFriend($row['UserName'])) { //if they are friends
					$user_obj->removeFriend($row['UserName']);
					header("Location: http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
				}
				else if($user_obj->didRecieveRequest($row['UserName'])) {
					header("Location: request.php");
				}
				else if($user_obj->didSendRequest($row['UserName'])) {
					//can add something later
				}
				else {
					$user_obj->sendRequest($row['UserName']);
					header("Location: http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");

				}
			}

			}

			echo "<div class='search_result'>
					<div class='searchPageFriendButtons'>
						<form action='' method='POST'>
							" . $button . "
							<br>
						</form>
					</div>


					<div class='result_profile_picture'>
						<a href='" . $row['UserName'] . "'><img src='" . $row['ProfilePicture'] . "' style='height: 100px;'></a>
					</div>

						<a href='" . $row['UserName'] . "'> " . $row['FirstName'] . " " . $row['LastName'] .  "
						<p id='grey'> " . $row['UserName'] . "</p>
						 </a>
						 <br>
						 " . $mutual_friends . "<br>

				 </div>
				 <hr id='search_hr'>";

		} //End while loop

		
	}

	echo "<span id='grey'>You can use this tool to search for </span>";
		echo "<a href='search.php?q=" . $query . "&type=name'>names</a> or <a href='search.php?q=" . $query . "&type=username'>usernames.</a><br><br><hr id='search_hr'> ";
	?>

</div>