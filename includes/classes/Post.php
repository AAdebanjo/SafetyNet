<?php 
class Post {
	private $user_obj;
	private $con;

	public function __construct($con, $user) {
		$this->con = $con;
		$this->user_obj = new User($con, $user); //within this class, an instance of the User class is created

		//constuctor is called as soon as the user creates an object of the User class

		//$user_obj = new User($con, "john_zoidberg");
		//$user_obj = new User($con, $userLoggedIn);
	}

	public function submitPost($body, $user_to, $image_name) {
		$body = strip_tags($body); //removes html tags
		$body = mysqli_real_escape_string($this->con, $body);

		$body = str_replace('\r\n', '\n', $body);
		$body = nl2br($body); //replaces new lines with line breaks

		$check_empty = preg_replace('/\s+/', '', $body); //forward slashes are essentially surrounding the text we want to replace - deletes all spaces

		if($check_empty != "") { //check_empty is not to be used in the database, just to check if, for instance, the post body is completely blank


			$body_array = preg_split("/\s+/", $body);

			foreach($body_array as $key => $value) {

				if(strpos($value, "www.youtube.com/watch?v=") !== false) {

					$link = preg_split("!&!", $value);
					//preg_replace - replace a string inside of a string
					$value = preg_replace("!watch\?v=!", "embed/", $link[0]);
					$value = "<br><iframe width=\'420\' height=\'315\' src=\'" . $value . "\'></iframe><br>";

					//save the original post with the amendment
					$body_array[$key] = $value; //key keeps track of what position the array is in
				}

			}

			$body = implode(" ", $body_array);


			//Current date and time
			$date_added = date("Y-m-d H:i:s");

			//Get username
			$added_by = $this->user_obj->getUsername();

			//If user is on their own profile, user_to is 'none'
			//Will be set to 'none' if user is on home page

			if($user_to == $added_by) {
				$user_to = "none";
			}

			//insert post
			$query = mysqli_query($this->con, "INSERT INTO posts VALUES('', '$body', '$added_by', '$user_to', '$date_added', 'no', 'no', '0', '$image_name')");
			$returned_id = mysqli_insert_id($this->con); //returns the id of the post that was just submitted. if the id is 10, it will return 10

			echo $user_to;

			//insert notification
			if($user_to != 'none') { //only if we're posting to another person's profile
			$notification = new Notification($this->con, $added_by);
			$notification->insertNotification($returned_id, $user_to, "profile_post");
		}

			//update post count for user
			$num_posts = $this->user_obj->getNumPosts();
			$num_posts++; //increases the post count by one
			$update_query = mysqli_query($this->con, "UPDATE users SET NumPosts='$num_posts' WHERE UserName='$added_by'"); //added_by = the user who made and posted the post in question

			
			//after a user's first five posts, their thanks bonus is removed 
			$is_new_account = $this->user_obj->getIsNewAccount();
			if($num_posts <= 5) {
				$num_thanks = $this->user_obj->getNumThanks();
				$total_num_thanks = $this->user_obj->getTotalThanks();
				$num_thanks += 2;
				$total_num_thanks += 2;
				$thanks_query = mysqli_query($this->con, "UPDATE users SET NumThanks='$num_thanks', TotalNumThanks='$total_num_thanks' WHERE UserName='$added_by'");
			} else {
				$is_new_account_query = mysqli_query($this->con, "UPDATE users SET IsNewAccount='no' WHERE UserName='$added_by'");
			}



			//for every ten posts, the user gets a thank 
			if($num_posts > 0 && $num_posts % 10 == 0) {
				$num_thanks = $this->user_obj->getNumThanks();
				$total_num_thanks = $this->user_obj->getTotalThanks();
				$num_thanks++;
				$total_num_thanks++;
				$thanks_query = mysqli_query($this->con, "UPDATE users SET NumThanks='$num_thanks', TotalNumThanks='$total_num_thanks' WHERE UserName='$added_by'");
			}

			header("Location: " . $_SERVER['REQUEST_URI']);


			/*
			//after a user makes a certain number of posts, their level increases by 1
			$player_level = $this->user_obj->getPlayerLevel();
			$num_thanks = $this->user_obj->getNumThanks();

			if($num_thanks >= 0 && $num_thanks % 50 == 0) {
				$player_level++;
				$level_query = mysqli_query($this->con, "UPDATE users SET PlayerLevel='$player_level' WHERE UserName='$added_by'");
			}
			*/


		}
	}

