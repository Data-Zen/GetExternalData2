<?php

/*
$json = '{"foo-bar": 12345}';

$obj = json_decode($json);
print $obj->{'foo-bar'}; // 12345
*/
$debug = 1;
date_default_timezone_set('UTC');//or change to whatever timezone you want
function coall($x) {
    if (empty($x)) {
        $z='null';
    }
    else
    {
        $x = str_replace("'","''",$x);
        $z=$x;
    }
    return $z;
}

function coall2($x) {
    if (strlen($x) ==2) {
        $z='null';
    }
    else
    {
        $z=$x;
    }
    return $z;
}
include 'credentials/ZenCoderCredentials.php';
$connect = pg_connect($ZenCoderRSModifyCredentials);
$sql="

drop table if exists public.zencoder_staging;
CREATE TABLE public.zencoder_staging  ( 
    audio_bitrate_in_kbps   int4 NULL ENCODE LZO,
    audio_codec             varchar(10000) NULL ENCODE LZO,
    audio_sample_rate       int4 NULL ENCODE LZO,
    audio_tracks            varchar(10000) NULL ENCODE LZO,
    channels                int4 NULL ENCODE LZO,
    created_at              timestamp NULL ENCODE LZO,
    duration_in_ms          int8 NULL ENCODE LZO,
    error_class             varchar(10000) NULL ENCODE LZO,
    error_message           varchar(10000) NULL ENCODE LZO,
    file_size_bytes         int8 NULL ENCODE LZO,
    finished_at             timestamp NULL ENCODE LZO,
    format                  varchar(10000) NULL ENCODE LZO,
    frame_rate              int4 NULL ENCODE LZO,
    height                  int4 NULL ENCODE LZO,
    id                      int8 NULL ENCODE LZO DISTKEY,
    md5_checksum            varchar(10000) NULL ENCODE LZO,
    privacy                 varchar(10000) NULL ENCODE LZO,
    state                   varchar(10000) NULL ENCODE LZO,
    test                    varchar(10000) NULL ENCODE LZO,
    updated_at              timestamp NULL ENCODE LZO,
    video_bitrate_in_kbps   int4 NULL ENCODE LZO,
    video_codec             varchar(10000) NULL ENCODE LZO,
    width                   int4 NULL ENCODE LZO,
    total_bitrate_in_kbps   int8 NULL ENCODE LZO,
    outputurl               varchar(65535) NULL ENCODE LZO,
    azvideoid               int8 null encode lzo,
    azvideotype             varchar(100) null encode lzo,
    azbroadcaster           varchar(10000) NULL ENCODE LZO,
    video_reference_id      varchar(10000) NULL ENCODE LZO,
    inputurl                varchar(65535) NULL ENCODE LZO,
    sourcelatitude          float ENCODE bytedict,
    sourcelongitude         float ENCODE bytedict,
    sourcelocation          varchar(10000) NULL ENCODE LZO,
    destinationlatitude     float ENCODE bytedict,
    destinationlongitude    float ENCODE bytedict,
    destinationlocation     varchar(10000) NULL ENCODE LZO
    )
DISTSTYLE KEY
SORTKEY ( created_at )

";
if ($debug==1)
{
echo "\n*******StartQuery\n".$sql."\n*******EndQuery\n";
}
$rec = pg_query($connect,$sql);



if (isset($page)) {
 }
else
{   $page=1;
}



$sql="select min(created_at) from (
select isnull(min(created_at),'2010-01-01') created_at from zencoder where state ='processing'
union 
select dateadd(h,-3,max(created_at)) created_at from zencoder)
";
echo "\n*******StartQuery\n".$sql."\n*******EndQuery\n";
$result_maxdate = pg_query($connect, $sql);
   while ($row = pg_fetch_array($result_maxdate)) {
     $maxDTinFinalTable= $row[0];
   }
/* Set this just to get in the loop first time */
$MinDTinStage='2020-01-01';
$i=0;
$errorcount =0;
if ($debug==1)
{
    echo "maxDTinFinalTable: " . $maxDTinFinalTable . "\n";    
}

while ($MinDTinStage >= $maxDTinFinalTable and $i < 500000 /*To ensure no infinite loop*/)
{


include 'GetZenCoderInclude.php';


$sql=" select isnull(min(created_at),'2020-01-01') from public.zencoder_staging";
$result_mindate = pg_query($connect, $sql);
   while ($row = pg_fetch_array($result_mindate)) {
     $MinDTinStage= $row[0];
   }
if ($debug==1)
{
    echo "MinDTinStage: " . $MinDTinStage . "\n";    
}
if ($rowsaffected > 0 ){
/* INSERT WAS GOOD */
$page=$page+1;
sleep(20);
$errorcount =0;
}
else
{
/* INSERT WAS BAD */
$errorcount =$errorcount+1;
$waittime=$errorcount*60;
echo "Problem with page: $page \n Waiting $waittime seconds. $errorcount Errors so far \n";
sleep($waittime);
if ($errorcount > 10) {  echo "\n\nQuiting. Too Many Errors\n\n";}

}
$i=$i+1;
}

/* Load the final table */
$sql=" 


/*  Delete existing data so that we can load clean data*/
delete from public.zencoder 
where exists 
(select 1 from public.zencoder_staging b where public.zencoder.id=b.id);


/* Load the final de-duped data */
INSERT INTO public.zencoder 
select a.*,nvl(c.name_en,sourcelocation_country_cd) From (

    SELECT distinct *
    ,duration_in_ms / 1000 / 60 duration_in_minutes
    ,duration_in_ms::float / 1000 / 60 /60 duration_in_hours
    ,case when split_part(sourcelocation,',',3) = '' then sourcelocation else split_part(sourcelocation,',',3) end sourcelocation_country_cd
FROM public.zencoder_staging b 
where not exists (select 1 from public.zencoder where public.zencoder.id=b.id)
) a 
left join countries c on trim(lower(sourcelocation_country_cd))=trim(lower(c.code)) ;





/* Get rid of any possible duplicates that are not  Distinct   */
delete from zencoder
using 
(
select id,max(created_at) created_at,max(finished_at) finished_at, max(duration_in_ms) duration_in_ms, max(updated_at) updated_at from zencoder where id in (
select id from zencoder group by 1 having count(1) > 1)
group by 1) b
where b.id=zencoder.id and b.created_at<>zencoder.created_at and b.finished_at <> zencoder.finished_at and b.updated_at <> zencoder.updated_at;
select count(1),(select min(created_at ) from zencoder z) from zencoder;
";
echo "\n*******StartQuery\n".$sql."\n*******EndQuery\n";
$rec = pg_query($connect,$sql);

  /*$id   = $artist['id'];
  $name = $artist['name'];

  $rank = $chunk['rank'];

  $tuple = array($id, $name, $rank);

  $results[] = $tuple;
*/

?>