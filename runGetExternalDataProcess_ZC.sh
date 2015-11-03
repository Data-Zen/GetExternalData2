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


MyPath="/home/paul/scripts/GetExternalData"
START_TIME=$SECONDS

	php ./ZenCoder/GetZenCoderLoop.php


date
ELAPSED_TIME=$(($SECONDS - $START_TIME))

let ELAPSED_TIME_Minutes=$ELAPSED_TIME/60

echo "ELAPSED_TIME in SECONDS for ZC:" $ELAPSED_TIME

echo "ELAPSED_TIME in Minutes for ZC:" $ELAPSED_TIME_Minutes