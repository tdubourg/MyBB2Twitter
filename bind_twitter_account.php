<?php

define("IN_MYBB", 1);
require_once "../../../global.php";


require_once "mh_twitter_class.php";
require_once "OAuth/twitterOAuth.php";
require_once "is_serialized.php";
require_once "MyBB2Twitter/functions.php";

?>
<div class="wrap">

<?php
if (!empty($_GET['reset_account']) && $_GET['step']=='1') {
	echo '<div id="message" class="updated fade"><p>If you are only changing your Twitter account, you can skip this screen and use the same application you registered before.</p></div>';
}
?>

<h2>MyBB2Twitter Setup</h2>
<div style="width:700px;">

<?php

if ($_POST) {
	if ($_GET['step'] == '3') {
		if ($_POST['app_key'] != '' && $_POST['app_key_secret'] != '') {
			MyBB2Twitter_update_option('MyBB2Twitter_app_key', $_POST['app_key']);
			MyBB2Twitter_update_option('MyBB2Twitter_app_key_secret', $_POST['app_key_secret']);
		} else {
				$_GET['step'] = '2';
		}
	}
	if ($_GET['step'] == '4') {
		if ($_POST['twitter_user'] != '') {
			MyBB2Twitter_update_option('MyBB2Twitter_twitter_user', $_POST['twitter_user']);
		} else {
			$_GET['step'] = '3';
		}
	}
}

if ($_GET['oauth']=='1') {
	$_GET['step'] = '5';
}

if ($_GET['step']) {
	MyBB2Twitter_update_option('MyBB2Twitter_install_stage', $_GET['step']);
}

$stage = MyBB2Twitter_get_option('MyBB2Twitter_install_stage');
if ($stage == NULL) {
	MyBB2Twitter_add_option('MyBB2Twitter_install_stage', '1');
	$stage = '1';
	MyBB2Twitter_update_option('MyBB2Twitter_install_stage', 1);
}

// $plugin_dir = WP_CONTENT_URL.'/plugins/'.plugin_basename(dirname(__FILE__));
$next_step = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
$next_step_split = explode('&step', $next_step);
$next_step = htmlentities($next_step_split[0]);
echo $next_step;


if ($stage == '1') {

	?>
	<h3>Step 1: Register your MyBB forum with Twitter</h3>
	<p>For security reasons, MyBB2Twitter uses Twitter's OAuth protocol to authenticate and access your Twitter account. For it to work, you must register your MyBB forum as an application with Twitter. Click the button below to go to Twitter.com and register it.</p>
	<p style="text-align:center"><a class="button" href="http://twitter.com/oauth_clients/new" target="_blank">Register Application</a></p>
	<p>When you arrive at Twitter, click the &quot;Register a new application,&quot; you will see a form like the one pictured below. You will have to fill-out a few fields.</p>
	<p style="text-align:center"><img src="<?php echo $plugin_dir ?>/images/wizard_1.jpg" alt="Twitter Form" /></p>
	<p>You do not need to upload an icon. It's entirely optional, and chances are nobody will see it.</p>
	<ol style="list-style:decimal; margin: 0 25px 15px 25px;">
	<li>Enter your MyBB forum's name in the <strong>Application Name</strong> field.</li>
	<li>Put a brief description of your MyBB forum in the <strong>Description</strong> box. Just a few words to identify the application.</li>
	<li>Your MyBB forum's URL should go in the <strong>Application Website</strong> field. (E.g. <em>http://www.webmaster-source.com.</em>)</li>
	<li>Leave the <strong>Application Type</strong> as <strong>Browser.</strong></li>
	<li>In the <strong>Callback URL</strong> field, paste <strong><code>http://<?php echo $mybb->settings['bburl'].'/inc/plugins/MyBB2Twitter/bind_twitter_account.php'; ?>?oauth=1</code></strong></li>
	<li>Set <strong>Default Access type</strong> as <strong>Read &amp; Write.</strong></li>
	<li>Set <strong>Use Twitter for logging</strong> to <strong>No.</strong></li>
	<li>Press the <strong>Save</strong> button, then come back here and continue the setup wizard.</li>
	</ol>
	<p style="text-align:right"><a class="button" href="?step=2">Continue...</a></p>
	<?php

}



