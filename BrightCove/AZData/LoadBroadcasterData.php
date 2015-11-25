<?php


date_default_timezone_set('UTC');//or change to whatever timezone you want

$connect = pg_connect($BrightCoveModifyCredentials);



$sql = "drop table if exists broadcaster_details_stg;
create table broadcaster_details_stg
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

copy broadcaster_details_stg
from 's3://$S3bucketName/broadcasters.csv' with 
credentials 'aws_access_key_id=$S3accessKey;aws_secret_access_key=$S3secretKey' 
csv
IGNOREHEADER 1
$S3Region;


drop table if exists broadcaster_details;
create table broadcaster_details
(
ID_User BIGINT ENCODE lzo
,Username VARCHAR(10000) ENCODE lzo sortkey distkey
,Email VARCHAR(10000) ENCODE lzo
,User_Status VARCHAR(10000) ENCODE lzo,Channel_Status VARCHAR(10000) ENCODE lzo,Role VARCHAR(10000) ENCODE lzo
,Package VARCHAR(10000) ENCODE lzo
,Package_abbrev VARCHAR(10) ENCODE lzo
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



INSERT INTO dev.public.broadcaster_details (id_user
       , username
       , email
       , user_status
       , channel_status
       , role
       , package
	   , Package_abbrev
       , team
       , organization
       , user_date_created
       , channel_date_created
       , channel_time_created
       , channel_date_updated
       , followers_count
       , unfollowers_count
       , month
       , week
       , channel_name
       , last_broadcasted_date
       , channel_frozen_date
      
) 

select distinct
		 max(id_user)
       , trim(lower(replace(replace(username,'-',''),'_','')) )
       , max(email) 
       , max(user_status) 
       , max(channel_status)
       , max(role)
       , max(package)
	   , max(substring(package,1,4)) 
       , max(team)
       , max(organization)
       , max(user_date_created)
       , max(channel_date_created)
       , max(channel_time_created)
       , max(channel_date_updated)
       , max(followers_count)
       , max(unfollowers_count)
       , max(\"month\")
       , max(week)
       , max(channel_name)
       , max(last_broadcasted_date)
       , max(channel_frozen_date)
	
 From 
 broadcaster_details_stg
 where trim(username) <> ''
 group by lower(replace(replace(username,'-',''),'_',''))  
";



echo "\n*******StartQuery\n".$sql."\n*******EndQuery\n";
$rec = pg_query($connect,$sql);


$sql = "

GRANT SELECT ON TABLE public.broadcaster_details TO GROUP readonly;

alter table broadcaster_details add analytics_ignore smallint;
truncate table broadcaster_details_rollup;
update broadcaster_details
set analytics_ignore=0;
update broadcaster_details
set analytics_ignore=1
WHERE 
(email ilike '%azubu.com' or email  ilike '%geeksforless%'
or email  ilike '%deleted%' or email  ilike '%test%' 

);



insert into Broadcaster_Channel_count
select count(1) ct,package,getdate()  from public.broadcaster_details where channel_status='ACTIVE' and user_status='ACTIVE' group by package ;



";



echo "\n*******StartQuery\n".$sql."\n*******EndQuery\n";
$rec = pg_query($connect,$sql);
#var_dump(pg_fetch_array($rec));



?>