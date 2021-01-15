<?php 
include("includes/header.php");



if(isset($_POST['post'])){



	$can_be_uploaded = 1; //check if the file is ready to be uploaded, or if there is an error
	$image_name = $_FILES['file_upload']['name']; //allows us to get file information. 'name' retrieves the name of the image
	$error_message = "";

	if($image_name != "") {
		$target_directory = "assets/images/posts/";
		$image_name = $target_directory . uniqid() . basename($image_name); //unique identifier prevents users with identical image post names from overriding each other
		$image_file_type = pathinfo($image_name, PATHINFO_EXTENSION); //allows us to retrieve file extensions

		if($_FILES['file_upload']['size'] > 10000000) {
			$error_message = "Your file is too large to be uploaded.";
			$can_be_uploaded = 0;
		}

		if(strtolower($image_file_type) != "jpeg" && strtolower($image_file_type) != "jpg" && strtolower($image_file_type) != "png") {
			$error_message = "Only JPG and PNG files can be uploaded.";
			$can_be_uploaded = 0;
		}

		if($can_be_uploaded == 1) {
			if(move_uploaded_file($_FILES['file_upload']['tmp_name'], $image_name)) {
				//image uploaded okay
			} else {
				$can_be_uploaded = 0;
				//image did not upload
			}
		}
	} 

	if($can_be_uploaded == 1) {
		$post = new Post($con, $userLoggedIn);
		$post->submitPost($_POST['post_text'], 'none', $image_name);
	} else {
		echo "<div style='text-align:center;' class='alert alert-danger'>
				$error_message
			</div>";
	}



	//$post = new Post($con, $userLoggedIn);
	//$post->submitPost($_POST['post_text'], 'none', $image_name);
    //header("Location: index.php"); //when post is submitted, the page will essentially refresh
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

		<?php echo "Level " . $user['PlayerLevel'] . "<br>";
		 echo "Posts: " . $user['NumPosts'] . "<br>"; 
		 echo "Current Thanks: " . $user['NumThanks'];

		?>
	</div>

</div>

<div class="main_column column">
	<form class="post_form" action="index.php" method="POST" enctype="multipart/form-data">
		<input type="file" name="file_upload" id="file_upload">
		<textarea name="post_text" id="post_text" placeholder="Share a post, video, or news report."></textarea>
		<input type="submit" name="post" id="post_button" value="Post">
		<br>

	</form>

	<div class="posts_area"></div> <!-- where the posts will be loaded -->
	<img id="loading" src="assets/images/icons/loading.gif">

</div>


<script>
	$(function(){

		var userLoggedIn = '<?php echo $userLoggedIn; ?>';
		var inProgress = false;

	loadPosts(); //Load first posts

	$(window).scroll(function() {
		var bottomElement = $(".status_post").last();
		var noMorePosts = $('.posts_area').find('.noMorePosts').val();

        // isElementInViewport uses getBoundingClientRect(), which requires the HTML DOM object, not the jQuery object. The jQuery equivalent is using [0] as shown below.
        if (isElementInView(bottomElement[0]) && noMorePosts == 'false') {
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
			url: "includes/handlers/ajax_load_posts.php",
			type: "POST",
			data: "page=" + page + "&userLoggedIn=" + userLoggedIn,
			cache:false,

			success: function(response) {
				$('.posts_area').find('.nextPage').remove(); //Removes current .nextpage 
				$('.posts_area').find('.noMorePosts').remove(); //Removes current .nextpage 
				$('.posts_area').find('.noMorePostsText').remove(); //Removes current .nextpage 

				$('#loading').hide();
				$(".posts_area").append(response);

				inProgress = false;
			}
		});
	}

    //Check if the element is in view
    function isElementInView (el) {
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