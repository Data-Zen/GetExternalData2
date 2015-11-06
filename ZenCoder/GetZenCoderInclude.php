<?php

/*
$json = '{"foo-bar": 12345}';

$obj = json_decode($json);
print $obj->{'foo-bar'}; // 12345
*/
//if (isset($page)) {
//    $page=1;
//}

$url = 'https://app.zencoder.com/api/v2/jobs?api_key='.$ZenCoderApi.'&page='. $page;
echo "\n URL:".$url ."\n";
/////$obj = json_decode(file_get_contents($url));
// title attribute
//var_dump($obj)
/////$media= $obj[0]->job->input_media_file;
/////var_dump($media);
// image object
//$obj->image
// image name
//$obj->image->name


/* FUNCTIONS ARE IN GETZENCODERLOOP.PHP */


$results = json_decode(file_get_contents($url),true);
$csv="

drop table if exists public.zencoder_staging_tmp;
CREATE TABLE public.zencoder_staging_tmp  ( 
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
SORTKEY ( finished_at );

 insert into zencoder_staging_tmp
(
audio_bitrate_in_kbps,
        audio_codec,
        audio_sample_rate,
        audio_tracks,
        channels,
        created_at,
        duration_in_ms,
        error_class,
        error_message,
        file_size_bytes,
        finished_at,
        format,
        frame_rate,
        height,
        id,
        md5_checksum,
        privacy,
        state,
        test,
        updated_at,
        video_bitrate_in_kbps,
        video_codec,
        width,
        total_bitrate_in_kbps,
        outputurl,
        AZVideoID,
        AZVideoType,
        AZBroadcaster,
        video_reference_id,
        inputurl
        ) values 
";
foreach ($results as $chunk) {
    $job = $chunk["job"];
    
     $input_media_files = $job["input_media_file"];
        $output_media_files = $job["output_media_files"][0];
        $outputurl=coall($output_media_files["url"]); 
        if (strpos($outputurl,"CH") >0  )
        {    
            //Use to get video / broadcaster / broadcasttype
            $AZURL=substr($outputurl, strpos($outputurl,"video")+strlen("video"));
            if (strpos($AZURL,"_") >0  ) {$AZURL=substr($AZURL, 0,strpos($AZURL,"_"));}
            if (strpos($AZURL,"/") >0  ) {$AZURL=substr($AZURL, 0,strpos($AZURL,"/"));}
            if (strpos($AZURL,".") >0  ) {$AZURL=substr($AZURL, 0,strpos($AZURL,"."));}
            $AZVideoID=substr($AZURL, 0,strpos($AZURL,"CH"));
            $AZVideoType="CH";
            $AZBroadcaster=substr($AZURL, strpos($AZURL,"CH")+2);
           
        }
        else
        {
            $AZURL=substr($outputurl, strpos($outputurl,"video")+strlen("video"));
            if (strpos($AZURL,"_") >0  ) {$AZURL=substr($AZURL, 0,strpos($AZURL,"_"));}
            if (strpos($AZURL,"/") >0  ) {$AZURL=substr($AZURL, 0,strpos($AZURL,"/"));}
            if (strpos($AZURL,".") >0  ) {$AZURL=substr($AZURL, 0,strpos($AZURL,"."));}
            $AZVideoID=substr($AZURL, 0,strpos($AZURL,"SV"));
            $AZVideoType="SV";
            $AZBroadcaster=substr($AZURL, strpos($AZURL,"SV")+2);
        }
       // echo "\n\n AZURL: $AZURL\n\n\n\n";    
       // echo "\n\n\n AZVIDEOID: $AZVideoID\n\n\n\n";
       // echo "\n\n AZBroadcaster: $AZBroadcaster\n\n\n\n"; 

        $csv= $csv . "(".coall($input_media_files["audio_bitrate_in_kbps"]) . ",'" .
        $input_media_files["audio_codec"] . "'," .
        coall($input_media_files["audio_sample_rate"]) . ",'" .
        $input_media_files["audio_tracks"] . "'," .
        coall($input_media_files["channels"]) . "," .
        
        coall2("'" . $input_media_files["created_at"] . "'")."," .
        
        coall($input_media_files["duration_in_ms"]) . ",'" .
        coall($input_media_files["error_class"]) . "','" .
        coall($input_media_files["error_message"]) . "'," .
        coall($input_media_files["file_size_bytes"]) . "," .
        coall2("'" . $input_media_files["finished_at"] . "'").",'" .
        //$input_media_files["finished_at"] . "','" .
        $input_media_files["format"] . "'," .
        coall($input_media_files["frame_rate"]) . "," .
        coall($input_media_files["height"]) . "," .
        coall($input_media_files["id"]) . ",'" .
        $input_media_files["md5_checksum"] . "','" .
        $input_media_files["privacy"] . "','" .
        $input_media_files["state"] . "','" .
        $input_media_files["test"] . "'," .
        coall2("'" . $input_media_files["updated_at"] . "'")."," .
        //$input_media_files["updated_at"] . "'," .
        coall($input_media_files["video_bitrate_in_kbps"]) . ",'" .
        $input_media_files["video_codec"] . "'," .
        coall($input_media_files["width"]) . "," .
        coall($input_media_files["total_bitrate_in_kbps"]) . ",'" .
        $outputurl . "'," .
        coall($AZVideoID) . ",'" .
        $AZVideoType . "','" .
        $AZBroadcaster . "','" .
        "video".coall($AZVideoID) .  $AZVideoType . $AZBroadcaster."','" .
        $input_media_files["url"] . "'),
		";
    }
    $csv=rtrim($csv);
    $csv=rtrim($csv,',');

date_default_timezone_set('UTC');//or change to whatever timezone you want
//phpinfo();

       

echo "\n*******StartQuery\n".$csv."\n*******EndQuery\n";
$rowsaffected=0;
$rec = pg_query($connect,$csv);
$rowsaffected=pg_affected_rows($rec);
echo "Rows affected $rowsaffected \n\n";
$sql = "
;
/*  Delete existing data so that we can load clean data*/
delete from public.zencoder_staging 
where exists 
(select 1 from public.zencoder_staging_tmp b where zencoder_staging.id=b.id);


/* Load the final de-duped data */
INSERT INTO public.zencoder_staging 
    SELECT distinct *    
FROM public.zencoder_staging_tmp b ;


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