	public function loadPostsFriends($data, $limit) {

		$page = $data['page'];
		$userLoggedIn = $this->user_obj->getUsername();

		// echo $page;

		if($page == 1) { //if page is equal to 1 - which means that post has been loaded - start at the very first element
			$start = 0;
		//	echo $start;
		} else {
			$start = ($page - 1) * $limit; //if it is the second time loading a post for example, it will start at the 10th element
		//	echo $start;
		}


		$str = ""; //string to return
		$data_query = mysqli_query($this->con, "SELECT * FROM posts WHERE IsDeleted='no' ORDER BY PostID DESC"); //if deleted, we do not want to show it

		if(mysqli_num_rows($data_query) > 0) {


			$num_iterations = 0; //Number of results checked (not necasserily posted)
			$count = 1;

			while($row = mysqli_fetch_array($data_query)) {
				$id = $row['PostID'];
				$body = $row['Body'];
				$added_by = $row['AddedBy'];
				$date_time = $row['DateAdded'];
				$image_path = $row['PostImage'];

				//prepare user_to string so it can be included even if not posted to a user
				if($row['UserTo'] == "none") {
					$user_to = "";
				}
				else {
					$user_to_obj = new User($this->con, $row['UserTo']);
					$user_to_name = $user_to_obj->getFirstAndLastName();
					$user_to = "to <a href='" . $row['UserTo'] . "'>" . $user_to_name . "</a>"; //will return a link to the user's profile page, and their first/last name as the text
				}

				//Check if user who posted, has their account closed
				$added_by_obj = new User($this->con, $added_by);
				if($added_by_obj->isClosed()) {
					continue; //takes us straight back to the start of the loop, goes to the next iteration
				}

				$user_logged_obj = new User($this->con, $userLoggedIn);
				if($user_logged_obj->isFriend($added_by)){

					if($num_iterations++ < $start)
						continue; //if less than the position starting at


					//Once 10 posts have been loaded, break
					if($count > $limit) {
						break;
					}
					else {
						$count++;
					}

					if($userLoggedIn == $added_by) //if own post
					$delete_button = "<button class='delete_button btn-danger' id='post$id'>X</button>";
					else //if post does not belong to the user
					$delete_button = "";


					$user_details_query = mysqli_query($this->con, "SELECT FirstName, LastName, ProfilePicture FROM users WHERE UserName='$added_by'");
					$user_row = mysqli_fetch_array($user_details_query);
					$first_name = $user_row['FirstName'];
					$last_name = $user_row['LastName'];
					$profile_pic = $user_row['ProfilePicture'];


					?>
					<script> 
						function toggle<?php echo $id; ?>() { //how to know which comments to show
							

						var target = $(event.target); //this is where the person has clicked
						if(!target.is("a")) {

							var element = document.getElementById("toggleComment<?php echo $id; ?>");

							if(element.style.display == "block") {
								element.style.display = "none";
							} else {
								element.style.display = "block";
							}

						}


					}
				</script>
				<?php

				$comments_check = mysqli_query($this->con, "SELECT * FROM comments WHERE PostID='$id'");
				$comments_check_num = mysqli_num_rows($comments_check);


					//Timeframe
				$date_time_now = date("Y-m-d H:i:s");
				$start_date = new DateTime($date_time); //time post was made
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

				if($image_path != "") {
					$image_div = "<div class='posted_image'>
									<img src='$image_path'>
								</div>";
				} else {
					$image_div = "";
				}

				$str .= "<div class='status_post' onClick='javascript:toggle$id()'>
				<div class='post_profile_pic'>
				<img src='$profile_pic' width='50'>
				</div>

				<div class='posted_by' style='color:#ACACAC;'>
				<a href='$added_by'> $first_name $last_name </a> $user_to &nbsp;&nbsp;&nbsp;&nbsp;$time_message
				$delete_button
				</div>
				<div id='post_body'>
				$body
				$image_div
				<br>
				<br>
				<br>
				</div>

				<div class='newsfeedPostOptions'>
				$comments_check_num Comments &nbsp;&nbsp;&nbsp;
				<iframe src='thank_functionality.php?post_id=$id' scrolling='no'></iframe>
				</div>

				</div>
				<div class='post_comment' id='toggleComment$id' style='display:none;'>
				<iframe src='comment_frame.php?post_id=$id' id='comment_iframe' frameborder='0'></iframe>
				</div>
				<hr>";
			}

			?>
			<script>

				$(document).ready(function() {

					$('#post<?php echo $id; ?>').on('click', function() {
						bootbox.confirm("Are you sure you want to delete this post?", function(result) {

							$.post("includes/form_handlers/delete_post.php?post_id=<?php echo $id; ?>", {result:result});

							if(result)
								location.reload();

						});
					});


				});

			</script>
			<?php

			} //End while loop

			if($count > $limit) { //if reached the full amount of posts 
				$str .= "<input type='hidden' class='nextPage' value='" . ($page + 1) . "'>
							<input type='hidden' class='noMorePosts' value='false'>"; //hidden input field. won't be able to see it, but keeps track of values

			} else { //if the total number of posts stops at, say, five or six
				$str .= "<input type='hidden' class='noMorePosts' value='true'><p style='text-align: center;'>No more posts to show! </p>"; //hidden input field. won't be able to see it, but keeps track of values

			}
		}

		echo $str;
	}

