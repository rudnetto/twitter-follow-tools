<?php

require "twitteroauth-master/autoload.php";
use Abraham\TwitterOAuth\TwitterOAuth;

session_start();

include_once("config.php");

if(isset($_GET["reset"]) && $_GET['reset'] == 1)
{
	echo "ads";
	session_destroy();
	header('location: index.php');
}

if (isset($_SESSION['status']) && $_SESSION['status'] == 'verified')
{
	// SUCCESS, CONNECTED.
	$screenname      		= $_SESSION['request_vars']['screen_name'];
	$twitterid          = $_SESSION['request_vars']['user_id'];
	$oauth_token        = $_SESSION['request_vars']['oauth_token'];
	$oauth_token_secret = $_SESSION['request_vars']['oauth_token_secret'];

	$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $oauth_token, $oauth_token_secret);


	$followCount = 0;
	$errorCount = 0;
	$i = 0;

	$myFollowing = array();
	$myFollowers = array();
	$notFollowingBack = array();

	//CONECTADO
	//CALL PARA RETORNAR FOLLOWING

	$cFollowing = $connection->get("friends/ids", ["screen_name" => $screenname, "stringify_ids" => "true"]);

	$i = 0;
	foreach($cFollowing->ids as $flwng)
	{
		$myFollowing[$i] = $flwng;
		$i++;
	}

	//CALL PARA RETORNAR FOLLOWERS
	$cFollowers = $connection->get("followers/ids", ["screen_name" => $screenname, "stringify_ids" => "true"]);

	$i = 0;
	foreach($cFollowers->ids as $flwrs)
	{
		$myFollowers[$i] = $flwrs;
	    $i++;
	}

	//CRIA LISTA DE NOT FOLLOWING BACK
	foreach($myFollowing as $flwng)
	{
		if (in_array($flwng, $myFollowers) == false)
	  	{
	    	array_push($notFollowingBack, $flwng);
		}
	}

	$_SESSION['followers'] = $myFollowers;
	$_SESSION['following'] = $myFollowing;
	$_SESSION['notfollow'] = $notFollowingBack;
	$_SESSION['initialized'] = 1;

	echo "<h2> Hello, " . $screenname . "!</h2>";
	echo "I follow " . count($myFollowing) . " people.";
	echo "<br>" . count($myFollowers) . " people follow me.";
	echo "<br>" . count($notFollowingBack) . " people dont follow me back.<br><br><br>";

	echo '<form method="get" action="logout.php">';
	echo '<input type="submit" value="Logout">';
	echo '</form><br><br>';

	if (isset($_SESSION['initialized']) == 1)
	{
		if (isset($_GET['followTrick']))
		{
			$follow_count = 0;
			$target = $_GET['followTrick'];

			echo '<form method="get">';
			echo '<input type="submit" value="Back">';
			echo '</form>';

			$target_timeline = $connection->get("statuses/user_timeline", ["screen_name" => $target,
																																		"count" => 80,
																																		"exclude_replies" => "true",
																																		"include_rts" => "false"]);


			$hasError = false;

			if ($connection->getLastHttpCode() == 200)
			{
				while($hasError == false)
				{
					foreach($target_timeline as $tt)
					{

						if ($tt->retweet_count > 80)
						{
							$retweeters_ids = $connection->get("statuses/retweeters/ids", ["id" => $tt->id, "stringify_ids" => "true"]);
							echo "You're going to follow retweeters from tweet " . $tt->id .  ' by '  . $target . '<br>';

							ob_flush();
							flush();

							if ($connection->getLastHttpCode() == 200)
							{
								foreach ($retweeters_ids->ids as $rt_id)
								{
									if (in_array($rt_id, $myFollowing) == false)
									{
										sleep(2);

										$friendship = $connection->post("friendships/create", ["user_id" => $rt_id]);

										if ($connection->getLastHttpCode() == 200)
										{
											$follow_count++;
											echo "You just followed user " . $rt_id . "! - Total: " . $follow_count . "<br>";
										} else {
											echo "HTTP Error Code: " . $connection->getLastHttpCode() . "<br>";
							        $hasError = true;
							        break;
										}
									}

									ob_flush();
						      flush();
								}
							} else {
								echo "HTTP Error Code: " . $connection->getLastHttpCode() . "<br>";
								$hasError = true;
								break;
							}

							if ($hasError == true)
							{
								break;
							}
						}
					}
				}

				echo "<br><br> <h2>You just followed " . $follow_count . " people from " . $target . " retweets.</h2>";

		  } else {
        echo "HTTP Error Code: " . $connection->getLastHttpCode() . "<br>";
			}

		}	elseif (isset($_GET['unfollowTrick'])) {

			echo '<form method="get">';
			echo '<input type="submit" value="Back">';
			echo '</form>';

			$hasError = false;
			$unfollow_count = 0;

			while($hasError == false)
			{
				$justUnfollowed = 0;
				$gone = '';

				for ($i=0; $i < 80; $i++)
		    {
		      $gone = $gone . array_shift($notFollowingBack) . ",";
		    }

				$lookup = $connection->post("users/lookup", ["user_id" => $gone, "include_entities" => "false"]);

				foreach($lookup as $lkp)
		    {
		      sleep(2);
		      $unfollow = $connection->post("friendships/destroy", ["user_id" => $lkp->id]);

		      if ($connection->getLastHttpCode() == 200)
		      {
		        $unfollow_count++;
		        echo "You just unfollowed " . $lkp->name . " (@" . $lkp->screen_name . ")  --  Total: " . $unfollow_count . "<br>";
		      } else {
		        echo "HTTP Error Code: " . $connection->getLastHttpCode() . "<br>";
		        $hasError = true;
		        break;
		      }

		      ob_flush();
		      flush();
		    }
			}

			echo "<br><br> <h2>You just unfollowed " . $unfollow_count . " people who didn't follow you back.</h2>";

		} else {

			echo '<form method="get">';
			echo 'Target twitter to followtrick:<br>';
			echo '<input type="text" name="followTrick">';
			echo '<input type="submit" value="Follow!">';
			echo '</form>';

			echo '<form method="get">';
			echo '<input type="submit" name="unfollowTrick" value="Unfollow">';
			echo '</form>';
		}
	} //3


} else {
	echo '<a href="sign-in.php">SIGN IN WITH TWITTER</a>';
}


?>
