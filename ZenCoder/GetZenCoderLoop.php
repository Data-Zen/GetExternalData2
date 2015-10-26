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
    url                     varchar(65535) NULL ENCODE LZO
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



$sql=" select isnull(max(created_at)-8,'2014-01-01') from public.zencoder";
$result_maxdate = pg_query($connect, $sql);
   while ($row = pg_fetch_array($result_maxdate)) {
     $maxdtinFinaltable= $row[0];
   }
/* Set this just to get in the loop first time */
$mindtinStage=date('2030-01-01');

if ($debug==1)
{
    echo "maxdtinFinaltable: " . $maxdtinFinaltable . "\n";    
}

while ($mindtinStage >= $maxdtinFinaltable)
{


include 'GetZenCoder.php';


$sql=" select isnull(min(created_at),'2030-01-01') from public.zencoder_staging";
$result_mindate = pg_query($connect, $sql);
   while ($row = pg_fetch_array($result_mindate)) {
     $mindtinStage= $row[0];
   }
if ($debug==1)
{
    echo "mindtinStage: " . $mindtinStage . "\n";    
}
$page=$page+1;
sleep(5);
}

/* Load the final table */
$sql=" 

/*  Delete existing data so that we can load clean data*/
delete from public.zencoder 
where exists 
(select 1 from public.zencoder_staging b where public.zencoder.id=b.id);


/* Load the final de-duped data */
INSERT INTO public.zencoder(audio_bitrate_in_kbps, audio_codec, audio_sample_rate, audio_tracks, channels, created_at, duration_in_ms, error_class, error_message, file_size_bytes, finished_at, format, frame_rate, height, id, md5_checksum, privacy, state, test, updated_at, video_bitrate_in_kbps, video_codec, width, total_bitrate_in_kbps, url, created_at_pst) 
    SELECT audio_bitrate_in_kbps, audio_codec, audio_sample_rate, audio_tracks, channels, created_at, duration_in_ms, error_class, error_message, file_size_bytes, finished_at, format, frame_rate, height, id, md5_checksum, privacy, state, test, updated_at, video_bitrate_in_kbps, video_codec, width, total_bitrate_in_kbps, url, convert_timezone('PST',created_at) 
FROM public.zencoder_staging b 
where not exists (select 1 from public.zencoder where public.zencoder.id=b.id)
 
";
    
$rec = pg_query($connect,$sql);

  /*$id   = $artist['id'];
  $name = $artist['name'];

  $rank = $chunk['rank'];

  $tuple = array($id, $name, $rank);

  $results[] = $tuple;
*/

?>