if ($stage == '2') {

	?>
	<h3>Step 2: Enter the Application Keys</h3>
	<p>To enable WordPress to interface with the Twitter API, you must enter the application consumer keys. You can find them by visiting <a href="http://twitter.com/oauth_clients/" target="_blank">this page on Twitter</a> and selecting the application you registered in the previous step.</p>
	<p style="text-align:center"><img src="<?php echo $plugin_dir ?>/images/wizard_2.jpg" alt="Finding the Application Keys" /></p>
	<p>Enter the <strong>Consumer Key</strong> and <strong>Consumer Secret</strong> key in the form below.</p>
	<form method="post" action="<?php echo $next_step; ?>&amp;step=3">
	<table class="form-table">
		<tr valign="top">
		<th scope="row"><label for="app_key">Consumer Key</label></th>
		<td><input name="app_key" type="text" id="app_key" value="<?php echo MyBB2Twitter_get_option('MyBB2Twitter_app_key'); ?>" class="regular-text" /></td>
		</tr>
		<tr valign="top">
		<th scope="row"><label for="app_key_secret">Consumer Secret</label></th>
		<td><input name="app_key_secret" type="text" id="app_key_secret" value="<?php echo MyBB2Twitter_get_option('MyBB2Twitter_app_key_secret'); ?>" class="regular-text" /></td>
		</tr>
	</table>
	<input type="hidden" name="save_app_keys" value="yes" />
	<p class="submit" style="text-align:right"><input type="submit" name="Submit" class="button-primary" value="Save and Continue" /></p>
	</form>
	<?php

}



if ($stage == '3') {

	?>
	<h3>Step 3: Enter Your Twitter Username</h3>
	<p>Tweetable will need to know your Twitter username for some of it's functions.</p>
	<form method="post" action="<?php echo $next_step; ?>&amp;step=4">
	<table class="form-table">
		<tr valign="top">
		<th scope="row"><label for="twitter_user">Twitter Username</label></th>
		<td><input name="twitter_user" type="text" id="twitter_user" value="" class="regular-text" /></td>
		</tr>
	</table>
	<input type="hidden" name="save_twitter_user" value="yes" />
	<p class="submit" style="text-align:right"><input type="submit" name="Submit" class="button-primary" value="Save and Continue" /></p>
	</form>
	<?php

}



if ($stage == '4') {

	$twitter = new Twitter_API(MyBB2Twitter_get_option('MyBB2Twitter_app_key'), MyBB2Twitter_get_option('MyBB2Twitter_app_key_secret'));
	$request_link = $twitter->oauth_authorize_link();
	MyBB2Twitter_update_option('MyBB2Twitter_request_oauth', $request_link);

	?>
	<h3>Step 4: Authorize Your Twitter Account</h3>
	<p>Almost done! Now you need to authorize your MyBB forum to have access to your Twitter account.</p>
	<p>Click the button below, and you will be taken to Twitter.com. If you're already logged in, you will be presented with the option to authorize your MyBB forum. Press the button to do so, and you will come right back here.</p>
	<p style="text-align:center"><a href="<?php echo $request_link['request_link']; ?>"><img src="<?php echo $plugin_dir ?>/images/twitter_connect.png" alt="Sign in with Twitter" /></a></p>
	<?php

}



if ($stage == '5') {

	$request_link = MyBB2Twitter_get_option('MyBB2Twitter_request_oauth');
	$twitter = new Twitter_API(MyBB2Twitter_get_option('MyBB2Twitter_app_key'), MyBB2Twitter_get_option('MyBB2Twitter_app_key_secret'));
	$tokens = $twitter->oauth_get_user_token($request_link['request_token'], $request_link['request_token_secret']);
	$access_token = $tokens['access_token'];
	$access_token_secret = $tokens['access_token_secret'];
	MyBB2Twitter_update_option('MyBB2Twitter_access_token', $access_token);
	MyBB2Twitter_update_option('MyBB2Twitter_access_token_secret', $access_token_secret);
	MyBB2Twitter_delete_option('MyBB2Twitter_request_oauth');
	MyBB2Twitter_update_option('MyBB2Twitter_account_activated', '1');
	// MyBB2Twitter_update_option('MyBB2Twitter_url_shortener', 'jmp2.fr');
	$searches = array( 1 => '#wordpress', 2 => 'pcinfo-web');
	MyBB2Twitter_update_option('MyBB2Twitter_saved_searches', $searches);

	MyBB2Twitter_delete_option('MyBB2Twitter_install_stage');
	
	?>
	<h3>Step 5: And You're Done!</h3>
	<p>You have successfully authorized this MyBB forum to access your Twitter account <strong><?php echo MyBB2Twitter_get_option('MyBB2Twitter_twitter_user'); ?></strong>.</p>
	<?php

}

?>

<br />
<small><a href="<?=$_SERVER['SCRIPT_NAME']?>?installing=1&reset_account=1&step=1">[Restart Setup]</a></small>

</div>
</div>
