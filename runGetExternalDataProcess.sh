#!/bin/bash
# My first script

#echo "Hello World!"
#loop=$1
date
loop=${1:-8}
a=1
#enddate=`date +%Y-%m-%d`
#startdate=`date -v-1d +%F`
#echo "StartDate: " $startdate
#echo  "EndDate: " $enddate


while [ $a -le $loop ]
do
   echo $a
  
  #php ./BrightCove/getBCtags.php $a 1 #For Backfill 

  php ./BrightCove/getBCtags.php $a 0   #For Daily 
  a=`expr $a + 1`
  sleep 5
done

#Now Get Zencoder
php ZenCoder/GetZenCoderLoop.php
date