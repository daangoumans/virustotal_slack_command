<?php
header("Content-Type: application/json;charset=utf-8");
/*

REQUIREMENTS

* A custom slash command on a Slack team
* A web server running PHP5 with cURL enabled

USAGE

* Place this script on a server running PHP5 with cURL.
* Set up a new custom slash command on your Slack team:
http://my.slack.com/services/new/slash-commands
* Under "Choose a command", enter whatever you want for
the command. /chkurl is easy to remember.
* Under "URL", enter the URL for the script on your server.
* Leave "Method" set to "Post".
* Decide whether you want this command to show in the
autocomplete list for slash commands.
* If you do, enter a short description and usage hint.

*/

// Grab some of the values from the slash command, create vars for post back to Slack

$command = $_POST['command'];
$text = $_POST['text'];
$token = $_POST['token'];

// Check the token and make sure the request is from our team

if ($token != '')
	{ //replace this with the token from your slash command configuration page
	$msg = "The token for the slash command doesn't match. Check your script.";
	die($msg);
	echo $msg;
	}

if ($text == "help")
	{
	$reply = "Just put in a url like /chkurl https://asdf.com/";
	}
  else
	{

	// api key

	$api_key = "";
	$url_to_check = "$text";

	// Set up cURL

	$post = array(
		'apikey' => $api_key,
		'scan' => '1',
		'resource' => $url_to_check
	);
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, 'https://www.virustotal.com/vtapi/v2/url/report');
	curl_setopt($ch, CURLOPT_POST, True);
	curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate'); // please compress data
	curl_setopt($ch, CURLOPT_USERAGENT, "gzip, My php curl client");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, True);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
	$result = curl_exec($ch);
	$status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);

	// status code check

	if ($status_code == 200)
		{ // OK
		$js = json_decode($result, true);

		// make json reply

		$php_array = array(
			'response_type' => 'in_channel',
			'text' => '*Result for: ' . $js["url"]
                . " from date: " . $js["scan_date"]
                . "* \nThe scan id is: _" . $js["scan_id"]
                . "_ \n \n With " . $js['positives']
                . " positive scan results from " . $js['total']
                . " scanners. \n \n " . $js['permalink']
		);
		$reply = json_encode($php_array);
		}
	  else
		{ // Error occured
		$php_array = array(
			array(
				'response_type' => 'ephemeral'
			) ,
			array(
				'text' => "Sorry, that didn't work. Please try again."
			)
		);
		$reply = json_encode($php_array);
		}
	}

// Send the reply back to the user.

echo $reply;
