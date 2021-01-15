<?php 
include("includes/header.php");

$message_obj = new Message($con, $userLoggedIn);

if(isset($_GET['profile_username'])) {
  $username = $_GET['profile_username'];
  $user_details_query = mysqli_query($con, "SELECT * FROM users WHERE UserName='$username'");
  $user_array = mysqli_fetch_array($user_details_query);

	$num_friends = (substr_count($user_array['FriendArray'], ",")) - 1; //if there is just a comma, it will return 1

	}


//code for the "Remove Friend" button - appears on a user's profile if the current user is already friends with them
	if(isset($_POST['remove_friend'])) {
		$user = new User($con, $userLoggedIn);
		$user->removeFriend($username);
	}


//code for the "Add Friend" button - appears on a user's profile by default, i.e. if they have not yet sent a request
	if(isset($_POST['add_friend'])) {
		$user = new User($con, $userLoggedIn);
		$user->sendRequest($username);
	}

//code for the "Respond to request" button - only appears on a user's profile if another person has sent them a friend request
	if(isset($_POST['respond_request'])) {
		header("Location: friend_requests.php");
	}


//code for the "Level Up" button
  if(isset($_POST['level_up'])) {
    $user = new User($con, $userLoggedIn);
    $num_thanks = $user->getNumThanks();
    $user_level = $user->getLevel();


    if($num_thanks >= 30) {
      $user_level++;
      $num_thanks -= 30;
      $level_query = mysqli_query($con, "UPDATE users SET PlayerLevel='$user_level' WHERE UserName='$username'");
      $reset_thanks_query = mysqli_query($con, "UPDATE users SET NumThanks='$num_thanks' WHERE UserName='$username'");
      header("Location: $username");
      echo "<div class='level_up_message'>Congratulations! Your level has gone up!</div>";
    } else {
      echo "<div class='level_up_message'>You have not gained enough Thanks to level up.</div>";
    }

  }


  if(isset($_POST['post_message'])) { //if button is pressed
    if(isset($_POST['message_body'])) { //if there is something in the body
      $body = mysqli_real_escape_string($con, $_POST['message_body']);
      $date = date("Y-m-d H:i:s");
      $message_obj->sendMessage($username, $body, $date);
      header("Location: $username");
    } 
/*

*/
   $link = '#profileTabs a[href="#messages_div"]';
  echo "<script> 
          $(function() {
              $('" . $link ."').tab('show');
          });
        </script>";

  }


  if(isset($_POST['send_thanks'])) {
    $date = date("Y-m-d-H:i:s");
    $message_obj->sendThanks($date);

     $link = '#profileTabs a[href="#messages_div"]';

    echo "<script>
    $(function() {
      $('" . $link . "').tab('show');
      });
      </script>";

   header("Location: $username");


  }


    ?>

    <style type="text/css">
     .wrapper {
      margin-left: 0px;
      padding-left: 0px;
    }
  </style>

  <div class="profile_left">
   <img src="<?php echo $user_array['ProfilePicture']; ?>">

   <div class="profile_info">
    <p><?php echo "Posts: " . $user_array['NumPosts']; ?></p>
    <p><?php echo "Current Thanks: " . $user_array['NumThanks']; ?></p>
    <p><?php echo "Total Thanks: " . $user_array['TotalNumThanks']; ?></p>
    <p><?php echo "Friends: " . $num_friends; ?></p>
    <p><?php echo "Level: " . $user_array['PlayerLevel']; ?></p>
    <p><?php 
        if($user_array['PlayerLevel'] < 4) {
            if($user_array['NumThanks'] < 30) {
                echo (30 - $user_array['NumThanks']) . " more Thanks until Level " . ($user_array['PlayerLevel'] + 1);
            } else {
                 echo "0 more Thanks until Level " . ($user_array['PlayerLevel'] + 1);
            }
        } else {
            echo "You have reached the maximum level! Good job!";
        }?></p>
  </div>


  <form action="<?php echo $username; ?>" method="POST">
    <?php 
    $profile_user_obj = new User($con, $username); 
    if($profile_user_obj->isClosed()) {
     header("Location: user_closed.php");
   }

   $logged_in_user_obj = new User($con, $userLoggedIn);

 			//check if user is on their own profile

   if($userLoggedIn != $username) {

 				if($logged_in_user_obj->isFriend($username))  { //if true, the two users are friends 
 					echo '<input type="submit" name="remove_friend" class="danger" value="Remove Friend"><br>';
 				}
 				else if ($logged_in_user_obj->didRecieveRequest($username)) { //if other user has recieved friend request 
 					echo '<input type="submit" name="respond_request" class="warning" value="Respond to request"><br>';	
 				}
 				else if ($logged_in_user_obj->didSendRequest($username)) {  //if true, the current user has already sent a request to the other user
 					echo '<input type="submit" name="" class="default" value="Request Sent"><br>';	
 				}
 				else { //default
 					echo '<input type="submit" name="add_friend" class="default" value="Add Friend"><br>';
 				}
 			}

 			?>
    </form>
    <input type="submit" class="deep_blue" data-toggle="modal" data-target="#post_form" value="Post Something">

    <?php 
      if($userLoggedIn != $username) { //if user is not in their own profile
        echo '<div class="profile_info_bottom">';
        echo $logged_in_user_obj->getMutualFriends($username) . " mutual friends";
        echo '</div>';
      } else {
        $current_level = $user_array['PlayerLevel'];

        //image of their achievements will appear on a user's profile
        switch($current_level) {
          case 1:
            echo '<img id="level_up_sign" src="assets/images/level_up_pics/novice.png">';
            break;
          case 2:
            echo '<img id="level_up_sign" src="assets/images/level_up_pics/adept.png">';
            break;
          case 3:
            echo '<img id="level_up_sign" src="assets/images/level_up_pics/expert.png">';
            break;
          case 4:
            echo '<img id="level_up_sign" src="assets/images/level_up_pics/master.png">';
        }

        //if the number of Thanks a user has reaches or exceeds 30, the "Level Up" button will change color to symbolise that the respective function is now available to them
        echo ($user_array['NumThanks'] == 30) ?
         '<form action="" method="POST"><input type="submit" name="level_up" class="success" value="Level Up"></form><br>' :
         '<form action="" method="POST"><input type="submit" name="level_up" class="danger" value="Level Up"></form><br>';
      }



      ?>


    </div>
    

    <div class="profile_main_column column">

      <ul class="nav nav-tabs" role="tablist" id="profileTabs">
       <li role="presentation" class="active"><a href="#newsfeed_div" aria-controls="newsfeed_div" role="tab" data-toggle="tab">Newsfeed</a></li>
       <li role="presentation"><a href="#messages_div" aria-controls="messages_div" role="tab" data-toggle="tab">Messages</a></li>
     </ul>

     <div class="tab-content">

      <div role="tabpanel" class="tab-pane fade in active" id="newsfeed_div">
        <div class="posts_area"></div>
        <img id="loading" src="assets/images/icons/loading.gif">
      </div>

      <div role="tabpanel" class="tab-pane fade" id="messages_div">
        <?php


        echo "<h4> You and <a href='" . $username . "'>" . $profile_user_obj->getFirstAndLastName() . "</a></h4><hr><br>";
        echo "<div class='loaded_messages' id='scroll_messages'>";
        echo $message_obj->getMessages($username);
        echo "</div>";

        ?>




        <div class="message_post">
          <form action="" method="POST">
            <textarea name='message_body' id='message_textarea' placeholder='Write your message ...'></textarea>
            <input type='submit' name='post_message' class='info' id='message_submit' value='Send Message'>
            <input type='submit' name='send_thanks' class='info' id='thanks_submit' value='Send Thanks'>

          </form>
        </div>

        <script>
          var div = document.getElementById("scroll_messages");

          if(div != null) {
            div.scrollTop = div.scrollHeight;
          }
        </script>
      </div>


    </div>

    
  </div>


  <!-- Modal -->
  <div class="modal fade" id="post_form" tabindex="-1" role="dialog" aria-labelledby="postModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="myModalLabel">Post something</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <p>This will appear on the user's profile page and also their newsfeed for your friends to see.</p>
          
          <form class="profile_post" action="" method="POST">
           <div class="form-group">
            <textarea class="form_control" name="post_body"></textarea>
            <input type="hidden" name="user_from" value="<?php echo $userLoggedIn; ?>">
            <input type="hidden" name="user_to" value="<?php echo $username; ?>">
          </form>
          
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <button type="button" class="btn btn-primary" name="post_button" id="submit_profile_post">Post</button>
        </div>
      </div>
    </div>
  </div>

  
  <script>
   var userLoggedIn = '<?php echo $userLoggedIn; ?>';
   var username = '<?php echo $username; ?>';
   $(function(){
     var inProgress = false;
       loadPosts(); //Load first posts
       $(window).scroll(function() {
         var bottomElement = $(".status_post").last();
         var noMorePosts = $('.posts_area').find('.noMorePosts').val();
           // isElementInViewport uses getBoundingClientRect(), which requires the HTML DOM object, not the jQuery object. The jQuery equivalent is using [0] as shown below.
           if (isElementInView(bottomElement[0]) && noMorePosts === 'false') {
             loadPosts();
           }
         });
       function loadPosts() {
           if(inProgress) { //If it is already in the process of loading some posts, just return
             return;
           }
           
           inProgress = true;
           $('#loading').show();
           var page = $('.posts_area').find('.nextPage').val() || 1; //If .nextPage couldn't be found, it must not be on the page yet (it must be the first time loading posts), so use the value '1'
           $.ajax({
             url: "includes/handlers/ajax_load_profile_posts.php",
             type: "POST",
             data: "page=" + page + "&userLoggedIn=" + userLoggedIn + "&profileUsername=" + username,
             cache: false,
             success: function(response) {
                   $('.posts_area').find('.nextPage').remove(); //Removes current .nextpage
                   $('.posts_area').find('.noMorePosts').remove(); 
                   $('#loading').hide();
                   $(".posts_area").append(response);
                   
                   inProgress = false;
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
               rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) && //* or $(window).height()
               rect.right <= (window.innerWidth || document.documentElement.clientWidth) //* or $(window).width()
               );
      }
    });
  </script>



</div>
</body>
</html>