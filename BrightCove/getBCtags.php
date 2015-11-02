<?php
date_default_timezone_set('UTC');//or change to whatever timezone you want



if (!empty( $argv[1])) 
{ $a = $argv[1];}
else
{ $a=3;}

if (!empty( $argv[2])) 
{ $backfill = $argv[2];
	}
else
{ $backfill=0;}

if (!empty( $argv[3])) 
{ $daysback = $argv[3];
	}
else
{ $daysback=10;}




echo "\n\n backfill: $backfill \n\n"; 





include './BrightCove/credentials/BrightCoveCredentials.php';

$connect = pg_connect($BrightCoveReadOnlyCredentials);
if ($backfill ==0)
{
$sql= "select isnull(max(dt)-$daysback+$a,getdate()-479)::date maxdt, dateadd(day,1,isnull(max(dt)-$daysback+$a,getdate()-479))::date from public.bc_videos";

}
else
{
$sql= "select isnull(max(dt)+1,getdate()-479)::date maxdt, dateadd(day,1,isnull(max(dt)+1,getdate()-479))::date from public.bc_videos";

}
echo "\n\nSQL ".$sql . "\n\n";
$result2 = pg_query($connect, $sql);

   while ($row = pg_fetch_array($result2)) {
     $fromdate= $row[0];
     $todate= $row[1];  //One More Day
   }

$fromdateepoch = strtotime($fromdate." UTC");
$todateepoch = strtotime($todate." UTC");

if ($fromdateepoch > 0  and $todateepoch > 0) 
{
echo  "FromDate: " . $fromdate. "\n";
echo  "ToDate: " . $todate. "\n";
echo  "FromDateEpoch: " . $fromdateepoch. "\n";
echo  "ToDateEpoch: " . $todateepoch. "\n";
}
else
{
echo "Something was wrong with your dates, try one of the below formats:", "\n";
echo "now", "\n";
echo "10 September 2000", "\n";
echo "-1 day", "\n";
echo "-1 week", "\n";
echo "-1 week 2 days 4 hours 2 seconds", "\n";
echo "next Thursday", "\n";
echo "last Monday", "\n";
exit;
}

if((time()-(60*60*24)) <strtotime($todate." UTC")-(86400*4 ))

{
echo "\n\n\n\n\nFUTURE \n\n\n\n\n";
exit("Was in the future already so exited procedure");
}
$limit="100000000";
echo "Limit:" . $limit . "\n";


include './BrightCove/getBCinclude.php';

include './BrightCove/bcs3.php';

include './BrightCove/loadbcinclude.php';

?>

