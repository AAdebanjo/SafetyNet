<?php  

if(isset($_POST['login_button'])) { //if it has been pressed

	$email = filter_var($_POST['log_email'], FILTER_SANITIZE_EMAIL); //makes sure email is in the correct format, i.e. "sanitizes" it

	$_SESSION['log_email'] = $email; //Store email into session variable 
	$password = md5($_POST['log_password']); //encrypts password. get password

	$check_database_query = mysqli_query($con, "SELECT * FROM users WHERE Email='$email' AND Password='$password'");
	$check_login_query = mysqli_num_rows($check_database_query);

	if($check_login_query == 1) { //if there is one row returned, it means the login attempt was successful
		$row = mysqli_fetch_array($check_database_query); //gives the ability to access the results from queries
		$username = $row['UserName'];

		$user_closed_query = mysqli_query($con, "SELECT * FROM users WHERE Email='$email' AND IsClosed='yes'");
		if(mysqli_num_rows($user_closed_query) == 1) {
			$reopen_account = mysqli_query($con, "UPDATE users SET IsClosed='no' WHERE Email='$email'");  //updates/reopens user account
		}

		$_SESSION['username'] = $username; //creates a new sesion variable for username, sets it to value of the username




		
		





		$check_if_first_time_query = mysqli_query($con, "SELECT FirstLogin FROM users WHERE UserName='$username'");
		$row = mysqli_fetch_array($check_if_first_time_query);
		$first_time = $row['FirstLogin'];


		if($first_time == "yes") {
			$no_longer_first_time = mysqli_query($con, "UPDATE users SET FirstLogin='no' WHERE Email='$email'");
			header("Location: intro_page.php");
			exit();
		}

		else {
			header("Location: index.php"); //if already logged in, will automatically go to index page
			exit();
		}

		
	}
	else {
		array_push($error_array, "Email or password was incorrect<br>");
	}


}

?>