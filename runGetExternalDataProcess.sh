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
date
START_TIME=$SECONDS
MyPath="/home/paul/scripts/GetExternalData"
loop=${1:-8}
a=1
#enddate=`date +%Y-%m-%d`
#startdate=`date -v-1d +%F`
#echo "StartDate: " $startdate
#echo  "EndDate: " $enddate
cd $MyPath

while [ $a -le $loop ]
do
   echo $a
  
  #php ./BrightCove/getBCtags.php $a 1 #For Backfill 
  
  php ./BrightCove/getBCtags.php $a 0   #For Daily 
  a=`expr $a + 1`
  sleep 5
done

#Now Get Zencoder
date
ELAPSED_TIME=$(($SECONDS - $START_TIME))
echo "ELAPSED_TIME in SECONDS for BC:" $ELAPSED_TIME
START_TIME=$SECONDS

	php ./ZenCoder/GetZenCoderLoop.php
date
ELAPSED_TIME=$(($SECONDS - $START_TIME))
echo "ELAPSED_TIME in SECONDS for ZC:" $ELAPSED_TIME