<?php

require "twitteroauth-master/autoload.php";
use Abraham\TwitterOAuth\TwitterOAuth;

session_start();

$access_token = "";
$access_token_secret = "";
$consumer_key = "QMsP8myj420K8ov3KtbBn0HVx";
$consumer_secret = "4T0WvdsO0rN0hWIeIIgFmXoPK6AW8OnamRV1zQmtBDVGKYGLgI";

include_once("config.php");


if(isset($_REQUEST['oauth_token']) && $_SESSION['token'] !== $_REQUEST['oauth_token'])
{
	echo "1";
	session_destroy();
	header('location: index.php');

} elseif (isset($_REQUEST['oauth_token']) && $_SESSION['token'] == $_REQUEST['oauth_token']) {
	echo "2";
	$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $_SESSION['token'], $_SESSION['token_secret']);
	//$access_token = $connection->getAccessToken($_REQUEST['oauth_verifier']);
	$access_token = $connection->oauth('oauth/access_token', ['oauth_verifier' => $_REQUEST['oauth_verifier']]);

	if($connection->getLastHttpCode() == '200')
	{
		$_SESSION['status'] = 'verified';
		$_SESSION['request_vars'] = $access_token;

		unset($_SESSION['token']);
		unset($_SESSION['token_secret']);
		header('location: index.php');
	} else {
		die("error, try again later!");
	}
} else {
	if (isset($_GET['denied']))
	{
		header('location: index.php');
		die();
	}

	$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET);
	$request_token = $connection->oauth('oauth/request_token', array('oauth_callback' => 'http://127.0.0.1/twitter/sign-in.php'));

	$_SESSION['token'] 			= $request_token['oauth_token'];
	$_SESSION['token_secret']	= $request_token['oauth_token_secret'];

	if($connection->getLastHttpCode() == '200')
	{
		$url = $connection->url('oauth/authorize', array('oauth_token' => $request_token['oauth_token']));
		header('location: ' . $url);
	} else {
		die('something went wrong.');
	}
}
?>
