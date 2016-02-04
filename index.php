<?php
session_start();
require_once('Facebook/autoload.php');
$fb = new Facebook\Facebook([
  'app_id' => 'put app id here',
  'app_secret' => 'put app secret here',
  'default_graph_version' => 'put app version here',
]);

$your_app_redirect_url = 'put app redirect here';

$helper = $fb->getRedirectLoginHelper();

$permissions = ['email']; 
	
try {
	if (isset($_SESSION['facebook_access_token'])) {
		$accessToken = $_SESSION['facebook_access_token'];
	} else {
  		$accessToken = $helper->getAccessToken();
	}
} catch(Facebook\Exceptions\FacebookResponseException $e) {
 	echo 'Graph returned an error: ' . $e->getMessage();

  	exit;
} catch(Facebook\Exceptions\FacebookSDKException $e) {
	echo 'Facebook SDK returned an error: ' . $e->getMessage();
  	exit;
 }

if (isset($accessToken)) {
	if (isset($_SESSION['facebook_access_token'])) {
		$fb->setDefaultAccessToken($_SESSION['facebook_access_token']);
	} else {
		$_SESSION['facebook_access_token'] = (string) $accessToken;
		$oAuth2Client = $fb->getOAuth2Client();
		$longLivedAccessToken = $oAuth2Client->getLongLivedAccessToken($_SESSION['facebook_access_token']);
		$_SESSION['facebook_access_token'] = (string) $longLivedAccessToken;
		$fb->setDefaultAccessToken($_SESSION['facebook_access_token']);
	}
	if (isset($_GET['code'])) {
		header('Location: ./');
	}
	try {
		$profile_request = $fb->get('/me?fields=name,first_name,last_name,email');
		$profile = $profile_request->getGraphNode()->asArray();
	} catch(Facebook\Exceptions\FacebookResponseException $e) {
		echo 'Graph returned an error: ' . $e->getMessage();
		session_destroy();
		header("Location: ./");
		exit;
	} catch(Facebook\Exceptions\FacebookSDKException $e) {
		echo 'Facebook SDK returned an error: ' . $e->getMessage();
		exit;
	}
	echo '<img src="//graph.facebook.com/'.$profile['id'].'/picture?type=large">';
	echo "<br>";
	echo $profile['name'];
} else {
	$loginUrl = $helper->getLoginUrl($your_app_redirect_url, $permissions);
	echo '<a href="' . $loginUrl . '">Log in with Facebook!</a>';
}
?>