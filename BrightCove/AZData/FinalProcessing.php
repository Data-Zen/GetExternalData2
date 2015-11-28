<?php

include './BrightCove/credentials/BrightCoveCredentials.php';
$debug=1;
/*first log in*/
$connect = pg_connect($BrightCoveModifyCredentials);
$sql="
update broadcaster_details
set username = trim(lower(replace(replace(username,'-',''),'_','')));

update zencoder
set azbroadcaster=trim(lower(replace(replace(azbroadcaster,'-',''),'_','')));



update bc_videos
set azbroadcaster=trim(lower(replace(replace(azbroadcaster,'-',''),'_','')));


  insert into bc_videos_rollup
select max(nvl(account,0)) 
       , max(nvl(account_name,'')) bc_account_name
       , sum(nvl(bytes_delivered,0))  bc_bytes_delivered
       , max(nvl(engagement_score,0))  bc_engagement_score
       , max(nvl(play_rate,0))  bc_play_rate
       , max(nvl(video,0)) bc_video
       , max(nvl(video_duration,0)) bc_video_duration
       , max(nvl(video_engagement_1,0)) bc_video_engagement_1
       , max(nvl(video_engagement_100,0)) bc_video_engagement_100
       , max(nvl(video_engagement_25,0)) bc_video_engagement_25
       , max(nvl(video_engagement_50,0)) bc_video_engagement_50
       , max(nvl(video_engagement_75,0))  bc_video_engagement_75
       , sum(nvl(video_impression,0))  bc_video_impression
       , max(nvl(video_name,'')) bc_video_name 
       , max(nvl(video_percent_viewed,0))  bc_video_percent_viewed
       , sum(nvl(video_seconds_viewed,0))  bc_video_seconds_viewed
       , sum(nvl(video_view,0))  bc_video_view
       , video_reference_id  bc_video_reference_id
       , max(nvl(videoname,'')) bc_videoname
       , max(nvl(videotags,'')) bc_videotags
       , min(nvl(dt,'2001-01-01')) bc_dt
       , max(nvl(azvideoid,0)) bc_azvideoid
       , max(nvl(azvideotype,''))  bc_azvideotype
       , max(nvl(azbroadcaster,'')) bc_azbroadcaster
 from bc_videos b
 where not exists (Select 1 from bc_videos_rollup br where br.bc_video_reference_id=b.video_reference_id)
and video_reference_id is not null
  group by video_reference_id;

  


  



  INSERT INTO dev.public.zencoder_rollup (zc_audio_bitrate_in_kbps
       , zc_audio_codec
       , zc_audio_sample_rate
       , zc_audio_tracks
       , zc_channels
       , zc_created_at
       , zc_duration_in_ms
       , zc_error_class
       , zc_error_message
       , zc_file_size_bytes
       , zc_finished_at
       , zc_format
       , zc_frame_rate
       , zc_height
       , zc_id
       , zc_md5_checksum
       , zc_privacy
       , zc_state
       , zc_test
       , zc_updated_at
       , zc_video_bitrate_in_kbps
       , zc_video_codec
       , zc_width
       , zc_total_bitrate_in_kbps
       , zc_outputurl
       , zc_azvideoid
       , zc_azvideotype
       , zc_azbroadcaster
       , zc_video_reference_id
       , zc_inputurl
       , zc_sourcelatitude
       , zc_sourcelongitude
       , zc_sourcelocation
       , zc_destinationlatitude
       , zc_destinationlongitude
       , zc_destinationlocation
       , zc_duration_in_minutes
       , zc_duration_in_hours
       , zc_sourcelocation_country_cd
       , zc_sourcelocation_country
) select max(nvl(audio_bitrate_in_kbps,0))
       , max(nvl(audio_codec,''))
       , max(nvl(audio_sample_rate,0))
       , max(nvl(audio_tracks,''))
       , max(nvl(channels,0))
       , min(nvl(created_at,'2001-01-01'))
       , sum(nvl(duration_in_ms,0))
       , max(nvl(error_class,''))
       , max(nvl(error_message,''))
       , max(nvl(file_size_bytes,0))
       , max(nvl(finished_at,'2001-01-01'))
       , max(nvl(format,''))
       , max(nvl(frame_rate,0))
       , max(nvl(height,0))
       , max(nvl(id,0))
       , max(nvl(md5_checksum,''))
       , max(nvl(privacy,''))
       , max(nvl(state,''))
       , max(nvl(test,''))
       , max(nvl(updated_at,'2001-01-01'))
       , max(nvl(video_bitrate_in_kbps,0))
       , max(nvl(video_codec,''))
       , max(nvl(width,0))
       , max(nvl(total_bitrate_in_kbps,0))
       , max(nvl(outputurl,''))
       , max(nvl(azvideoid,0))
       , max(nvl(azvideotype,''))
       , max(nvl(azbroadcaster,''))
       , video_reference_id
       , max(nvl(inputurl,''))
       , max(nvl(sourcelatitude,0))
       , max(nvl(sourcelongitude,0))
       , max(nvl(sourcelocation,''))
       , max(nvl(destinationlatitude,0))
       , max(nvl(destinationlongitude,0))
       , max(nvl(destinationlocation,''))
       , sum(nvl(duration_in_minutes,0))
       , sum(nvl(duration_in_hours,0))
       , max(nvl(sourcelocation_country_cd,''))
       , max(nvl(sourcelocation_country ,''))
 FROM zencoder zb
 where not exists (Select 1 from zencoder_rollup zr where zb.video_reference_id=zr.zc_video_reference_id)
and video_reference_id is not null
 group by video_reference_id;
 

 INSERT INTO dev.public.broadcaster_details_rollup 
select
max(id_user)
       , username
       , max(email)
       , max(user_status)
       , max(channel_status)
       , max(role)
       , max(package)
       , max(package_abbrev)
       , max(team)
       , max(organization)
       , max(user_date_created)
       , max(channel_date_created)
       , max(channel_time_created)
       , max(channel_date_updated)
       , max(followers_count)
       , max(unfollowers_count)
       , max(month)
       , max(week)
       , max(channel_name)
       , max(last_broadcasted_date)
       , max(channel_frozen_date)
       , max(analytics_ignore)
from broadcaster_details b
  where not exists (Select 1 from broadcaster_details_rollup br where br.b_username=b.username)
 group by username;
 
";
if ($debug==1)
{
echo "\n*******StartQuery\n".$sql."\n*******EndQuery\n";
}
$rec = pg_query($connect,$sql);
$rowsaffected=pg_affected_rows($rec);
echo "Rows affected $rowsaffected \n\n";

?>