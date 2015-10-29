<?php
date_default_timezone_set('UTC');//or change to whatever timezone you want



if (!empty( $argv[1])) 
{ $a = $argv[1];}
else
{ $a=3;}

if (!empty( $argv[2])) 
{ $backfill = $argv[2];
	}
else
{ $backfill=0;}
echo "\n\n backfill: $backfill \n\n"; 
// Go get last run
include './BrightCove/credentials/BrightCoveCredentials.php';

$connect = pg_connect($BrightCoveReadOnlyCredentials);
if ($backfill ==0)
{
$sql= "select isnull(max(dt)-9+$a,getdate()-479)::date maxdt, dateadd(day,1,isnull(max(dt)-9+$a,getdate()-479))::date from public.bc_videos";

}
else
{
$sql= "select isnull(max(dt)+1,getdate()-479)::date maxdt, dateadd(day,1,isnull(max(dt)+1,getdate()-479))::date from public.bc_videos";

}
echo "\n\nSQL ".$sql . "\n\n";
$result2 = pg_query($connect, $sql);

   while ($row = pg_fetch_array($result2)) {
     $fromdate= $row[0];
     $todate= $row[1];  //One More Day
   }

$fromdateepoch = strtotime($fromdate." UTC");
$todateepoch = strtotime($todate." UTC");

if ($fromdateepoch > 0  and $todateepoch > 0) 
{
echo  "FromDate: " . $fromdate. "\n";
echo  "ToDate: " . $todate. "\n";
echo  "FromDateEpoch: " . $fromdateepoch. "\n";
echo  "ToDateEpoch: " . $todateepoch. "\n";
}
else
{
echo "Something was wrong with your dates, try one of the below formats:", "\n";
echo "now", "\n";
echo "10 September 2000", "\n";
echo "-1 day", "\n";
echo "-1 week", "\n";
echo "-1 week 2 days 4 hours 2 seconds", "\n";
echo "next Thursday", "\n";
echo "last Monday", "\n";
exit;
}


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
if ($todateepoch =="") {
$to     = time()*1000;
}
else{
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
include './BrightCove/getBCinclude.php';

include './BrightCove/bcs3.php';

include './BrightCove/loadbcinclude.php';

?>

