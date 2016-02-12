<?php


date_default_timezone_set('UTC');//or change to whatever timezone you want
include './BrightCove/credentials/BrightCoveCredentials.php';
$connect = pg_connect($BrightCoveModifyCredentials);



$sql = "drop table if exists EngagmentDashboardData;

create table EngagmentDashboardData
(
id BIGINT ENCODE lzo,
video_id BIGINT ENCODE lzo,
video_title VARCHAR(10000) ENCODE lzo,
video_duration BIGINT ENCODE lzo,
video_created_at datetime ENCODE lzo,
video_view BIGINT ENCODE lzo,
video_view_amount_second BIGINT ENCODE lzo,
video_peak_ccu BIGINT ENCODE lzo,
video_average_ccu FLOAT ENCODE BYTEDICT,
type INT ENCODE LZO,
bc_video_id BIGINT ENCODE LZO,
reference_id VARCHAR(10000) ENCODE lzo,
user_id BIGINT ENCODE lzo,
user_username VARCHAR(10000) ENCODE lzo,
team_id INT ENCODE LZO,
team_name VARCHAR(10000) ENCODE lzo,
team_title VARCHAR(10000) ENCODE lzo,
league_id VARCHAR(10000) ENCODE lzo,
league_name VARCHAR(10000) ENCODE lzo,
league_title VARCHAR(10000) ENCODE lzo,
date datetime ENCODE lzo,
created_at datetime ENCODE lzo,
updated_at datetime ENCODE lzo,
category_id INT ENCODE LZO,
category_name VARCHAR(10000) ENCODE lzo,
category_title VARCHAR(10000) ENCODE lzo
);

copy EngagmentDashboardData
from 's3://$S3bucketNameGFLDailyDumps/analytics_video_play.csv' with 
credentials 'aws_access_key_id=$S3accessKey;aws_secret_access_key=$S3secretKey' 
csv
IGNOREHEADER 1
$S3Region;




GRANT SELECT ON TABLE public.EngagmentDashboardData TO GROUP readonly;

";



echo "\n*******StartQuery\n".$sql."\n*******EndQuery\n";
$rec = pg_query($connect,$sql);

$rowsaffected=pg_affected_rows($rec);
echo "Rows affected $rowsaffected \n\n";



?>