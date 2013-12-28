#!/bin/bash
# loop for dequeue
while :
do
	echo
	echo "::START::"
	php cron.php
	sleep 5
done