	public function loadProfilePosts($data, $limit) {

		$page = $data['page'];
		$profileUser = $data['profileUsername'];
		$userLoggedIn = $this->user_obj->getUsername();

		//echo $page;

		if($page == 1) { //if page is equal to 1 - which means that post has been loaded - start at the very first element
			$start = 0;
			//echo $start;
		} else {
			$start = ($page - 1) * $limit; //if it is the second time loading a post for example, it will start at the 10th element
			//echo $start;
		}


		$str = ""; //string to return
		$data_query = mysqli_query($this->con, "SELECT * FROM posts WHERE IsDeleted='no' AND((AddedBy='$profileUser' AND UserTo='none') OR UserTo='$profileUser') ORDER BY PostID DESC"); //if deleted, we do not want to show it

		if(mysqli_num_rows($data_query) > 0) {


			$num_iterations = 0; //Number of results checked (not necasserily posted)
			$count = 1;

			while($row = mysqli_fetch_array($data_query)) {
				$id = $row['PostID'];
				$body = $row['Body'];
				$added_by = $row['AddedBy'];
				$date_time = $row['DateAdded'];

				if($num_iterations++ < $start)
						continue; //if less than the position starting at


					//Once 10 posts have been loaded, break
					if($count > $limit) {
						break;
					}
					else {
						$count++;
					}

					if($userLoggedIn == $added_by) //if own post
					$delete_button = "<button class='delete_button btn-danger' id='post$id'>X</button>";
					else //if post does not belong to the user
					$delete_button = "";


					$user_details_query = mysqli_query($this->con, "SELECT FirstName, LastName, ProfilePicture FROM users WHERE UserName='$added_by'");
					$user_row = mysqli_fetch_array($user_details_query);
					$first_name = $user_row['FirstName'];
					$last_name = $user_row['LastName'];
					$profile_pic = $user_row['ProfilePicture'];


					?>
					<script> 
						function toggle<?php echo $id; ?>() { //how to know which comments to show
							

						var target = $(event.target); //this is where the person has clicked
						if(!target.is("a")) {

							var element = document.getElementById("toggleComment<?php echo $id; ?>");

							if(element.style.display == "block") {
								element.style.display = "none";
							} else {
								element.style.display = "block";
							}

						}


					}
				</script>
				<?php

				$comments_check = mysqli_query($this->con, "SELECT * FROM comments WHERE PostID='$id'");
				$comments_check_num = mysqli_num_rows($comments_check);


					//Timeframe
				$date_time_now = date("Y-m-d H:i:s");
				$start_date = new DateTime($date_time); //time post was made
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
						$time_message = $interval->m . " month " . $days;
					}
					else {
						$time_message = $interval->m . " months " . $days;
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


				$str .= "<div class='status_post' onClick='javascript:toggle$id()'>
				<div class='post_profile_pic'>
				<img src='$profile_pic' width='50'>
				</div>

				<div class='posted_by' style='color:#ACACAC;'>
				<a href='$added_by'> $first_name $last_name </a> &nbsp;&nbsp;&nbsp;&nbsp;$time_message
				$delete_button
				</div>
				<div id='post_body'>
				$body
				<br>
				<br>
				<br>
				</div>

				<div class='newsfeedPostOptions'>
				$comments_check_num Comments &nbsp;&nbsp;&nbsp;
				<iframe src='thank_functionality.php?post_id=$id' scrolling='no'></iframe>
				</div>

				</div>
				<div class='post_comment' id='toggleComment$id' style='display:none;'>
				<iframe src='comment_frame.php?post_id=$id' id='comment_iframe' frameborder='0'></iframe>
				</div>
				<hr>";
				

				?>
				<script>

					$(document).ready(function() {

						$('#post<?php echo $id; ?>').on('click', function() {
							bootbox.confirm("Are you sure you want to delete this post?", function(result) {

								$.post("includes/form_handlers/delete_post.php?post_id=<?php echo $id; ?>", {result:result});

								if(result)
									location.reload();

							});
						});


					});

				</script>
				<?php

			} //End while loop

			if($count > $limit) { //if reached the full amount of posts 
				$str .= "<input type='hidden' class='nextPage' value='" . ($page + 1) . "'>
							<input type='hidden' class='noMorePosts' value='false'>"; //hidden input field. won't be able to see it, but keeps track of values

			} else { //if the total number of posts stops at, say, five or six
				$str .= "<input type='hidden' class='noMorePosts' value='true'><p style='text-align: center;'>No more posts to show! </p>"; //hidden input field. won't be able to see it, but keeps track of values

			}
		}

		echo $str;
	}

