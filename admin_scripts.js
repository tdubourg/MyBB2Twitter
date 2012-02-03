/*** Character Counter ***/
jQuery(document).ready(function() {
	jQuery('#tweet').each(function(){
		var length = jQuery(this).val().length;
		var cur = 140 - length;
		jQuery(this).parent().find('#chars-left').html( '<strong>' + cur + '</strong> characters left');
		jQuery(this).keyup(function(){
			var new_length = jQuery(this).val().length;
			var new_cur = 140 - new_length;
			jQuery(this).parent().find('#chars-left').html( '<strong>' + new_cur + '</strong> characters left');
		});
	});
});



/*** Reply/Retweet Buttons ***/
jQuery(document).ready(function() {

	jQuery(".reply").click(function() {
		reply_to_status = jQuery(this).parents().filter('li.status').attr('id');
		reply_to_user = jQuery('#'+reply_to_status+' a.user').html();
		jQuery('#tweet').val('@'+reply_to_user+' ');
		jQuery('#in_reply_to_user').val(reply_to_user);
		jQuery('#in_reply_to_status').val(reply_to_status);
	});
	
	jQuery('#tweet').keyup(function(){
		var length = jQuery('#tweet').val().length;
		if (length == 0) {
			jQuery('#in_reply_to_user').val('');
			jQuery('#in_reply_to_status').val('');
		}
	});
	
		jQuery(".retweet").click(function() {
		reply_to_status = jQuery(this).parents().filter('li.status').attr('id');
		reply_to_user = jQuery('#'+reply_to_status+' a.user').html();
		tweet = jQuery('#'+reply_to_status+' span.status-text').html();
		tweet = tweet.replace(/(<([^>]+)>)/ig,""); 
		jQuery('#tweet').val('RT @'+reply_to_user+' '+tweet);
	});
	
});



/*** Update Status ***/
jQuery(document).ready(function() {

	jQuery("#update-status").click(function() {
		if (jQuery('#tweet').val().length > 140) {
			alert("Your message is too long. You must shorten it down to 140 characters.");
			return false;
		}
		jQuery('#update-status').val('Sending...');
		jQuery('#loading-send-tweet').show();
		tweet = jQuery('#tweet').val();
		in_reply_to_status = jQuery('#in_reply_to_status').val();
		do_action = jQuery('#do_action').val();
		token = jQuery('#js_token').val();
		post_to = jQuery('#post_to').val();
		datastring = 'tweet=' + tweet + '&in_reply_to_status=' + in_reply_to_status + '&do=' + do_action + '&token=' + token;
		jQuery.ajax({
			type: "POST",
			url: post_to,
			data: datastring,
			success: function(data) {
				jQuery('#my-latest-status').html(data);
				jQuery('#update-status').val('Update Status');
				jQuery('#tweet').val('');
				jQuery('#loading-send-tweet').fadeOut("slow");
			}
		});
		return false;
	});

});



/*** Shorten URLs ***/
jQuery(document).ready(function() {

	jQuery("#shorten-url").click(function() {
		theurl = prompt("Enter the URL to be shortened", "http://");
		if (theurl != null && theurl != "" && theurl != "http://" && theurl != false) {
			loading = setInterval(function() {
	     		jQuery('#shorten-url').toggleClass('tw-hide');
			}, 500);
			post_to = jQuery('#post_to').val();
			datastring = 'theurl=' + theurl + '&do=shorten-url';
			jQuery.ajax({
				type: "POST",
				url: post_to,
				data: datastring,
				success: function(data) {
					jQuery('#tweet').val(jQuery('#tweet').val()+' '+data);
					clearInterval(loading);
					jQuery('#shorten-url').removeClass('tw-hide');
				}
			});
		}
	});

});



/*** Add New Search ***/
jQuery(document).ready(function() {

	jQuery("#add-search").click(function() {
		jQuery('#loading-add-search').show();
		thekeyword = jQuery('#twitter-search-input').val();
		if (thekeyword != null && thekeyword != "") {
			post_to = jQuery('#post_to').val();
			datastring = 'thekeyword=' + thekeyword + '&do=search-add';
			jQuery.ajax({
				type: "POST",
				url: post_to,
				data: datastring,
				success: function(data) {
					jQuery('#loading-add-search').fadeOut("slow");
					jQuery('#twitter-search-input').val('');
					location.reload();
				}
			});
		}
		return false;
	});

});



/*** Delete Search ***/
jQuery(document).ready(function() {

	jQuery(".delete-search").click(function() {
		thesearch = jQuery(this).parents().filter('div.search-div').attr('id');
		dodelete = confirm("Delete this search?");
		if (thesearch != null && thesearch != "" && dodelete) {
			post_to = jQuery('#post_to').val();
			datastring = 'thesearch=' + thesearch + '&do=search-delete';
			jQuery.ajax({
				type: "POST",
				url: post_to,
				data: datastring,
				success: function(data) {
					//location.reload();
					jQuery("#"+thesearch).fadeOut("slow");
				}
			});
		}
		return false;
	});

});



