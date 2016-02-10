<?php


date_default_timezone_set('UTC');//or change to whatever timezone you want
include './BrightCove/credentials/BrightCoveCredentials.php';

$connect = pg_connect($BrightCoveModifyCredentials);



$sql = "drop table if exists user_following;
create table user_following
(
id	BIGINT ENCODE lzo,
user_id	BIGINT ENCODE lzo,
follow_id	BIGINT ENCODE lzo,
created_at	datetime ENCODE lzo
);

copy user_following
from 's3://$S3bucketNameGFLDailyDumps/user_following.csv' with 
credentials 'aws_access_key_id=$S3accessKey;aws_secret_access_key=$S3secretKey' 
csv
IGNOREHEADER 1
REGION 'us-west-2';



drop table if exists user_unfollowing;
create table user_unfollowing
(
id	BIGINT ENCODE lzo,
user_id	BIGINT ENCODE lzo,
unfollow_id	BIGINT ENCODE lzo,
created_at	datetime ENCODE lzo
);

copy user_unfollowing
from 's3://$S3bucketNameGFLDailyDumps/user_unfollowing.csv' with 
credentials 'aws_access_key_id=$S3accessKey;aws_secret_access_key=$S3secretKey' 
csv
IGNOREHEADER 1
REGION 'us-west-2';
	 
alter table user_following add created_At_Weekending datetime;
alter table user_unfollowing add created_At_Weekending datetime;

update user_following set created_At_Weekending=date_trunc('week', created_at) + 6;
update user_unfollowing set created_At_Weekending=date_trunc('week', created_at) + 6;


GRANT SELECT ON TABLE user_unfollowing TO GROUP readonly;
GRANT SELECT ON TABLE user_following TO GROUP readonly;

";



echo "\n*******StartQuery\n".$sql."\n*******EndQuery\n";
$rec = pg_query($connect,$sql);

$rowsaffected=pg_affected_rows($rec);
echo "Rows affected $rowsaffected \n\n";



?>