<?
// Raspberry Pi - AirPiano PHP
// Copyright (C) 2013 Jason A. Cox
// 
// Permission is hereby granted, free of charge, to any person obtaining a copy
// of this software and associated documentation files (the "Software"), to 
// deal in the Software without restriction, including without limitation the 
// rights to use, copy, modify, merge, publish, distribute, sublicense, and/or 
// sell copies of the Software, and to permit persons to whom the Software is 
// furnished to do so, subject to the following conditions:
// 
// The above copyright notice and this permission notice shall be included in 
// all copies or substantial portions of the Software.
// 
// THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR 
// IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, 
// FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE 
// AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER 
// LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING 
// FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER 
// DEALINGS IN THE SOFTWARE.
//
// Description
//
//    Remote control your MIDI keyboard/digital piano via your smartphone.
//    This allows you to pick MID and WAV files to play via the Raspberry Pi
//    that is connected to the MIDI and Audio port on  your MIDI device.
//
// Setup
//
//    MySQL:  Database 'piano' - see piano.sql file
//
//    Dequeue Service:  Run the cron.sh script to have the RPI scan for
//        new midi or wave files to play.  Run it with:
//               bash -x cron.sh 0<&- 1>/dev/null 2>/dev/null &
//
//    Apache: Install apache http with mod_php and mysql support
//
//    Website Code: Install this index.php file and the folder.png image
//        into the document root of your webserver.  Upload the MID and WAV
//        files to this location, indicated as $globalBase below.  Be sure to
//        update $globalBase to the folder where these files are located.
//

// Configuration Variables
// EDIT THIS FILE - setup.php - adjust for base URL and script name
include 'setup.php';

// HTML and PHP Code - Using Responsive Design
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">

<!-- viewport meta to reset iPhone inital scale -->
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>AirPiano</title>

<!-- css3-mediaqueries.js for IE8 or older -->
<!--[if lt IE 9]>
	<script src="http://css3-mediaqueries-js.googlecode.com/svn/trunk/css3-mediaqueries.js"></script>
<![endif]-->

<style type="text/css">

body {
	font: 0.7em/120% Arial, Helvetica, sans-serif;
}
a {
	color: #669;
	text-decoration: none;
}
a:hover {
	text-decoration: underline;
}
h1 {
	font: bold 36px/100% Arial, Helvetica, sans-serif;
}

/************************************************************************************
STRUCTURE
*************************************************************************************/
#pagewrap {
	padding: 5px;
	width: 960px;
	margin: 20px auto;
}
#header {
	/** height: 180px; *?
}
#content {
	width: 600px;
	float: left;
}
#sidebar {
	width: 300px;
	float: right;
}
#footer {
	clear: both;
}

/************************************************************************************
MEDIA QUERIES
*************************************************************************************/
/* for 980px or less */
@media screen and (max-width: 980px) {
	
	#pagewrap {
		width: 94%;
	}
	#content {
		width: 65%;
	}
	#sidebar {
		width: 30%;
	}

}

/* for 700px or less */
@media screen and (max-width: 700px) {

	#content {
		width: auto;
		float: none;
	}
	#sidebar {
		width: auto;
		float: none;
	}

}

/* for 480px or less */
@media screen and (max-width: 480px) {

	#header {
		height: auto;
	}
	h1 {
		font-size: 24px;
	}
	#sidebar {
		display: none;
	}

}

/* border & guideline (you can ignore these) */
#content {
	background: #f8f8f8;
}
#sidebar {
	background: #f0efef;
}
#header, #content, #sidebar {
	margin-bottom: 5px;
}
#pagewrap, #header, #content, #sidebar, #footer {
	border: solid 1px #ccc;
}

</style>
</head>

<body>
<?
//
// GLOBAL
//
if(isset($_REQUEST['dir'])) {
	$globaldir = $_GET["dir"];
}
else $globaldir = ".";

//
// PLAY
//
if(isset($_REQUEST['play'])) {

	$con = mysqli_connect('localhost',$dbuser,$dbpass,$dbname);
	if (!$con)
	  {
	  die('Could not connect: ' . mysqli_error($con));
	  }

	mysqli_select_db($con,"piano");
	if($_GET["play"]) {
		$stmt= $con->prepare("INSERT INTO `midisongs` (`id`, `file`, `timestamp`, `state`) VALUES (NULL, ?, CURRENT_TIMESTAMP, 'WAIT');");
		$stmt->bind_param('s', $_GET["play"]);
		$stmt->execute();
		$stmt->close();
		echo "<b>Added</b><br>";
	}

	mysqli_close($con);
}

