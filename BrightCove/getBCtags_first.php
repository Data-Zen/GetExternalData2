<?php
date_default_timezone_set('UTC');//or change to whatever timezone you want

include './BrightCove/credentials/BrightCoveCredentials.php';
$limit="100000000";
echo "Limit:" . $limit . "\n";

//$dimensions = $argv[4];
//if ($dimensions == "")
//{
	$dimensions="video";
//}

//$fields = $argv[5];
//if ($fields == "")
//{
	$fields="video,video.tags&";
//}

	echo  "Dimensions: " . $dimensions. "\n";
	echo  "Fields: " . $fields. "\n";


//$file = $argv[6];
//if ($file == "")
//{
	$file="files/bcoutputtags.json";
//}

	//echo  "Dimensions: " . $dimensions. "\n";
	//echo  "Fields: " . $fields. "\n";







function SendRequest($url, $method, $data, $headers) {
	$context = stream_context_create(array
		(
			"http"     => array(
				"method"  => $method,
				"header"  => $headers,
				"content" => http_build_query($data)
			)
		));
	return file_get_contents($url, false, $context);
}
// set up request for access token
$data          = array();
$client_id     = $BrightCoveClientID;
$client_secret = $BrightCoveClientSecret;
$auth_string   = "{$client_id}:{$client_secret}";
$request       = "https://oauth.brightcove.com/v3/access_token?grant_type=client_credentials";
$ch            = curl_init($request);
curl_setopt_array($ch, array(
		CURLOPT_POST           => TRUE,
		CURLOPT_RETURNTRANSFER => TRUE,
		CURLOPT_SSL_VERIFYPEER => FALSE,
		CURLOPT_USERPWD        => $auth_string,
		CURLOPT_HTTPHEADER     => array(
			'Content-type: application/x-www-form-urlencoded',
		),
		CURLOPT_POSTFIELDS => $data
	));
$result = curl_exec($ch);
curl_close($ch);
// Check for errors
if ($result === FALSE) {
	die(curl_error($ch));
}
// Decode the response
$resultData   = json_decode($result, TRUE);
$access_token = $resultData["access_token"];
echo "\n\n  access_token: $access_token \n\n";
// set up the API call
// no data to submit
$data = array();
// get current time and 24 hours ago in milliseconds
$method = "GET";
// get the URL and authorization info from the form data
$request = "https://analytics.api.brightcove.com/v1/data?accounts={$BrightCoveAccountID}&dimensions={$dimensions}&limit={$limit}&sort=-video_view&fields={$fields}";//&from={$from}&to={$to}";
echo $request . "\n";
// add headers
$headers = array(
	1=> "Authorization: Bearer {$access_token}",
	2=> "Content-type: application/x-www-form-urlencoded",
);
//send the tp request
$result = SendRequest($request, $method, $data, $headers);
$result = str_replace("video.tags","videotags",$result);

$cleanresult = substr($result,strpos($result,'"items":[')+8,-1);
//echo $cleanresult;
//echo "\n\n\n";
$cleanresult = substr($cleanresult,1,strrpos($cleanresult,"summary")-4);
//echo $cleanresult;
$cleanresult = str_replace("},{","}{",$cleanresult);

$cleanresult = str_replace('"',"",$cleanresult);

$cleanresult = str_replace("video_view",'"video_view"',$cleanresult);
$cleanresult = str_replace("video:",'"video":',$cleanresult);
$cleanresult = str_replace("videotags",'"videotags"',$cleanresult);

$cleanresult = str_replace("[",'"',$cleanresult);

$cleanresult = str_replace("]",'"',$cleanresult);



file_put_contents($file, $cleanresult);
//exec('php getBC.php "19 october 2015 "20 october 2015" 10 >/dev/null');
//exec ('whoami');


?>

