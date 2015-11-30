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
$result2 = pg_query($connect, "select isnull(max(dt)+1,getdate()-479)::date maxdt, dateadd(day,1,isnull(max(dt)+1,getdate()-479))::date from public.bc_videos_destination");

   while ($row = pg_fetch_array($result2)) {
     $fromdate= $row[0];
     $todate= $row[1];  //One More Day
   }
*/





















$sql = "

CREATE TABLE if not exists public.bc_videos_destination  ( 
	video               	int8 NULL ENCODE LZO ,
	video_view          	int4 NULL ENCODE LZO,
	videoname           	varchar(10000) NULL ENCODE LZO,	
	video_seconds_viewed	int8 NULL ENCODE LZO,
	video_reference_id  	varchar(10000) NULL ENCODE LZO,	
	video_duration      	float8 NULL ENCODE bytedict,
	destination_domain 		varchar(10000) NULL ENCODE LZO,	
	dt                  	date NULL ENCODE LZO sortkey  distkey
	)
DISTSTYLE KEY;

drop table if exists public.bc_videos_destination_staging;
CREATE TABLE public.bc_videos_destination_staging  ( 
		video               	int8 NULL ENCODE LZO ,
	video_view          	int4 NULL ENCODE LZO,
	videoname           	varchar(10000) NULL ENCODE LZO,	
	video_seconds_viewed	int8 NULL ENCODE LZO,
	video_reference_id  	varchar(10000) NULL ENCODE LZO,	
	video_duration      	float8 NULL ENCODE bytedict,
	destination_domain 		varchar(10000) NULL ENCODE LZO,	
	dt                  	date NULL ENCODE LZO sortkey  distkey
	)
DISTSTYLE KEY;

copy public.bc_videos_destination_staging
from 's3://$S3bucketName/bcoutput_video_destination.json' with 
credentials 'aws_access_key_id=$S3accessKey;aws_secret_access_key=$S3secretKey' 
json  'auto'
$S3Region;

/* Get rid of bad data */
delete from bc_videos_destination_staging where video is null;

/* Update Date */
update bc_videos_destination_staging set dt = '$fromdate';




/*  Delete existing data so that we can load clean data*/
delete from public.bc_videos_destination
where exists 
(select 1 from public.bc_videos_destination_staging b where public.bc_videos_destination.dt=b.dt and public.bc_videos_destination.video=b.video  );


/* Load the final de-duped data */
insert into public.bc_videos_destination
select distinct * from public.bc_videos_destination_staging a
where not exists (select 1 from public.bc_videos_destination b where a.dt=b.dt and a.video=b.video);

";



echo "\n*******StartQuery\n".$sql."\n*******EndQuery\n";
$rec = pg_query($connect,$sql);

$rowsaffected=pg_affected_rows($rec);
echo "Rows affected $rowsaffected \n\n";



?>