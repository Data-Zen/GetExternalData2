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
    inputurl                varchar(65535) NULL ENCODE LZO
    )
DISTSTYLE KEY
SORTKEY ( finished_at )

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



$sql="select min(id) from (
select isnull(min(id),0) id from zencoder where state ='processing'
union 
select max(id) id from zencoder)
";
$result_maxdate = pg_query($connect, $sql);
   while ($row = pg_fetch_array($result_maxdate)) {
     $maxIDinFinalTable= $row[0];
   }
/* Set this just to get in the loop first time */
$MinIDinStage=99999999999999;
$i=0;

if ($debug==1)
{
    echo "maxIDinFinalTable: " . $maxIDinFinalTable . "\n";    
}

while ($MinIDinStage >= $maxIDinFinalTable and $i < 500)
{


include 'GetZenCoderInclude.php';


$sql=" select isnull(min(id),99999999999999) from public.zencoder_staging";
$result_mindate = pg_query($connect, $sql);
   while ($row = pg_fetch_array($result_mindate)) {
     $MinIDinStage= $row[0];
   }
if ($debug==1)
{
    echo "MinIDinStage: " . $MinIDinStage . "\n";    
}
$page=$page+1;
$i=$i+1;
sleep(5);
}

/* Load the final table */
$sql=" 


/*  Delete existing data so that we can load clean data*/
delete from public.zencoder 
where exists 
(select 1 from public.zencoder_staging b where public.zencoder.id=b.id);


/* Load the final de-duped data */
INSERT INTO public.zencoder 
    SELECT distinct *    
FROM public.zencoder_staging b 
where not exists (select 1 from public.zencoder where public.zencoder.id=b.id);

/* Get rid of any possible duplicates that are not  Distinct   */
delete from zencoder
using 
(
select id,max(created_at) created_at,max(finished_at) finished_at, max(duration_in_ms) duration_in_ms, max(updated_at) updated_at from zencoder where id in (
select id from zencoder group by 1 having count(1) > 1)
group by 1) b
where b.id=zencoder.id and b.created_at<>zencoder.created_at and b.finished_at <> zencoder.finished_at and b.updated_at <> zencoder.updated_at;

insert into ccus
select a.video_name,sum(a.video_view) video_view,sum(a.video_seconds_viewed) video_seconds_viewed ,getdate() dt
from bc_videos a 
join zencoder b on a.video_reference_id=b.video_reference_id
where b.state='processing'
group by video_name
order by 2 desc; 
";
    
$rec = pg_query($connect,$sql);

  /*$id   = $artist['id'];
  $name = $artist['name'];

  $rank = $chunk['rank'];

  $tuple = array($id, $name, $rank);

  $results[] = $tuple;
*/

?>