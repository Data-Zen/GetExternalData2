<?php


date_default_timezone_set('UTC');//or change to whatever timezone you want

$connect = pg_connect($BrightCoveModifyCredentials);



$sql = "/*drop table if exists broadcaster_details_stg;
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
*/
copy broadcaster_details_stg
from 's3://$S3bucketName/broadcasters.csv' with 
credentials 'aws_access_key_id=$S3accessKey;aws_secret_access_key=$S3secretKey' 
csv
IGNOREHEADER 1
$S3Region;


alter table broadcaster_details_stg add cleanusername varchar(1000) encode lzo;
update broadcaster_details_stg set cleanusername = lower(replace(replace(username,'-',''),'_',''));


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


INSERT INTO dev.PUBLIC.broadcaster_details (
                id_user
                , username
                , email
                , user_status
                , channel_status
                , ROLE
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
SELECT id_user
                , username
                , email
                , user_status
                , channel_status
                , ROLE
                , package
                , substring(package, 1, 4)
                , team
                , organization
                , user_date_created
                , channel_date_created
                , channel_time_created
                , channel_date_updated
                , followers_count
                , unfollowers_count
                , \"month\"
                , week
                , channel_name
                , last_broadcasted_date
                , channel_frozen_date
FROM (
                SELECT id_user
                                , cleanusername AS username
                                , email
                                , user_status
                                , channel_status
                                , ROLE
                                , package
                                , substring(package, 1, 4)
                                , team
                                , organization
                                , user_date_created
                                , channel_date_created
                                , channel_time_created
                                , channel_date_updated
                                , followers_count
                                , unfollowers_count
                                , \"month\"
                                , week
                                , channel_name
                                , last_broadcasted_date
                                , channel_frozen_date
                                , rank() OVER (
                                                PARTITION BY cleanusername ORDER BY CASE 
                                                                                WHEN channel_status ilike 'active'
                                                                                                THEN 1
                                                                                ELSE 0
                                                                                END DESC
                                                                , nvl(followers_count, 0) DESC
                                                                , user_date_created ASC
                                                                , id_user ASC
                                                ) AS rnk
                FROM broadcaster_details_stg
               /* WHERE cleanusername IN (
                                                SELECT cleanusername
                                                FROM broadcaster_details_stg
                                                GROUP BY cleanusername
                                                HAVING count(1) > 1
                                                )*/
                )
WHERE rnk = 1;  
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
$rowsaffected=pg_affected_rows($rec);
echo "Rows affected $rowsaffected \n\n";



?>