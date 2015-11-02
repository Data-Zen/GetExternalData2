#!/bin/bash
#SHELL=/bin/bash
# Redirect stdout ( > ) into a named pipe ( >() ) running "tee"
#exec > >(tee .runGetExternalDataProcess.log)

# Without this, only stdout would be captured - i.e. your
# log file would not contain any error messages.
# SEE answer by Adam Spiers, which keeps STDERR a seperate stream -
# I did not want to steal from him by simply adding his answer to mine.
#exec 2>&1

#echo "Hello World!"
#loop=$1


backfill=1

date
START_TIME=$SECONDS
MyPath="/home/paul/scripts/GetExternalData"
daysback=3
if [ "$backfill" -eq 1 ] ; then
	daysback=500
fi
let loopcount=$daysback+30

loop=${1:-$loopcount}
a=1
#enddate=`date +%Y-%m-%d`
#startdate=`date -v-1d +%F`
#echo "StartDate: " $startdate
#echo  "EndDate: " $enddate
cd $MyPath
  
  php ./BrightCove/getBCtags_first.php #$a 0 $daysback   #For Daily 
  php ./BrightCove/loadbcinclude_tags.php #$a 0 $daysback   #For Daily 
while [ $a -le $loop ]
do
   echo $a
   echo "Days back: $daysback"
  
  php ./BrightCove/getBCtags.php $a $backfill $daysback 
  a=`expr $a + 1`
  sleep 5
done

#Now Get Zencoder
date
ELAPSED_TIME_BC=$(($SECONDS - $START_TIME))
let ELAPSED_TIME_BC_Minutes=$ELAPSED_TIME_BC/60
echo "ELAPSED_TIME in SECONDS for BC:" $ELAPSED_TIME
START_TIME=$SECONDS

	php ./ZenCoder/GetZenCoderLoop.php


date
ELAPSED_TIME=$(($SECONDS - $START_TIME))

let ELAPSED_TIME_Minutes=$ELAPSED_TIME/60
echo "ELAPSED_TIME in SECONDS for BC:" $ELAPSED_TIME_BC
echo "ELAPSED_TIME in Minutes for BC:" $ELAPSED_TIME_BC_Minutes
echo "ELAPSED_TIME in SECONDS for ZC:" $ELAPSED_TIME

echo "ELAPSED_TIME in Minutes for ZC:" $ELAPSED_TIME_Minutes