	public function getSinglePost($post_id) {

		$userLoggedIn = $this->user_obj->getUsername();
		
		$opened_query = mysqli_query($this->con, "UPDATE notifications SET IsOpened='yes' WHERE UserTo='$userLoggedIn' AND NotificationLink LIKE '%=$post_id'");


		$str = ""; //string to return
		$data_query = mysqli_query($this->con, "SELECT * FROM posts WHERE IsDeleted='no' AND PostID='$post_id'"); //if deleted, we do not want to show it

		if(mysqli_num_rows($data_query) > 0) {



			$row = mysqli_fetch_array($data_query);
			$id = $row['PostID'];
			$body = $row['Body'];
			$added_by = $row['AddedBy'];
			$date_time = $row['DateAdded'];

				//prepare user_to string so it can be included even if not posted to a user
			if($row['UserTo'] == "none") {
				$user_to = "";
			}
			else {
				$user_to_obj = new User($this->con, $row['UserTo']);
				$user_to_name = $user_to_obj->getFirstAndLastName();
					$user_to = "to <a href='" . $row['UserTo'] . "'>" . $user_to_name . "</a>"; //will return a link to the user's profile page, and their first/last name as the text
				}

				//Check if user who posted, has their account closed
				$added_by_obj = new User($this->con, $added_by);
				if($added_by_obj->isClosed()) {
					return; //takes us straight back to the start of the loop, goes to the next iteration
				}

				$user_logged_obj = new User($this->con, $userLoggedIn);
				if($user_logged_obj->isFriend($added_by)){



					if($userLoggedIn == $added_by) //if own post
					$delete_button = "<button class='delete_button btn-danger' id='post$id'>X</button>";
					else //if post does not belong to the user
					$delete_button = "";


					$user_details_query = mysqli_query($this->con, "SELECT FirstName, LastName, ProfilePicture FROM users WHERE UserName='$added_by'");
					$user_row = mysqli_fetch_array($user_details_query);
					$first_name = $user_row['FirstName'];
					$last_name = $user_row['LastName'];
					$profile_pic = $user_row['ProfilePicture'];


					?>
					<script> 
						function toggle<?php echo $id; ?>() { //how to know which comments to show
							

						var target = $(event.target); //this is where the person has clicked
						if(!target.is("a")) {

							var element = document.getElementById("toggleComment<?php echo $id; ?>");

							if(element.style.display == "block") {
								element.style.display = "none";
							} else {
								element.style.display = "block";
							}

						}


					}
				</script>
				<?php

				$comments_check = mysqli_query($this->con, "SELECT * FROM comments WHERE PostID='$id'");
				$comments_check_num = mysqli_num_rows($comments_check);


					//Timeframe
				$date_time_now = date("Y-m-d H:i:s");
				$start_date = new DateTime($date_time); //time post was made
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

				$str .= "<div class='status_post' onClick='javascript:toggle$id()'>
				<div class='post_profile_pic'>
				<img src='$profile_pic' width='50'>
				</div>

				<div class='posted_by' style='color:#ACACAC;'>
				<a href='$added_by'> $first_name $last_name </a> $user_to &nbsp;&nbsp;&nbsp;&nbsp;$time_message
				$delete_button
				</div>
				<div id='post_body'>
				$body
				<br>
				<br>
				<br>
				</div>

				<div class='newsfeedPostOptions'>
				$comments_check_num Comments &nbsp;&nbsp;&nbsp;
				<iframe src='thank_functionality.php?post_id=$id' scrolling='no'></iframe>
				</div>

				</div>
				<div class='post_comment' id='toggleComment$id' style='display:none;'>
				<iframe src='comment_frame.php?post_id=$id' id='comment_iframe' frameborder='0'></iframe>
				</div>
				<hr>";


				?>
				<script>

					$(document).ready(function() {

						$('#post<?php echo $id; ?>').on('click', function() {
							bootbox.confirm("Are you sure you want to delete this post?", function(result) {

								$.post("includes/form_handlers/delete_post.php?post_id=<?php echo $id; ?>", {result:result});

								if(result)
									location.reload();

							});
						});


					});

				</script>

				<?php

			}

			else {
					echo "<p>You cannot see this post because you are not friends with this user</p>"; //else statement is without the above script
					return;
				}

			} 
			else {
		echo "<p>No post found. If you clicked a link, it may be broken.</p>"; //else statement is without the above script
		return;
	}

	echo $str;

}
}




?>