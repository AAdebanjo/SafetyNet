<?php 
include ("includes/header.php");
//include("includes/classes/User.php");
//include("includes/classes/Post.php");
//include("includes/classes/Message.php");

$message_obj = new Message($con, $userLoggedIn);
//take a username, passes a variable through the URL

if(isset($_GET['u'])) { //u for username
	$user_to = $_GET['u'];
} else {
	$user_to = $message_obj->getMostRecentUser(); //retrieve the most recent user the person has had an interaction with/most recent user they've messages

	if($user_to == false) {
		$user_to = 'new'; //if user has not sent a message to anybody yet, then the variable will be set to 'new' - which means it will send a new message
	}
}


if($user_to != "new") {
	$user_to_obj = new User($con, $user_to); //if the user is not new(i.e., the user is not trying to send a new message), then create a User object out of the username we have have
	//if it IS new, either because there haven't been any recent interactions or if the user simply wants to create a new message, then a User object is not required, not enough to create one 
}

if(isset($_POST['post_message'])) {

	if(isset($_POST['message_body'])) {
		$body = mysqli_real_escape_string($con, $_POST['message_body']); //prepares a string so that it can be used in a MySQL format
		$date = date("Y-m-d H:i:s");
		$message_obj->sendMessage($user_to, $body, $date); 
		header("Location: send_messages.php?u=$user_to");
	}
}


if(isset($_POST['send_thanks'])) {
	$url = $_SERVER['REQUEST_URI'];
	$user_to = $user_to = substr($url, strpos($url, "=") + 1);
	$date = date("Y-m-d-H:i:s");
	$message_obj->sendThanks($date);
	header("Location: send_messages.php?u=$user_to");
}

?>

<div class="user_details column">
	<a href="<?php echo $userLoggedIn; ?>">  <img src="<?php echo $user['ProfilePicture']; ?>"> </a>

	<div class="user_details_left_right">
		<a href="<?php echo $userLoggedIn; ?>">
			<?php 
			echo $user['FirstName'] . " " . $user['LastName'];

			?>
		</a>
		<br>
		<?php echo "Level " . $user['PlayerLevel']. "<br>";
		echo "Posts: " . $user['NumPosts']. "<br>"; 
		echo "Current Thanks: " . $user['NumThanks'];

		?>
	</div>
</div> <!--best modified for later -->

<div class="main_column column" id="main_column">
	<?php
	if($user_to != "new") {
		echo "<h4> You and <a href='$user_to'>" . $user_to_obj->getFirstAndLastName() . "</a></h4><hr><br>";
		echo "<div class='loaded_messages' id='scroll_messages'>";
		echo $message_obj->getMessages($user_to);
		echo "</div>";
	} 
	else {
		echo "<h4>New message</h4>";
	}
	?>




	<div class="message_post">
		<form action="" method="POST">
				<?php  //this php block will execute differently depending on whether or not user_to is equal to new(new message) or if the message is to be sent to a user

				if($user_to == "new") {
					echo "Select the friend you would like to message <br><br>";
					?>
					To: <input type='text' onkeyup='getUsers(this.value, "<?php echo $userLoggedIn; ?>")' name='q' placeholder='Name' autocomplete='off' id='search_text_input';
					>
					<?php
					echo "<div class='results'></div>";
				}

				else {
					echo "<textarea name='message_body' id='message_textarea' placeholder='Write your message ...'></textarea>";
					echo "<input type='submit' name='post_message' class='info' id='message_submit' value='Send Message'>";
					echo "<input type='submit' name='send_thanks' class='info' id='thanks_submit' value='Send Thanks'>";

					

				}

				?>

			</form>
		</div>

		<script>
			var div = document.getElementById("scroll_messages");

			if(div != null) {
				div.scrollTop = div.scrollHeight;
			}
		</script>


	</div>

	<div class="user_details column" id="conversations">
		<h4>Conversations</h4>

		<div class="loaded_conversations">
			<?php echo $message_obj->getConversations(); ?>
		</div>
		<br>
		<a href="send_messages.php?u=new">New Message</a>

	</div>


