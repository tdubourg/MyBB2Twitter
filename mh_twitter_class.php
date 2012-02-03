<?php

/*
File Name: PHP Twitter API Class
Author: Matt Harzewski (redwall_hp)Author URL: http://www.webmaster-source.com
License: LGPL
*/

require_once('OAuth/twitterOAuth.php');

class Twitter_API {
	function __construct($consumer_key='', $consumer_secret='') {

		if ($consumer_key != '' && $consumer_secret != '') {
			$this->consumer_key = $consumer_key;
			$this->consumer_secret = $consumer_secret;
			$this->oauth_on = TRUE;
		} else {
			$this->oauth_on = FALSE;
		}

	}

	//Update a user's status. #Auth #NoLimit
	public function update_status($status, $auth_user, $auth_pass, $in_reply_to_status_id='') {

		$url = "http://twitter.com/statuses/update.xml";
		//$data = "status={$status}";
		$data['status'] = $status;
		if (isset($in_reply_to_status_id)) {
			//$data = $data."&in_reply_to_status_id={$in_reply_to_status_id}";
			$data['in_reply_to_status_id'] = $in_reply_to_status_id;
		}
		$response = $this->send_request($url, 'POST', $data, $auth_user, $auth_pass);//
		if ($response != 401) {
			$xml = new SimpleXmlElement($response);
		} else {
			$xml = "401 - Authentication Error";
		}
		return $xml;

	}

	//Sends HTTP requests for other functions.
	private function send_request($url, $method='GET', $data='', $auth_user='', $auth_pass='') {

		if ($this->oauth_on && $auth_user != '') {
			$response = $this->oauth_request($url, $method, $auth_user, $auth_pass, $data);
		}
		else {
			$ch = curl_init($url);curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
			if (strtoupper($method)=="POST") {
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
			}
			if (ini_get('open_basedir') == '' && ini_get('safe_mode') == 'Off'){
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			}
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			if ($auth_user != '' && $auth_pass != '') {
				curl_setopt($ch, CURLOPT_USERPWD, "{$auth_user}:{$auth_pass}");
			}
			$response = curl_exec($ch);
			$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);
			if ($httpcode != 200) {
				return $httpcode;
			}
		}
		return $response;

	}



	//Get OAuth authorization link
	public function oauth_authorize_link() {

		$oauth = new TwitterOAuth($this->consumer_key, $this->consumer_secret);
		$oauth_token = $oauth->getRequestToken();
		$request_link = $oauth->getAuthorizeURL($oauth_token);
		$data = array( "request_link" => $request_link, "request_token" => $oauth_token['oauth_token'], "request_token_secret" => $oauth_token['oauth_token_secret'] );
		return $data;

	}



	//Acquire OAuth user token
	public function oauth_get_user_token($request_token, $request_token_secret) {

		$oauth = new TwitterOAuth($this->consumer_key, $this->consumer_secret, $request_token, $request_token_secret);
		$tokens = $oauth->getAccessToken();
		$user_token = array ( "access_token" => $tokens['oauth_token'], "access_token_secret" => $tokens['oauth_token_secret'] );
		return $user_token;

	}



	//Send an API request via OAuth
	public function oauth_request($url, $method, $user_access_key, $user_access_secret, $data) {
		$oauth = new TwitterOAuth($this->consumer_key, $this->consumer_secret, $user_access_key, $user_access_secret);
		//$thedata = array();
		//parse_str($data, $thedata);
		$response = $oauth->OAuthRequest($url, $data, $method);
		return $response;

	}



	//Shorten long URLs with is.gd or bit.ly.
	// This function has been modif by me 
	public function shorten_url($the_url, $api_key='', $user='') {
	
		$the_url=rawurlencode($the_url);
		if(!empty($api_key) && !empty($user)) $url = "http://api.jmp2.fr/shorten?longUrl={$the_url}&login={$user}&apiKey={$api_key}&format=xml&mode=twitter";
		else $url = "http://api.jmp2.fr/shorten?longUrl={$the_url}&format=xml&mode=twitter";
		// die($url);
		$response = $this->send_request($url, 'GET');
		$the_results = new SimpleXmlElement($response);
		if ($the_results->errorCode == '0') {
			$response = $the_results->results->nodeKeyVal->shortUrl;
		} else {
			$response = '';
		}
	
		return trim($response);

	}

	// This function has been completely modif by me 
	//Shrink a tweet and accompanying URL down to fit in 140 chars.
	public function fit_tweet($message, $url) {

		$message = $message;
		$length=strlen($message.' '.$url);
		// If we're too prolix ... 
		if ($length > 140) {
			$max = 140-(strlen($url)+6); // +6 for "[...] "
			$pos=strrpos($message,' ');
			if(!$pos) { // There's no blankspace in the message or it's the first char, so we gonna cut the troll way...
				$message = substr($message, 0, $max);
			} else { // If it's possible, we won't cut a word, so cut after a blankspace :
				$message = substr($message, 0, $max);
				$pos=strrpos($message,' ');
				$message = substr($message, 0, $pos);
			}
		
			// Let's mark the message as having been shorten
			$message = $message.'[...]'; 
		}
	
		return $message.' '.$url;
	}

}

?>
