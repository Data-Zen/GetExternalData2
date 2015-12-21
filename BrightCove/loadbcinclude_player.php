<?php


date_default_timezone_set('UTC');//or change to whatever timezone you want
//phpinfo();
/*
echo "CREDENTIALS \n\n\n\n";
echo $BrightCoveModifyCredentials;
echo "END CREDENTIALS  \n\n\n\n";
*/
$connect = pg_connect($BrightCoveModifyCredentials);
/*
$result2 = pg_query($connect, "select isnull(max(dt)+1,getdate()-479)::date maxdt, dateadd(day,1,isnull(max(dt)+1,getdate()-479))::date from public.bc_player");

   while ($row = pg_fetch_array($result2)) {
     $fromdate= $row[0];
     $todate= $row[1];  //One More Day
   }
*/





















$sql = "

drop table if exists public.bc_player_staging;
CREATE TABLE public.bc_player_staging  ( 
	account             	int8 NULL ENCODE LZO,
	account_name        	varchar(10000) NULL ENCODE LZO,
	bytes_delivered      	int8 NULL ENCODE bytedict,
	engagement_score      	float8 NULL ENCODE bytedict,
	play_rate           	float8 NULL ENCODE bytedict,
	player        	varchar(10000) NULL ENCODE LZO,
	player_load             int8 NULL ENCODE LZO ,
	player_name        	varchar(10000) NULL ENCODE LZO,
	video_impression      	int8 NULL ENCODE bytedict,
	video_percent_viewed	float8 NULL ENCODE bytedict,
	video_seconds_viewed	int8 NULL ENCODE LZO,
	video_view          	int4 NULL ENCODE LZO,
	dt                  	date NULL ENCODE LZO sortkey  distkey
	)
DISTSTYLE KEY;


copy bc_player_staging
from 's3://$S3bucketName/bcoutput_player.json' with 
credentials 'aws_access_key_id=$S3accessKey;aws_secret_access_key=$S3secretKey' 
json  'auto'
$S3Region;

/* Get rid of bad data */
delete from bc_player_staging where video_view is null;

/* Update Date */
update bc_player_staging set dt = '$fromdate';




/*  Delete existing data so that we can load clean data*/
delete from public.bc_player
where dt in 
(select distinct dt  from public.bc_player_staging b  );


/* Load the final de-duped data */
insert into public.bc_player
select distinct * from public.bc_player_staging a
where not exists (select 1 from public.bc_player b where a.dt=b.dt);

";



echo "\n*******StartQuery\n".$sql."\n*******EndQuery\n";
$rec = pg_query($connect,$sql);

$rowsaffected=pg_affected_rows($rec);
echo "Rows affected $rowsaffected \n\n";



?>