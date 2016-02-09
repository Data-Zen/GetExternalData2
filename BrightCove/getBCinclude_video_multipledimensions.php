<?php


$array = [

    ["video_device", "video,device_os,device_type","video_view,video.name,video_seconds_viewed,video.reference_id,video_duration,device_os,device_type"],
    ["video_country", "video,country","video_view,video.name,video_seconds_viewed,video.reference_id,video_duration,country,country_name"],
    ["video_referrer", "video,referrer_domain,source_type","video_view,video.name,video_seconds_viewed,video.reference_id,video_duration,referrer_domain,source_type"],
    #["video_source", "video,source_type,search_terms","video_view"],
    ["video_destination", "video,destination_domain","video_view,video.name,video_seconds_viewed,video.reference_id,video_duration,destination_domain"]
];

foreach ($array as list($afilename, $adimension,$afields)) {


//$dimensions = $argv[4];
//if ($dimensions == "")
//{
	//$dimensions="video,device_os,device_type";
	//$dimensions="video,country";
	$dimensions=$adimension;
	//$dimensions="video,source_type,search_terms";
	//video, destination_domain
//}

//$fields = $argv[5];
//if ($fields == "")
//{
	//$fields="account,account.name,active_media,bytes_delivered,device_os,device_type,daily_unique_viewers,drm_bytes_packaged,engagement_score,licenses_served,live_seconds_streamed,play_rate,player_load,,video,video_duration,video_engagement_1,video_engagement_100,video_engagement_25,video_engagement_50,video_engagement_75,video_impression,video_name,video_percent_viewed,video_seconds_viewed,video_view,video.reference_id,video.name&";
	//$fields="video_view,video.name,video_seconds_viewed,video.reference_id,video_duration,source_type,referrer_domain&";
	$fields=$afields;
//}

	echo  "Dimensions: " . $dimensions. "\n";
	echo  "Fields: " . $fields. "\n";


//$file = $argv[6];
//if ($file == "")
//{
//	$file="files/bcoutput.json";
//}

//	echo  "Dimensions: " . $dimensions. "\n";
//	echo  "Fields: " . $fields. "\n";





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



// set up request for access token
$data          = array();

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
// set up the API call
// no data to submit
$data = array();
// get current time and 24 hours ago in milliseconds
if ($todateepoch =="") {
$to     = time()*1000;
}
else
{
	$to=$todateepoch*1000;
}
//$to = strtotime($todate);
if ($fromdateepoch =="") {
$from   = $to-(24*60*60*1000);
}
else
{
	$from=$fromdateepoch*1000;
}
//$from   = $to-(24*60*60*1000)*$daysback;
$to=$to-1;
$method = "GET";
// get the URL and authorization info from the form data
$request = "https://analytics.api.brightcove.com/v1/data?accounts={$BrightCoveAccountID}&dimensions={$dimensions}&limit={$limit}&sort=-video_view&fields={$fields}&from={$from}&to={$to}";
echo $request . "\n";
// add headers
$headers = array(
	1=> "Authorization: Bearer {$access_token}",
	2=> "Content-type: application/x-www-form-urlencoded",
);
//send the tp request

$result = SendRequest($request, $method, $data, $headers);
$result = str_replace("account.name","account_name",$result);
$result = str_replace("video.reference_id","video_reference_id",$result);
$result = str_replace("video.name","videoname",$result);
//$result = str_replace("video.tags","videotags",$result);
$cleanresult = substr($result,strpos($result,'"items":[')+8,-1);
//echo $cleanresult;
//echo "\n\n\n";
$cleanresult = substr($cleanresult,1,strrpos($cleanresult,"summary")-4);
//echo $cleanresult;
$cleanresult = str_replace("},{","}{",$cleanresult);

$file="files/bcoutput_".$afilename . ".json";

file_put_contents($file, $cleanresult);

}




?>

