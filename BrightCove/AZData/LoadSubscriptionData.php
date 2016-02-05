<?php


date_default_timezone_set('UTC');//or change to whatever timezone you want

$connect = pg_connect($BrightCoveModifyCredentials);



$sql = "drop table if exists subscriptions_stg;
create table subscriptions_stg
(
id	BIGINT ENCODE lzo,
user_id	VARCHAR(10000) ENCODE lzo,
subscriber_id	VARCHAR(10000) ENCODE lzo,
created_at	VARCHAR(10000) ENCODE lzo,
contract_id	VARCHAR(10000) ENCODE lzo,
contract_item_code	VARCHAR(10000) ENCODE lzo,
contract_status	VARCHAR(10000) ENCODE lzo,
platform_ids	VARCHAR(10000) ENCODE lzo,
updated_at	VARCHAR(10000) ENCODE lzo,
is_visible	VARCHAR(10000) ENCODE lzo,
next_payment_date	VARCHAR(10000) ENCODE lzo,
paid_up_to_date	VARCHAR(10000) ENCODE lzo,
cancellation_date	VARCHAR(10000) ENCODE lzo,
payment_method VARCHAR(10000) ENCODE lzo
);

copy subscriptions_stg
from 's3://$S3bucketNameGFLDailyDumps/user_subscription.csv' with 
credentials 'aws_access_key_id=$S3accessKey;aws_secret_access_key=$S3secretKey' 
csv
IGNOREHEADER 1
$S3Region;


/*
copy broadcaster_details_stg
from 's3://$S3bucketName/broadcasters_live.csv' with 
credentials 'aws_access_key_id=$S3accessKey;aws_secret_access_key=$S3secretKey' 
csv
IGNOREHEADER 1
$S3Region;
*/
";



echo "\n*******StartQuery\n".$sql."\n*******EndQuery\n";
$rec = pg_query($connect,$sql);

$rowsaffected=pg_affected_rows($rec);
echo "Rows affected $rowsaffected \n\n";



?>