//
// DELETE
//

if(isset($_REQUEST['del'])) {
	$con = mysqli_connect('localhost',$dbuser,$dbpass,$dbname);
	if (!$con)
	  {
	  die('Could not connect: ' . mysqli_error($con));
	  }

	mysqli_select_db($con,"piano");
	if($_GET["del"] > 0) {
		$sql="DELETE FROM midisongs WHERE id=". $_GET["del"] ." LIMIT 1";
		$result = mysqli_query($con,$sql);
		echo "<b>Deleted</b><br>";
	}

	mysqli_close($con);
}
?>

<div id="pagewrap">

	<div id="header">
		<h1>AirPiano</h1>
<?php

$con = mysqli_connect('localhost',$dbuser,$dbpass,$dbname);
if (!$con)
  {
  die('Could not connect: ' . mysqli_error($con));
  }

mysqli_select_db($con,"piano");
$sql="SELECT * FROM midisongs ORDER by timestamp";

$result = mysqli_query($con,$sql);

echo "<table border='1'>
<tr>
<th>In Queue</th>
<th>State</th>
<th>Delete</th>
</tr>";

while($row = mysqli_fetch_array($result))
  {
  echo "<tr>";
  echo "<td>" . $row['file'] . "</td>";
  echo "<td>" . $row['state'] . "</td>";
  echo "<td><a href='$globalBase$globalScript?dir=$globaldir&del=" . $row['id'] . "'>Delete</a></td>";

  echo "</tr>";
  }
echo "</table>";

mysqli_close($con);
?>
	</div>

	<div id="content">
		<h2>Media Library</h2>
	
<?

// breadcrumb path
if($_REQUEST['dir']) {
	 $crumbs = explode("/",$_REQUEST['dir']);
} else  {
	 $crumbs = explode("/",".");
}
echo "<p>Path: ";
$builddir = ".";
foreach($crumbs as $crumb){
    if($crumb == ".") {
	echo "<a href='$globalBase$globalScript?dir=$builddir'> [ROOT] </a>\n";
    }
    else {
	    $builddir = $builddir . "/" . $crumb;
	    echo "<a href='$globalBase$globalScript?dir=$builddir'> [$crumb] </a>\n";
   }
    echo "/ ";
}
echo "<br>";
echo "<br>";

// display current
if(isset($_REQUEST['dir'])) {
    $current_dir = $_REQUEST['dir'];
} else {
    $current_dir = '.';
}

//
// GRAB DIRS AND FILES
//
$listFiles = array();
$listDirs = array();

if ($handle = opendir($current_dir)) {
     while (false !== ($file_or_dir = readdir($handle))) {
        if(in_array($file_or_dir, array('.', '..'))) continue;
	if(substr($file_or_dir,0,1) == '.') continue;
        $path = $current_dir.'/'.$file_or_dir;
        if(is_dir($path)) 
		$listDirs[] = $file_or_dir;
        if(is_file($path)) {
		$listFiles[] = $file_or_dir;
        } 
    }
    closedir($handle);
}

sort($listFiles, SORT_NATURAL | SORT_FLAG_CASE);
sort($listDirs, SORT_NATURAL | SORT_FLAG_CASE);

//
// LIST DIRS
//
foreach($listDirs as $file_or_dir) {
    $path = $current_dir.'/'.$file_or_dir;
    echo '<img src="'.$globalBase.'folder.png">';
    echo '<a href="'.$globalBase.$globalScript.'?dir='.$path.'"> ['.$file_or_dir."]\n</a><br/>";
}
//
// LIST FILES
//
foreach($listFiles as $file_or_dir) {
    if(!(substr($file_or_dir,-4,4) == ".wav" || substr($file_or_dir,-4,4) == ".mid")) continue;
    $path = $current_dir.'/'.$file_or_dir;
    echo '<a href="'.$globalBase.$globalScript.'?dir='.$globaldir.'&play='.$path.'">'.$file_or_dir."\n</a><br/>";
}

?>
</p>
		<p></p>
	</div>

	<div id="sidebar">
		<h3></h3>
		<p></p>
	</div>
	
	<div id="footer">
		<h4></h4>
	</div>

</div>

</body>
</html>
