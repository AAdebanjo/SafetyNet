$(document).ready(function() {

	$('#search_text_input').focus(function() {
			if(window.matchMedia( "(min-width: 800px)" ).matches) { //if the window has a width of 800px or larger, this will validate to true
				$(this).animate({width: '250px'}, 500);
				//currently targeting 'this' current object, which in this case is the input field
				//second parameter determines the speed that the statment will be executed
			}
		});

	$('button_loader').on('click', function() {
			document.search_form.submit(); //name of the form on header.php
		});

		//ajax call that is going to submit the form for us 
		//Button for profile post

		$('#submit_profile_post').click(function() {

			$.ajax({
				type: "POST",
				url: "includes/handlers/ajax_submit_profile_post.php",
				data: $('form.profile_post').serialize(),
				success: function(msg) {
					$("#post_form").modal('hide');
					location.reload();
				},
				error: function() {
					alert('Failure');
				}
			});


		});


	});


//function for the notification/search results to disappear after clicking away from the page
$(document).click(function(e) {
	if(e.target.class != "search_results" && e.target.id != "search_text_input") { //"target" is what has been clicked on, and "class" is the class of the target
		$(".search_results").html(""); //remove all the html data
	$('.search_results_footer').html("");
	$('.search_results_footer').toggleClass("search_results_footer_empty");
	$('.search_results_footer').toggleClass("search_results_footer");
}

	if(e.target.class != "dropdown_data_window") { //"target" is what has been clicked on, and "class" is the class of the target
		$(".dropdown_data_window").html(""); //remove all the html data
		//need to remove the css elements as well
		$(".dropdown_data_window").css({"padding" :  "0px", "height" : "0px"});
	}
});

//send a request to ajax_friend_search.php with two values
//when it returns, it will set the value of the "results" div with the contents of what was in data was returned
function getUsers(value, user) {
	$.post("includes/handlers/ajax_friend_search.php", {query:value, userLoggedIn:user}, function(data) {
		$(".results").html(data);
	});
}


function getDropdownData(user, type) {

	if($(".dropdown_data_window").css("height") == "0px") { //if the height css property is 0

		var pageName;

		if(type == 'notification') {
			pageName = "ajax_load_notifications.php";
			$("span").remove("#unread_notification");
		}
		else if (type == 'message') {
			pageName = "ajax_load_messages.php";
			$("span").remove("#unread_message");
		}

		var ajaxreq = $.ajax({
			url: "includes/handlers/" + pageName, //where we'll send the data to
			type: "POST",
			data: "page=1&userLoggedIn=" + user,
			cache: false,

			success: function(response) { //where we'll append the messages to the appropriate div
				$(".dropdown_data_window").html(response); //will return all of the messages that the 'response' parameter returned
				$(".dropdown_data_window").css({"padding" : "0px", "height": "280px", "border" : "1px solid #DADADA"});
				$("#dropdown_data_type").val(type);
			}

		});

	}
	else {
		//if the notification bar is already open
		$(".dropdown_data_window").html("");
		$(".dropdown_data_window").css({"padding" : "0px", "height": "0px", "border" : "none"});
	}

}


function getLiveSearchUsers(value, user) {

	//what's it doing? it's sending the data to ajax_search.php with two parameters, value and user
	$.post("includes/handlers/ajax_search.php", {query:value, userLoggedIn:user}, function(data) {

		if($(".search_results_footer_empty")[0]) {
			$(".search_results_footer_empty").toggleClass("search_results_footer"); //if it's on the page, remove. If it's hidden, show
			$(".search_results_footer_empty").toggleClass("search_results_footer_empty");
		}

		$('.search_results').html(data);
		$('.search_results_footer').html("<a href='search.php?q=" + value + "'>See All Results</a>");

		if(data == "") { //if whatever is typed in returns nothing
			$('.search_results_footer').html("");
			$('.search_results_footer').toggleClass("search_results_footer_empty");
			$('.search_results_footer').toggleClass("search_results_footer");
		}

	});
}