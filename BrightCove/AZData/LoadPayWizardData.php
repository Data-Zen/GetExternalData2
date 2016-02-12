<?php


date_default_timezone_set('UTC');//or change to whatever timezone you want

include './BrightCove/credentials/BrightCoveCredentials.php';
$connect = pg_connect($BrightCoveModifyCredentials);



$sql = "drop table if exists SubscriptionRevenue_stg;

create table SubscriptionRevenue_stg
(
csn BIGINT ENCODE lzo,
paymenttype VARCHAR(10000) ENCODE lzo,
accountname VARCHAR(10000) ENCODE lzo,
paymentaccount VARCHAR(10000) ENCODE lzo,
customeraccountid bigint ENCODE lzo,
dt VARCHAR(10000) ENCODE lzo,
paymentstatus VARCHAR(10000) ENCODE lzo,
paymentid bigint ENCODE lzo,
refundpaymentid bigint ENCODE lzo,
authcode VARCHAR(10000) ENCODE lzo,
paymentamount float encode BYTEDICT,
subscontractid bigint encode lzo
);



copy SubscriptionRevenue_stg
from 's3://$S3bucketName/paywizarddata.csv' with 
credentials 'aws_access_key_id=$S3accessKey;aws_secret_access_key=$S3secretKey' 
csv
IGNOREHEADER 3
$S3Region;
/*
drop table if exists SubscriptionRevenue;

create table SubscriptionRevenue
(
csn BIGINT ENCODE lzo,
paymenttype VARCHAR(10000) ENCODE lzo,
accountname VARCHAR(10000) ENCODE lzo,
paymentaccount VARCHAR(10000) ENCODE lzo,
customeraccountid bigint ENCODE lzo,
dt datetime ENCODE lzo,
paymentstatus VARCHAR(10000) ENCODE lzo,
paymentid bigint ENCODE lzo,
refundpaymentid bigint ENCODE lzo,
authcode VARCHAR(10000) ENCODE lzo,
paymentamount float encode BYTEDICT,
subscontractid bigint encode lzo
);*/


delete from  SubscriptionRevenue
where csn in (select csn from subscriptionrevenue_stg where csn is not null)
;
insert into SubscriptionRevenue
SELECT csn
       , paymenttype
       , accountname
       , paymentaccount
       , customeraccountid
       , dt::datetime
       , paymentstatus
       , paymentid
       , refundpaymentid
       , authcode
       , paymentamount
       , subscontractid
 FROM dev.public.subscriptionrevenue_stg
 where csn not in (select csn from subscriptionrevenue)
 ;
GRANT SELECT ON TABLE public.SubscriptionRevenue TO GROUP readonly;

";



echo "\n*******StartQuery\n".$sql."\n*******EndQuery\n";
$rec = pg_query($connect,$sql);

$rowsaffected=pg_affected_rows($rec);
echo "Rows affected $rowsaffected \n\n";



?>