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
$result2 = pg_query($connect, "select isnull(max(dt)+1,getdate()-479)::date maxdt, dateadd(day,1,isnull(max(dt)+1,getdate()-479))::date from public.bc_videos");

   while ($row = pg_fetch_array($result2)) {
     $fromdate= $row[0];
     $todate= $row[1];  //One More Day
   }
*/





















$sql = "



drop table if exists public.bc_videos_staging;
CREATE TABLE public.bc_videos_staging  ( 
	account             	int8 NULL ENCODE LZO,
	account_name        	varchar(10000) NULL ENCODE LZO,
	bytes_delivered     	float8 NULL ENCODE bytedict,
	engagement_score    	float8 NULL ENCODE bytedict,
	play_rate           	float8 NULL ENCODE bytedict,
	video               	int8 NULL ENCODE LZO DISTKEY,
	video_duration      	float8 NULL ENCODE bytedict,
	video_engagement_1  	float8 NULL ENCODE bytedict,
	video_engagement_100	float8 NULL ENCODE bytedict,
	video_engagement_25 	float8 NULL ENCODE bytedict,
	video_engagement_50 	float8 NULL ENCODE bytedict,
	video_engagement_75 	float8 NULL ENCODE bytedict,
	video_impression    	int8 NULL ENCODE LZO,
	video_name          	varchar(10000) NULL ENCODE LZO,
	video_percent_viewed	float8 NULL ENCODE bytedict,
	video_seconds_viewed	int8 NULL ENCODE LZO,
	video_view          	int4 NULL ENCODE LZO,
	video_reference_id  	varchar(10000) NULL ENCODE LZO,
	videoname           	varchar(10000) NULL ENCODE LZO,
	videotags           	varchar(65535) NULL ENCODE LZO,
	dt                  	date NULL ENCODE LZO sortkey ,
	azvideoid           	int8 NULL ENCODE LZO,
	azvideotype         	varchar(100) NULL ENCODE LZO,
	azbroadcaster       	varchar(10000) NULL ENCODE LZO 
	)
DISTSTYLE KEY;

copy bc_videos_staging
from 's3://$S3bucketName/bcoutput_video.json' with 
credentials 'aws_access_key_id=$S3accessKey;aws_secret_access_key=$S3secretKey' 
json  'auto'
$S3Region;

/* Get rid of bad data */
delete from bc_videos_staging where video is null and video_view is null and video_name is null and video_reference_id is null;

/* Update Date */
update bc_videos_staging set dt = '$fromdate';

/* Update Tags */
update bc_videos_staging set videotags=bc_videos_tags.videotags
from bc_videos_tags
where bc_videos_tags.video=bc_videos_staging.video
and bc_videos_staging.videotags is null;


/* Update the Mobile Data */
update bc_videos_staging set video = -1, video_name = 'Mobile'
where video is null and video_view is not null and video_name is null and video_seconds_viewed is not null;


/*    */

update bc_videos_staging
set azvideoid=
CASE
WHEN charindex('VOD',substring(video_reference_id,6)) >0  
        and ( charindex('VOD',substring(video_reference_id,6))  < case when charindex('CH',substring(video_reference_id,6)) = 0 then 100000 else charindex('CH',substring(video_reference_id,6)) end ) 
        and ( charindex('VOD',substring(video_reference_id,6))  < case when charindex('SV',substring(video_reference_id,6)) = 0 then 100000 else charindex('SV',substring(video_reference_id,6)) end ) 
THEN substring(substring(video_reference_id,6),1,charindex('VOD',substring(video_reference_id,6))-1  )::bigint

WHEN charindex('CH',substring(video_reference_id,6)) >0 
    and (charindex('CH',substring(video_reference_id,6)) < case when charindex('SV',substring(video_reference_id,6)) = 0 then 100000 else charindex('SV',substring(video_reference_id,6)) end ) 
THEN substring(substring(video_reference_id,6),1,charindex('CH',substring(video_reference_id,6))-1  )::bigint

WHEN charindex('SV',substring(video_reference_id,6)) >0 
THEN substring(substring(video_reference_id,6),1,charindex('SV',substring(video_reference_id,6))-1  )::bigint

ELSE
NULL
END-- azvideoid
,azbroadcaster=CASE
WHEN charindex('VOD',substring(video_reference_id,6)) >0  
        and ( charindex('VOD',substring(video_reference_id,6))  < case when charindex('CH',substring(video_reference_id,6)) = 0 then 100000 else charindex('CH',substring(video_reference_id,6)) end ) 
        and ( charindex('VOD',substring(video_reference_id,6))  < case when charindex('SV',substring(video_reference_id,6)) = 0 then 100000 else charindex('SV',substring(video_reference_id,6)) end ) 
THEN substring(substring(video_reference_id,6),charindex('VOD',substring(video_reference_id,6))+3  )


WHEN charindex('CH',substring(video_reference_id,6)) >0 
    and (charindex('CH',substring(video_reference_id,6)) < case when charindex('SV',substring(video_reference_id,6)) = 0 then 100000 else charindex('SV',substring(video_reference_id,6)) end ) 
THEN substring(substring(video_reference_id,6),charindex('CH',substring(video_reference_id,6))+2  )

WHEN charindex('SV',substring(video_reference_id,6)) >0 
THEN substring(substring(video_reference_id,6),charindex('SV',substring(video_reference_id,6))+2  )

ELSE
NULL
END -- azbroadcaster 
,azvideotype=
CASE
WHEN charindex('VOD',substring(video_reference_id,6)) >0  
        and ( charindex('VOD',substring(video_reference_id,6))  < case when charindex('CH',substring(video_reference_id,6)) = 0 then 100000 else charindex('CH',substring(video_reference_id,6)) end ) 
        and ( charindex('VOD',substring(video_reference_id,6))  < case when charindex('SV',substring(video_reference_id,6)) = 0 then 100000 else charindex('SV',substring(video_reference_id,6)) end ) 
THEN  'VOD'

WHEN charindex('CH',substring(video_reference_id,6)) >0 
    and (charindex('CH',substring(video_reference_id,6)) < case when charindex('SV',substring(video_reference_id,6)) = 0 then 100000 else charindex('SV',substring(video_reference_id,6)) end ) 
THEN  'CH'

WHEN charindex('SV',substring(video_reference_id,6)) >0 
THEN 'SV'

ELSE
NULL
END 
where azvideoid is null;




/*  Delete existing data so that we can load clean data*/
delete from public.bc_videos 
where exists 
(select 1 from public.bc_videos_staging b where public.bc_videos.video=b.video and public.bc_videos.dt=b.dt);


/* Load the final de-duped data */
insert into public.bc_videos
select distinct * from public.bc_videos_staging a
where not exists (select 1 from public.bc_videos b where a.video=b.video and a.dt=b.dt);

";



echo "\n*******StartQuery\n".$sql."\n*******EndQuery\n";
$rec = pg_query($connect,$sql);
$rowsaffected="A SQL problem occured";
$rowsaffected=pg_affected_rows($rec);
echo "Rows affected $rowsaffected \n\n";



?>