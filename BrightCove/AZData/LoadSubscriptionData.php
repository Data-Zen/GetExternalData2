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



drop table if exists public.subscriptions;
CREATE TABLE public.subscriptions
(
	id BIGINT ENCODE lzo,
	user_id BIGINT ENCODE lzo,
	subscriber_id BIGINT ENCODE lzo,
	created_at datetime ENCODE lzo,
	contract_id BIGINT ENCODE lzo,
	contract_item_code VARCHAR(10000) ENCODE lzo,
	contract_status VARCHAR(10000) ENCODE lzo,
	platform_ids BIGINT ENCODE lzo,
	updated_at datetime ENCODE lzo,
	is_visible VARCHAR(10000) ENCODE lzo,
	next_payment_date date ENCODE lzo,
	paid_up_to_date date ENCODE lzo,
	cancellation_date date ENCODE lzo,
	payment_method VARCHAR(10000) ENCODE lzo,
	created_at_weekending datetime ENCODE lzo,
	cancellation_date_weekending datetime ENCODE lzo
	
)
DISTSTYLE EVEN;







INSERT INTO dev.public.subscriptions (id
       , user_id
       , subscriber_id
       , created_at
       , contract_id
       , contract_item_code
       , contract_status
       , platform_ids
       , updated_at
       , is_visible
       , next_payment_date
       , paid_up_to_date
       , cancellation_date
       , payment_method
	   , created_at_weekending
	   , cancellation_date_weekending
) 

select 
id
       , user_id::bigint
       , subscriber_id::bigint
       , created_at::datetime
       , contract_id::bigint
       , contract_item_code
       , contract_status
       , case when platform_ids ='' then null else platform_ids::bigint end 
       , updated_at::date
       , is_visible
       , next_payment_date::date
       , paid_up_to_date::date
       , case when cancellation_date ='' then null else cancellation_date::date end  
       , payment_method
	   , date_trunc('week', created_at::datetime) + 6
	   , case when cancellation_date ='' then null else date_trunc('week', cancellation_date::datetime) + 6 end     
	    From public.subscriptions_stg
		;
	   

GRANT SELECT ON TABLE public.subscriptions TO GROUP readonly;

";



echo "\n*******StartQuery\n".$sql."\n*******EndQuery\n";
$rec = pg_query($connect,$sql);

$rowsaffected=pg_affected_rows($rec);
echo "Rows affected $rowsaffected \n\n";



?>