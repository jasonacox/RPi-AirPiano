::CHECK::
<?
// Raspberry Pi - AirPiano PHP - Dequeue Cron Job
// Copyright (C) 2013 Jason A. Cox
//
// include globals - EDIT THIS FILE!
include 'setup.php';

// functions
function startsWith($haystack, $needle)
{
    return $needle === "" || strpos($haystack, $needle) === 0;
}
function endsWith($haystack, $needle)
{
    return $needle === "" || substr($haystack, -strlen($needle)) === $needle;
}

// open mysql get top
$con = mysqli_connect('localhost',$dbuser,$dbpass,$dbname);
if (!$con)
  {
  die('Could not connect: ' . mysqli_error($con));
  }

mysqli_select_db($con,"piano");
$sql="SELECT * FROM midisongs ORDER by timestamp LIMIT 1";
$result = mysqli_query($con,$sql);
if (!$result) {
	die('::NONE::');
}

$row = mysqli_fetch_array($result);
if (!$row) {
	die('::NONE::');
}
$file = $row['file'];
$id = $row['id'];

// change status to playing
$sql ="UPDATE `midisongs` SET `state` = 'PLAY' WHERE `midisongs`.`id` = ".$id;
$result = mysqli_query($con,$sql);

echo $file;
echo "\n";
if(endsWith($file,".wav")) {
	// play audio file
	echo "(Audio) ";
	print('aplay '.escapeshellarg("$globalpath$file"));
	system('aplay '.escapeshellarg("$globalpath$file"));
	// delete
        $sql="DELETE FROM midisongs WHERE id=". $id ." LIMIT 1";
        $result = mysqli_query($con,$sql);
}
if(endsWith($file,".mid")) {
	// play MIDI file
	echo "(MIDI) ";
	print('aplaymidi --port=20:0 '.escapeshellarg("$globalpath$file"));
	system('aplaymidi --port=20:0 '.escapeshellarg("$globalpath$file"));
	// delete
        $sql="DELETE FROM midisongs WHERE id=". $id ." LIMIT 1";
        $result = mysqli_query($con,$sql);
}
echo "::DONE::\n";


?>
