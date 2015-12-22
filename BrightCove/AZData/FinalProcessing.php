<?php




echo "\n\n*******Running FinalProcessing.php*************";
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

delete from bc_videos_rollup where bc_dt >= (select max(bc_dt)-60 from  bc_videos_rollup );
 insert into bc_videos_rollup
WITH rankings AS
  (SELECT *
   FROM
     (SELECT *,
             rank() over (partition BY video_reference_id
                          ORDER BY video_view DESC, video_seconds_viewed DESC, dt desc) AS rank
      FROM bc_videos b
      WHERE video_reference_id IS NOT NULL
        AND NOT EXISTS
          (SELECT 1
           FROM bc_videos_rollup br
           WHERE br.bc_video_reference_id=b.video_reference_id) )
   WHERE rank=1)


SELECT max(nvl(account,0)),
       max(nvl(account_name,'')) bc_account_name ,
       sum(nvl(bytes_delivered,0)) bc_bytes_delivered ,
       max(nvl(engagement_score,0)) bc_engagement_score ,
       max(nvl(play_rate,0)) bc_play_rate ,
       max(nvl(video,0)) bc_video ,
       max(nvl(video_duration,0)) bc_video_duration ,
       max(nvl(video_engagement_1,0)) bc_video_engagement_1 ,
       max(nvl(video_engagement_100,0)) bc_video_engagement_100 ,
       max(nvl(video_engagement_25,0)) bc_video_engagement_25 ,
       max(nvl(video_engagement_50,0)) bc_video_engagement_50 ,
       max(nvl(video_engagement_75,0)) bc_video_engagement_75 ,
       sum(nvl(video_impression,0)) bc_video_impression ,
       max(nvl(video_name,'')) bc_video_name,
       max(nvl(video_percent_viewed,0)) bc_video_percent_viewed ,
       sum(nvl(video_seconds_viewed,0)) bc_video_seconds_viewed ,
       sum(nvl(video_view,0)) bc_video_view ,
       video_reference_id bc_video_reference_id ,
       max(nvl(videoname,'')) bc_videoname ,
       max(nvl(videotags,'')) bc_videotags ,
       max(nvl(dt,'2001-01-01')) bc_dt ,
       max(nvl(azvideoid,0)) bc_azvideoid ,
       max(nvl(azvideotype,'')) bc_azvideotype ,
       max(nvl(azbroadcaster,'')) bc_azbroadcaster
FROM rankings b
GROUP BY video_reference_id;



  


  


delete from zencoder_rollup where zc_created_at >= (select max(zc_created_at)-30 from  zencoder_rollup );
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
 
drop table if exists public.broadcaster_details_rollup;
CREATE TABLE public.broadcaster_details_rollup
(
       b_id_user BIGINT ENCODE lzo,
       b_username VARCHAR(10000) ENCODE lzo DISTKEY,
       b_email VARCHAR(10000) ENCODE lzo,
       b_user_status VARCHAR(10000) ENCODE lzo,
       b_channel_status VARCHAR(10000) ENCODE lzo,
       b_role VARCHAR(10000) ENCODE lzo,
       b_package VARCHAR(10000) ENCODE lzo,
       b_package_abbrev VARCHAR(10) ENCODE lzo,
       b_team VARCHAR(10000) ENCODE lzo,
       b_organization VARCHAR(10000) ENCODE lzo,
       b_user_date_created VARCHAR(10000) ENCODE lzo,
       b_channel_date_created VARCHAR(10000) ENCODE lzo,
       b_channel_time_created VARCHAR(10000) ENCODE lzo,
       b_channel_date_updated VARCHAR(10000) ENCODE lzo,
       b_followers_count BIGINT ENCODE lzo,
       b_unfollowers_count BIGINT ENCODE lzo,
       b_month VARCHAR(10000) ENCODE lzo,
       b_week VARCHAR(10000) ENCODE lzo,
       b_channel_name VARCHAR(10000) ENCODE lzo,
       b_last_broadcasted_date VARCHAR(10000) ENCODE lzo,
       b_channel_frozen_date VARCHAR(10000) ENCODE lzo,
       b_analytics_ignore SMALLINT,
       b_rank INTEGER ENCODE lzo,
       b_azubuteam VARCHAR(10000) ENCODE lzo
)
SORTKEY
(
       b_username
);

GRANT ALL ON TABLE public.broadcaster_details_rollup TO GROUP admin_group;
GRANT SELECT ON TABLE public.broadcaster_details_rollup TO GROUP readonly;


 INSERT INTO dev.public.broadcaster_details_rollup 

