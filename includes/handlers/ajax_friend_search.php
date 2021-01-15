<?php 
include("../../config/config.php"); //allows to connect to the database
include("../classes/User.php");

$query = $_POST['query'];
$userLoggedIn = $_POST['userLoggedIn']; //come from safetenet.js

$names = explode(" ", $query); //splits up the spaces - ensures that there are multiple elements in the name array, for first and last name

//check to predict what the user is searching for, whether it is a username, first name, last

if(strpos($query, "_") !== false) { //if an underscore exists in the string, assume the user is searching for a username
	$usersReturned = mysqli_query($con, "SELECT * FROM users WHERE UserName LIKE 'query%' AND IsClosed='no' LIMIT 8"); //yemi = yemi345 or yemiekejrkejkfjdkfj
		//yem% = yem0 or yem2 or yemq390wid
}

else if (count($names) == 2) { //if there are two strings, assume they are searching for first and last names
	$usersReturned = mysqli_query($con, "SELECT * FROM users WHERE FirstName LIKE '%$names[0]%' AND LastName LIKE '%$names[1]%' AND IsClosed='no' LIMIT 8");
}

else { //this is for scenarios where the user enters only one name
	$usersReturned = mysqli_query($con, "SELECT * FROM users WHERE FirstName LIKE '%$names[0]%' OR LastName LIKE '%$names[0]%' AND IsClosed='no' LIMIT 8"); //"OR"?
}

if($query != "") { //if the query is not empty
	while($row = mysqli_fetch_array($usersReturned)) {
		$user = new User($con, $userLoggedIn);

		if($row['UserName'] != $userLoggedIn) { //if they haven't found a result of themselves 
		$mutual_friends = $user->getMutualFriends($row['UserName']) . " mutual friends";
	}
	else {
		$mutual_friends = "";
	}

	if($user->isFriend($row['UserName'])) {
		echo "<div class='resultDisplay'>
		<a href='send_messages.php?u=" . $row['UserName'] . "' style='color: #000;'>
		<div class='liveSearchProfilePicture'>
		<img src='". $row['ProfilePicture'] . "'>
		</div>

		<div class='liveSearchText'>
		".$row['FirstName'] . " " . $row['LastName']. "
		<p style='margin: 0;'>" . $row['UserName'] . "</p>
		<p id='grey'>" . $mutual_friends . "</p>
		</div>
		</a>
		</div>";


	}


	}
}
?>