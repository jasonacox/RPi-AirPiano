#!/bin/bash
pgrep -f shairport

#if we get no pids, service is not runnint

if [ $? -ne 0 ]
then
   service shairport start
   echo "shairport started."
fi
