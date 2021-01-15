<?php
require 'config/config.php';
include("classes/User.php");
include("classes/Post.php");
include("classes/Message.php");
include("classes/Notification.php");

if(isset($_SESSION['username'])) {
	$userLoggedIn = $_SESSION['username']; //if the session variable is set, make the session variable equal to that
	//username will be accessible anywhere
	//if not set, the user is not logged in
	$user_details_query = mysqli_query($con, "SELECT * FROM users WHERE UserName='$userLoggedIn'");
	$user = mysqli_fetch_array($user_details_query); //returns all the values for this user
}

else {
	header("Location: registration.php"); //takes the user back to the register page if they are not logged in
}

?>

<html>
<head>
	<title>SafetyNet</title>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
	<script src="assets/js/bootstrap.js"></script>
	<script src="assets/js/bootbox.js"></script>
	<script src="assets/js/safetenet.js"></script>
	<script src="assets/js/jquery.Jcrop.js"></script>
	<script src="assets/js/jcrop_bits.js"></script>

	<link rel="stylesheet" type="text/css" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
	<link rel="stylesheet" type="text/css" href="assets/css/bootstrap.css">
	<link rel="stylesheet" type="text/css" href="assets/css/style.css">
	<link rel="stylesheet" type="text/css" href="assets/css/jquery.Jcrop.css">
	<head>

		<body>
			<div class="top_bar">
				<div class="logo">
					<a href="index.php"><img src="assets/images/misc/safetynet_logo2.png" width=40>SAFETYNET</a>
				</div>

				<div class="search">
					<form action="search.php" method="GET" name="search_form">
						<input type="text" onkeyup="getLiveSearchUsers(this.value, '<?php echo $userLoggedIn; ?>')" name="q" placeholder="Search for users here..." autocomplete="off" id="search_text_input">
						<div class="button_holder">
							<i style="margin-top: 12px" class="fa fa-search fa-lg" aria-hidden="true"></i>
						</div>
					</form>

					<div class="search_results">
					</div>

					<div class="search_results_footer_empty">
					</div>

				</div>

				<div>
				 <?php
				 $user_obj = new User($con, $userLoggedIn);
				 $is_new_account_query = mysqli_query($con, "SELECT IsNewAccount From users WHERE UserName='$userLoggedIn'");
				 $row = mysqli_fetch_array($is_new_account_query);

				 $is_new_account = $row['IsNewAccount'];

				 if($is_new_account === "yes") {
				 	echo "<span style='color: #fff;'>You will gain 2 Thanks a piece when you send your first five posts.</span>";
				 }


				 ?>
				</div>

				<nav>

					<?php 
					//Unread messages
					$messages = new Message($con, $userLoggedIn);
					$num_messages = $messages->getUnreadNumber();
					//Unread notifications
					$notifications = new Notification($con, $userLoggedIn);
					$num_notifications = $notifications->getUnreadNumber();
					//Unread friend requests
					$user_obj = new User($con, $userLoggedIn);
					$num_requests =  $user_obj->getNumberOfFriendRequests();

					//if user has logged in the next day and/or after, they get 5 Thanks
					$last_logged_in_query = mysqli_query($con, "SELECT LastLogin FROM users WHERE UserName='$userLoggedIn'");
					$row = mysqli_fetch_array($last_logged_in_query);

					$current_date = date("Y-m-d");
					$last_logged_in_date = new DateTime($row['LastLogin']); //date the user last logged in
					$current_date_obj = new DateTime(date($current_date)); //current date
					$interval = $last_logged_in_date->diff($current_date_obj); //difference between the two dates

					$num_thanks_query = mysqli_query($con, "SELECT NumThanks, TotalNumThanks FROM users WHERE UserName='$userLoggedIn'");
					$row = mysqli_fetch_array($num_thanks_query);
					$num_thanks = $row['NumThanks'];
					$total_num_thanks = $row['TotalNumThanks'];

					if($interval->y >= 1 || $interval->m >= 1 || $interval->d >= 1) {
					$num_thanks+=5;
					$total_num_thanks+=5;
					$thanks_added_query = mysqli_query($con, "UPDATE users SET NumThanks='$num_thanks', TotalNumThanks='$total_num_thanks', LastLogin='$current_date' WHERE UserName='$userLoggedIn'"); //adds 5 Thanks to the user's account, and changes the last logged in date to the present day - ensures that users can only gain Thanks via this method roughly once a day
					echo "<div class='login_bonus_message'>You've recieved a login bonus of 5 Thanks!</div>";
					}



					?>



					<div><img src="assets/images/icons/thanks_symbol.png" height="25" width="20">
					<?php echo "<span style='color: #ffffff;'>" . $user['NumThanks'] . "</span>"; ?>
					</div>
					<span style="color:#fff">
						Welcome back,
					<a href="<?php echo $userLoggedIn; ?>">
						<?php echo $user['FirstName']; ?> 
					</a></span>

					<a href="index.php">
						<i class="fa fa-home"></i>
					</a>

					<a href="javascript:void(0);" onclick="getDropdownData('<?php echo $userLoggedIn; ?>', 'message')">
					<i class="fa fa-envelope" aria-label="Messages" aria-hidden="true"></i>
						<?php 
						if($num_messages > 0)
							echo '<span class="notification_badge" id="unread_message">' . $num_messages . '</span>';
						?>
					</a>

					<a href="javascript:void(0);" onclick="getDropdownData('<?php echo $userLoggedIn; ?>', 'notification')">
						<i class="fa fa-bell" aria-label="Notifications" aria-hidden="true"></i>
						<?php 
						if($num_notifications > 0)
							echo '<span class="notification_badge" id="unread_notification">' . $num_notifications . '</span>';
						?>	
					</a>

					<a href="user_settings.php">
					<i class="fa fa-cog" aria-label="Settings" aria-hidden="true"></i>
					</a>

					<a href="friend_requests.php">
						<i class="fa fa-users" aria-label="Friend Requests" aria-hidden="true"></i>
						<?php 
						if($num_requests > 0)
							echo '<span class="notification_badge" id="unread_request">' . $num_requests . '</span>';
						?>	</a>

						<a href="includes/handlers/logout.php">
						<i class="fa fa-sign-out" aria-label="Logout" aria-hidden="true"></i>
						</a>
					</nav>

					<div class="dropdown_data_window" style="height: 0px; border: none;"></div>
					<input type="hidden" id="dropdown_data_type" value="">

				</div>

				<script>
					$(function(){

						var userLoggedIn = '<?php echo $userLoggedIn; ?>';
						var dropdownInProgress = false;

						$(".dropdown_data_window").scroll(function() {
							var bottomElement = $(".dropdown_data_window a").last();
							var noMoreData = $('.dropdown_data_window').find('.noMoreDropdownData').val();

            // isElementInViewport uses getBoundingClientRect(), which requires the HTML DOM object, not the jQuery object. The jQuery equivalent is using [0] as shown below.
            if (isElementInView(bottomElement[0]) && noMoreData == 'false') {
            	loadPosts();
            }
        });

						function loadPosts() {
            if(dropdownInProgress) { //If it is already in the process of loading some posts, just return
            	return;
            }
            
            dropdownInProgress = true;

            var page = $('.dropdown_data_window').find('.nextPageDropdownData').val() || 1; //If .nextPage couldn't be found, it must not be on the page yet (it must be the first time loading posts), so use the value '1'

            var pageName; //Holds name of page to send ajax request to
            var type = $('#dropdown_data_type').val();

            if(type == 'notification')
            	pageName = "ajax_load_notifications.php";
            else if(type == 'message')
            	pageName = "ajax_load_messages.php";

            $.ajax({
            	url: "includes/handlers/" + pageName,
            	type: "POST",
            	data: "page=" + page + "&userLoggedIn=" + userLoggedIn,
            	cache: false,

            	success: function(response) {

                    $('.dropdown_data_window').find('.nextPageDropdownData').remove(); //Removes current .nextpage 
                    $('.dropdown_data_window').find('.noMoreDropdownData').remove();

                    $(".dropdown_data_window").append(response);

                    dropdownInProgress = false;
                }
            });
        }

        //Check if the element is in view
        function isElementInView (el) {

        	if(el == null) {
        		return;
        	}
        	var rect = el.getBoundingClientRect();

        	return (
        		rect.top >= 0 &&
        		rect.left >= 0 &&
                rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) && 
                rect.right <= (window.innerWidth || document.documentElement.clientWidth) 
                );
        }
    });
</script>




<div class="wrapper">