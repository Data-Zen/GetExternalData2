<?php


date_default_timezone_set('UTC');//or change to whatever timezone you want

include './BrightCove/credentials/BrightCoveCredentials.php';

$connect = pg_connect($BrightCoveModifyCredentials);



$sql = "
drop table if exists users_stg;
create table users_stg
(
Role VARCHAR(10000) ENCODE lzo
,ID_User BIGINT ENCODE lzo
,Username VARCHAR(10000) ENCODE lzo sortkey distkey
,Email VARCHAR(10000) ENCODE lzo
,User_Status VARCHAR(10000) ENCODE lzo
,User_Date_Created VARCHAR(10000) ENCODE lzo
,Package VARCHAR(10000) ENCODE lzo
,Team VARCHAR(10000) ENCODE lzo
,Channel_Name VARCHAR(10000) ENCODE lzo
,Followers_Count BIGINT ENCODE lzo
,Unfollowers_Count BIGINT ENCODE lzo
,Channel_Date_Created VARCHAR(10000) ENCODE lzo
,Channel_Date_Updated VARCHAR(10000) ENCODE lzo
,Last_Broadcasted_Date VARCHAR(10000) ENCODE lzo
,Channel_Status VARCHAR(10000) ENCODE lzo
,league_name VARCHAR(10000) ENCODE lzo
,league_title VARCHAR(10000) ENCODE lzo
,Channel_Frozen_Date VARCHAR(10000) ENCODE lzo
);

copy users_stg
from 's3://$S3bucketNameGFLDailyDumps/user.csv' with 
credentials 'aws_access_key_id=$S3accessKey;aws_secret_access_key=$S3secretKey' 
csv
IGNOREHEADER 1
REGION 'us-west-2';




alter table users_stg add cleanusername varchar(1000) encode lzo;
update users_stg set cleanusername = lower(replace(replace(username,'-',''),'_',''));
drop table if exists users;
--select * from users_stg order by user_date_created limit 100;


create table users
(
ID_User BIGINT ENCODE lzo
,Username VARCHAR(10000) ENCODE lzo sortkey distkey
,Email VARCHAR(10000) ENCODE lzo
,User_Status VARCHAR(10000) ENCODE lzo
,Channel_Status VARCHAR(10000) ENCODE lzo
,Role VARCHAR(10000) ENCODE lzo
,Package VARCHAR(10000) ENCODE lzo
,Package_abbrev VARCHAR(10) ENCODE lzo
,Team VARCHAR(10000) ENCODE lzo
,league_name VARCHAR(10000) ENCODE lzo
,User_Date_Created datetime ENCODE lzo
,Channel_Date_Created datetime ENCODE lzo
,Followers_Count BIGINT ENCODE lzo
,Unfollowers_Count BIGINT ENCODE lzo
,Channel_Name VARCHAR(10000) ENCODE lzo
,Last_Broadcasted_Date datetime ENCODE lzo
,Channel_Frozen_Date datetime ENCODE lzo
);

  INSERT INTO dev.PUBLIC.users (
                                          ID_User 
                                                 ,Username 
                                                 ,Email 
                                                 ,User_Status 
                                                 ,Channel_Status 
                                                 ,Role 
                                                 ,Package
                                                 ,Package_abbrev 
                                                 ,Team 
                                                 ,league_name 
                                                 ,User_Date_Created 
                                                 ,Channel_Date_Created 
                                                 ,Followers_Count 
                                                 ,Unfollowers_Count 
                                                 ,Channel_Name 
                                                 ,Last_Broadcasted_Date 
                                                 ,Channel_Frozen_Date 
                )
SELECT  id_user
                , username
                , email
                , user_status
                , channel_status
                , ROLE
                , package
                , substring(package, 1, 4)
                , team
                     ,league_name 
                            ,case when User_Date_Created='' then null else User_Date_Created::datetime  end User_Date_Created
                            ,case when Channel_Date_Created='' then null else Channel_Date_Created::datetime  end Channel_Date_Created
                            ,Followers_Count 
                            ,Unfollowers_Count 
                            ,Channel_Name 
                            ,case when Last_Broadcasted_Date='' then null else Last_Broadcasted_Date::datetime  end Last_Broadcasted_Date
                            ,case when Channel_Frozen_Date='' then null else Channel_Frozen_Date::datetime  end Channel_Frozen_Date
FROM (


                SELECT id_user
                                , cleanusername AS username
                                , email
                                , user_status
                                , channel_status
                                , ROLE
                                , package
                                , team
                                   ,league_name 
                                                        ,User_Date_Created 
                                                        ,Channel_Date_Created 
                                                        ,Followers_Count 
                                                        ,Unfollowers_Count 
                                                        ,Channel_Name 
                                                        ,Last_Broadcasted_Date 
                                                        ,Channel_Frozen_Date 
                                                        ,1 rnk
                FROM users_stg
           where cleanusername not in (select cleanusername from users_stg group by cleanusername having count(1) > 1)
union


                   SELECT id_user
                                , cleanusername AS username
                                , email
                                , user_status
                                , channel_status
                                , ROLE
                                , package
                                , team
                                   ,league_name 
                                                        ,User_Date_Created 
                                                        ,Channel_Date_Created 
                                                        ,Followers_Count 
                                                        ,Unfollowers_Count 
                                                        ,Channel_Name 
                                                        ,Last_Broadcasted_Date 
                                                        ,Channel_Frozen_Date 
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
                FROM users_stg
           where cleanusername  in (select cleanusername from users_stg group by cleanusername having count(1) > 1)
                )
WHERE rnk = 1;  
	   

GRANT SELECT ON TABLE public.users TO GROUP readonly;

";



echo "\n*******StartQuery\n".$sql."\n*******EndQuery\n";
$rec = pg_query($connect,$sql);

$rowsaffected=pg_affected_rows($rec);
echo "Rows affected $rowsaffected \n\n";



?>