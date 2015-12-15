<?php

include './BrightCove/credentials/BrightCoveCredentials.php';
/*first log in*/
$outputfile=$S3sourceDir . "/broadcasters_live.csv";

$csvurl='http://www.azubu.tv/api/admin/user/generateReport?features%5Bin%5D%5Broles.id%5D%5B0%5D=4&orderBy%5Buser.id%5D=desc'; 
$ch = curl_init(); 

curl_setopt($ch, CURLOPT_URL, $url); 
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US;       rv:1.8.1.6) Gecko/20070725 Firefox/2.0.0.6"); 
curl_setopt($ch, CURLOPT_TIMEOUT, 60); 
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); 
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie); 
curl_setopt($ch, CURLOPT_REFERER, $url);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata); 
curl_setopt($ch, CURLOPT_POST, true); 
$result = curl_exec($ch); 
//echo $result;  

/*now download the file*/
curl_setopt($ch, CURLOPT_URL, $csvurl); 
curl_setopt($ch, CURLOPT_REFERER, $url);
$result = curl_exec($ch); 
//echo "\n\n\n\n\n\n\n\n";
//echo $result;  
//echo "\n\n\n\n\n\n\n\n";

curl_close($ch);

file_put_contents($outputfile, $result);
include './BrightCove/bcs3.php';
include './BrightCove/AZData/LoadBroadcasterData_live.php';
//curl "http://www.azubu.tv/login_check" -H "Origin: http://www.azubu.tv" -H "Accept-Encoding: gzip, deflate" -H "Accept-Language: en-US,en;q=0.8" -H "User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/46.0.2490.80 Safari/537.36" -H "Content-Type: application/x-www-form-urlencoded; charset=UTF-8" -H "Accept: application/json, text/plain, */*" -H "Referer: http://www.azubu.tv/" -H "Cookie: bm_monthly_unique=true; locale=en; NG_TRANSLATE_LANG_KEY="%"22en"%"22; bm_daily_unique=true; bm_sample_frequency=1; _gat=1; _gat_UA-38697080-1=1; _gat_UA-57082407-1=1; _gat_AzubuProperties=1; ads_bm_last_load_status=BLOCKING; bm_last_load_status=BLOCKING; _ga=GA1.2.731319598.1446537612; HTML_ViewerId=83f8a543-7918-c5c7-86d2-90a569fde081; HTML_VisitValueCookie=0|0|0|0|0|0|0|0|0|0|0|0|0; HTML_BitRateBucketCsv=0,0,0,0,0,0,0,0; AkamaiAnalytics_BrowserSessionId=e32e35b4-89d8-5996-a860-b1e53cb89801; HTML_VisitCountCookie=1; HTML_VisitIntervalStartTime=1447102136372; HTML_isPlayingCount=1" -H "Connection: keep-alive" -H "DNT: 1" --data "_username=stacey.beckett"%"40azubu.com&_password=bradley471&_type=azubu-login" --compressed

?>