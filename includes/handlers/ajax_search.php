<?php 
include("../../config/config.php");
include("../../includes/classes/User.php");

$query = $_POST['query'];
$userLoggedIn = $_POST['userLoggedIn']; //the two variables that will be passed onto this page

$names = explode(" ", $query); //return an array, split by spaces

//If query contains an underscore, assume user is searching for usernames

if(strpos($query, '_') !== false) {
	$usersReturnedQuery = mysqli_query($con, "SELECT * FROM users WHERE UserName LIKE '$query%' AND IsClosed='no' LIMIT 8"); //limit queries to 8
}

//If there are two words, assume they are first and last names respectively
else if(count($names) == 2) {
	$usersReturnedQuery = mysqli_query($con, "SELECT * FROM users WHERE (FirstName LIKE '$names[0]%' AND LastName LIKE '$names[1]%') AND IsClosed='no' LIMIT 8"); //limit queries to 8
}

//If query has one word only, search first names or last names
else {
	$usersReturnedQuery = mysqli_query($con, "SELECT * FROM users WHERE (FirstName LIKE '$names[0]%' OR LastName LIKE '$names[0]%') AND IsClosed='no' LIMIT 8"); //limit queries to 8
}

if($query != "") {
	while($row = mysqli_fetch_array($usersReturnedQuery)) {
		$user = new User($con, $userLoggedIn);

		if($row['UserName'] != $userLoggedIn) { //if user hasn't found themselves, then find mutual friends
		$mutual_friends = $user->getMutualFriends($row['UserName']) . " mutual friends";
	} else {
		$mutual_friends = "";
	}

	echo "<div class='resultDisplay'>
	<a href='" . $row['UserName'] . "' style='color: #1485BD'>
	<div class='liveSearchProfilePicture'>
	<img src='" . $row['ProfilePicture'] ."'>
	</div>

	<div class='liveSearchText'>
	" . $row['FirstName'] . " " . $row['LastName'] . "
	<p>" . $row['UserName'] ."</p>
	<p id='grey'>" . $mutual_friends ."</p>
	</div>
	</a>
	</div>";
}
}


?>