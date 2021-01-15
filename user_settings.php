<?php 
include("includes/header.php");
include("includes/form_handlers/settings_handler.php"); //page that handles functionality of any buttons pressed on this page
?>

<div class="main_column column">
	<h4>Account settings</h4>
	<?php
	echo "<img src='" . $user['ProfilePicture'] . "' class='small_profile_picture'>";
	?>
	<br>
	<a href="upload_profile_picture.php">Upload new profile picture</a><br><br><br>

	Modify the values and click 'Update Details'

	<?php
	$user_data_query = mysqli_query($con, "SELECT FirstName, LastName, Email FROM users WHERE UserName='$userLoggedIn'");
	$row = mysqli_fetch_array($user_data_query);

	$first_name = $row['FirstName'];
	$last_name = $row['LastName'];
	$email = $row['Email'];
	?>

	<form action='user_settings.php' method='POST'>
		First Name: <input type="text" name="first_name" value="<?php echo $first_name; ?>" id="settings_input"><br>
		Last Name: <input type="text" name="last_name" value="<?php echo $last_name; ?>" id="settings_input"><br>
		Email: <input type="email" name="email" value="<?php echo $email; ?>" id="settings_input"><br>
		<?php 
		echo $message;
		?>

		<input type="submit" name="update_details" id="save_details" value="Update Details" class="info settings_submit"><br>
	</form>

	Change password
	<form action='user_settings.php' method='POST'>
		Old Password: <input type="password" name="old_password" id="settings_input"><br>
		New Password: <input type="password" name="new_password_1" id="settings_input"><br>
		Confirm New Password: <input type="password" name="new_password_2" id="settings_input"><br>

		<?php
		echo $password_message; 
		?>

		<input type="submit" name="update_password" id="save_details" value="Update Password" class="info settings_submit"><br>

	</form>

	<h4>Close Account</h4>
	<form action='user_settings.php' method="POST">
		<input type="submit" name="close_account" id="close_account" value="Close Account" class="danger settings_submit">
	</form>

</div>