select
max(id_user) id_user
       , a.username
       , max(email) email
       , max(user_status) user_status
       , max(channel_status) channel_status
       , max(role) role
       , max(package) package
       , max(package_abbrev) package_abbrev
       , case when max(team) = ''  then a.username else max(team) end team
       , max(organization) organization
       , max(user_date_created)  user_date_created
       , max(channel_date_created) channel_date_created
       , max(channel_time_created) channel_time_created
       , max(channel_date_updated) channel_date_updated
       , max(followers_count) followers_count
       , max(unfollowers_count) unfollowers_count
       , max(month) as month
       , max(week) as week
       , max(channel_name) channel_name
       , max(last_broadcasted_date) last_broadcasted_date
       , max(channel_frozen_date) channel_frozen_date
       , max(analytics_ignore) analytics_ignore
       , null
       , case when max(b.azubuteam) is null  then a.username else max(azubuteam) end azubuteam
from broadcaster_details a left join broadcaster_details_azubuteams b on a.username=b.username
--where not exists (Select 1 from broadcaster_details_rollup br where br.b_username=b.username)
 group by a.username;
 
 



update broadcaster_details_rollup set b_rank=rr.rank
from ( select bc_azbroadcaster,
rank() over (order by sum(nvl(bc_video_seconds_viewed ,0)) desc, sum(nvl(bc_video_view ,0)) desc) as rank
from bc_videos_rollup
where bc_dt > dateadd(d,-7,(select max(bc_dt) from bc_videos_rollup)::date)
group by bc_azbroadcaster) rr
where broadcaster_details_rollup.b_username=rr.bc_azbroadcaster;

update broadcaster_details_rollup set b_rank=9999 where b_rank is null;


delete from broadcaster_Top_40 where dt = getdate()::date;
/*
 insert into broadcaster_Top_40
with st as ( select getdate()::date as stdt)
, current_ranking as (
 select bc_azbroadcaster azbroadcaster,
rank() over (order by sum(nvl(bc_video_seconds_viewed ,0)) desc, sum(nvl(bc_video_view ,0)) desc) as rank
,max(st.stdt) stdt
from bc_videos_rollup join st on 1=1
where bc_dt between dateadd(d,-7,st.stdt) and st.stdt
group by bc_azbroadcaster ) 
select azbroadcaster,rank,stdt,nvl(WeeksOnTop40+1,1) WeeksOnTop40 from (
select *,(select WeeksOnTop40 from broadcaster_Top_40 bt where bt.dt = (select max(dt) from broadcaster_Top_40) and bt.azbroadcaster=current_ranking.azbroadcaster)  WeeksOnTop40
from current_ranking)
where rank <=40
and datepart(dow,getdate())=4  --Only do this on Thursday
order by 2
;

*/


INSERT INTO broadcaster_Top_40
WITH st AS (
                                SELECT getdate()::DATE AS stdt
                                )
                , current_ranking AS (
                                SELECT b_azubuteam
                                                , rank() OVER (
                                                                ORDER BY sum(nvl(bc_video_seconds_viewed, 0)) DESC
                                                                                , sum(nvl(bc_video_view, 0)) DESC
                                                                ) AS rank
                                                , max(st.stdt) stdt
                                FROM bc_videos_rollup a
                                INNER JOIN PUBLIC.broadcaster_details_rollup b
                                                ON bc_azbroadcaster = b_username
                                INNER JOIN st
                                                ON 1 = 1
                                WHERE bc_dt BETWEEN dateadd(d, - 7, st.stdt)
                                                                AND st.stdt
                                GROUP BY b_azubuteam
                                )
SELECT b_azubuteam
                , rank
                , stdt
                , nvl(WeeksOnTop40 + 1, 1) WeeksOnTop40
FROM (
                SELECT *
                                , (
                                                SELECT WeeksOnTop40
                                                FROM broadcaster_Top_40 bt
                                                WHERE bt.dt = (
                                                                                SELECT max(dt)
                                                                                FROM broadcaster_Top_40
                                                                                )
                                                                AND bt.azbroadcaster = current_ranking.b_azubuteam
                                                ) WeeksOnTop40
                FROM current_ranking
                )
WHERE rank <= 40
                AND datepart(dow, getdate()) = 4 --Only do this on Thursday
ORDER BY 2;








 
";
if ($debug==1)
{
echo "\n*******StartQuery\n".$sql."\n*******EndQuery\n";
}
$rec = pg_query($connect,$sql);
$rowsaffected=pg_affected_rows($rec);
echo "Rows affected $rowsaffected \n\n";

?>