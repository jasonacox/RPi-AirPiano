RPi-AirPlay
===========

Projects for the Raspberry Pi

Website: http://www.jasonacox.com/wordpress/archives/86

AirPiano
========

## Description

   Remote control your MIDI keyboard/digital piano via your smartphone.
   This allows you to pick MID and WAV files to play via the Raspberry Pi
   that is connected to the MIDI and Audio port on  your MIDI device.

## Setup

*   MySQL:  Database 'piano' - see `piano.sql` file

*   Dequeue Service:  Run the cron.sh script to have the RPI scan for
       new midi or wave files to play.  Run it with:
             ` bash -x cron.sh 0<&- 1>/dev/null 2>/dev/null &`

*   Apache: Install apache http with mod_php and mysql support

*   Website Code: Install this `index.php` file and the `folder.png` image
       into the document root of your webserver.  Upload the MID and WAV
       files to this location, indicated as `$globalBase`.  Be sure to
       update `$globalBase` to the folder where these files are located.
	  EDIT the `setup.php` file for the globals.


