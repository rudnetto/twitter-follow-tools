<?php

require "twitteroauth-master/autoload.php";
use Abraham\TwitterOAuth\TwitterOAuth;

session_start();

include_once("config.php");

session_destroy();

echo "Logging out...";
sleep(1);
header('location: index.php');

?>
