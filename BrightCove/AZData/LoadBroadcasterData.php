<?php


date_default_timezone_set('UTC');//or change to whatever timezone you want

$connect = pg_connect($BrightCoveModifyCredentials);



$sql = "drop table if exists broadcaster_details;
create table broadcaster_details
(
ID_User BIGINT ENCODE lzo
,Username VARCHAR(10000) ENCODE lzo sortkey distkey
,Email VARCHAR(10000) ENCODE lzo
,User_Status VARCHAR(10000) ENCODE lzo,Channel_Status VARCHAR(10000) ENCODE lzo,Role VARCHAR(10000) ENCODE lzo
,Package VARCHAR(10000) ENCODE lzo
,Team VARCHAR(10000) ENCODE lzo
,Organization VARCHAR(10000) ENCODE lzo
,User_Date_Created VARCHAR(10000) ENCODE lzo
,Channel_Date_Created VARCHAR(10000) ENCODE lzo
,Channel_Time_Created VARCHAR(10000) ENCODE lzo
,Channel_Date_Updated VARCHAR(10000) ENCODE lzo
,Followers_Count BIGINT ENCODE lzo
,Unfollowers_Count BIGINT ENCODE lzo
,Month VARCHAR(10000) ENCODE lzo
,Week VARCHAR(10000) ENCODE lzo
,Channel_Name VARCHAR(10000) ENCODE lzo
,Last_Broadcasted_Date VARCHAR(10000) ENCODE lzo
,Channel_Frozen_Date VARCHAR(10000) ENCODE lzo
);

copy broadcaster_details 
from 's3://$S3bucketName/broadcasters.csv' with 
credentials 'aws_access_key_id=$S3accessKey;aws_secret_access_key=$S3secretKey' 
csv
IGNOREHEADER 1;

/* Get rid of bad data 
delete from BC_Videos_tags where video is null;
*/

GRANT SELECT ON TABLE public.broadcaster_details TO GROUP readonly;

";



echo "\n*******StartQuery\n".$sql."\n*******EndQuery\n";
$rec = pg_query($connect,$sql);
var_dump(pg_fetch_array($rec));



?>