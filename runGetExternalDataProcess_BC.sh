#! /bin/bash
SHELL=/bin/bash
# Redirect stdout ( > ) into a named pipe ( >() ) running "tee"
#exec > >(tee .runGetExternalDataProcess.log)

# Without this, only stdout would be captured - i.e. your
# log file would not contain any error messages.
# SEE answer by Adam Spiers, which keeps STDERR a seperate stream -
# I did not want to steal from him by simply adding his answer to mine.
#exec 2>&1

#echo "Hello World!"
#loop=$1

if mkdir /tmp/lock_GetExternalData; then
  echo "Running Script" >&2
else
  echo "Script Already running. Lock Creation Failed failed - exit
  If you think this is an error try running: 'rm -rf /tmp/lock_GetExternalData'" >&2
  exit 1
fi


backfill=0
ZC=1

date
START_TIME=$SECONDS
MyPath="/home/paul/scripts/GetExternalData"
daysback=120
#daysback=160
let sleepv=1
if [ "$backfill" -eq 1 ] ; then
	daysback=500
fi
let loopcount=$daysback+7

loop=${1:-$loopcount}
a=1
#enddate=`date +%Y-%m-%d`
#startdate=`date -v-1d +%F`
#echo "StartDate: " $startdate
#echo  "EndDate: " $enddate
cd $MyPath
  
  php ./BrightCove/getBCtags_first.php 


let errorloop=0

while [ $a -le $loop ]
do
   echo "Currently on Day $a of $loop"
  php ./BrightCove/BCProcessing.php $a $backfill $daysback
  exitcode=$?
  #echo $output
  echo "exitcode: $exitcode"

    if [ "$exitcode" -ne 0 ] && [ "$errorloop" -le 2 ]; then
      let errorloop=$errorloop+1
      echo "Starting error $sleepv second sleep. Error Attempt $errorloop"
      sleep $sleepv
      echo "Finished error $sleepv second sleep"
      let sleepv=$sleepv*$errorloop
    else 
      if [ "$errorloop" -eq 0 ]; then
        echo "No Errors"
      else
        echo "Moving on From Errors"
      fi
      a=`expr $a + 1`      
      let errorloop=0
      echo "Starting regular $sleepv second sleep"
      sleep $sleepv
      echo "Finished regular $sleepv second sleep"
    fi


done

#Now Get Zencoder
date
ELAPSED_TIME_BC=$(($SECONDS - $START_TIME))
let ELAPSED_TIME_BC_Minutes=$ELAPSED_TIME_BC/60
echo "ELAPSED_TIME in SECONDS for BC:" $ELAPSED_TIME
START_TIME=$SECONDS
if [ "$ZC" -eq 1 ]; then

	php ./ZenCoder/GetZenCoderLoop.php
fi
#date
ELAPSED_TIME_ZC=$(($SECONDS - $START_TIME))
START_TIME=$SECONDS
php ./BrightCove/AZData/GetBroadcasterData_live.php 
php ./BrightCove/AZData/GetBroadcasterData.php 
php ./BrightCove/AZData/FinalProcessing.php

rm -rf /tmp/lock_GetExternalData
ELAPSED_TIME_FP=$(($SECONDS - $START_TIME))

let ELAPSED_TIME_Minutes_ZC=$ELAPSED_TIME_ZC/60
let ELAPSED_TIME_Minutes_FP=$ELAPSED_TIME_FP/60
echo "ELAPSED_TIME in SECONDS for BC:" $ELAPSED_TIME_BC
echo "ELAPSED_TIME in Minutes for BC:" $ELAPSED_TIME_BC_Minutes
echo "ELAPSED_TIME in SECONDS for ZC:" $ELAPSED_TIME_ZC
echo "ELAPSED_TIME in Minutes for ZC:" $ELAPSED_TIME_Minutes_ZC
echo "ELAPSED_TIME in SECONDS for FP:" $ELAPSED_TIME_FP
echo "ELAPSED_TIME in Minutes for FP:" $ELAPSED_TIME_Minutes_FP
date
