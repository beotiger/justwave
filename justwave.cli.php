<?php

	/* ***************************** */
	/*  		JustWave project				 */
	/*	(c) beotiger 2014-2015 AD		 */
	/*	http://justwave.beotiger.com */	
	/*	https://github.com/beotiger  */
	/*	email: beotiger@gmail.com		 */
	/* ***************************** */

if(!$argc)
	die('<h1>This script should be called in CLI environment</h1>');

$scriptName = array_shift($argv);

print "\n" . $scriptName . ' - PHP command line audio waveform generation.' . "\n";
print 'Generates waveforms from any media files (audio/video) and outputs them to png/gif/jpg files.' . "\n\n";

// mask is required
if($argc < 2) {
	printUsageAndOptions($scriptName);
	die;
}

set_time_limit(0);

$mask = array_shift($argv);
// default prog_color must be the same as wave_color
// in order to generate one image instead of two
$argv[] = 'prog_color=#909296';

// we need set prog_color to wave_color
// in order to get one wave image instead of two
foreach($argv as $val)
	if(preg_match('/^wave_color=(.*)$/', $val, $m))
		$argv[] = 'prog_color=' . $m[1];

// always use file mode
$argv[] = 'mode=file';

require_once 'JustWave.class.php';

// make class instance with ARGV parameters
$justwave = new JustWave('ARGV', $argv);

$numOfTotalFiles = $numOfSuccessFiles = 0;

foreach (glob($mask) as $fileName) {
	// create waves' images
	printf('Creating wave for %s', $fileName);
	$justwave->create($fileName);
	
	// rename image wave from key name to audio name
	if($justwave->status == 'ok') {
		$name = pathinfo($justwave->audio, PATHINFO_FILENAME);
		$newName = str_replace($justwave->key, $name, $justwave->dataUrlWave);
		
		if($newName) {
			rename($justwave->dataUrlWave, $newName);
			$justwave->dataUrlWave = $newName;
		}
		$numOfSuccessFiles++;
	}
	else
		$justwave->dataUrlWave = '???';

	printf(' in %s - %s (%s)' . "\n", $justwave->dataUrlWave, $justwave->status, $justwave->message);
	$numOfTotalFiles++;
} // foreach

print "** Success for $numOfSuccessFiles of $numOfTotalFiles files.\n";

function printUsageAndOptions($scriptName)
{
	print "Usage: php $scriptName mask [ options ]

mask - filename or pattern for media, e.g.: song.mp3 or *.ogg
	
options - optional parameter(s), in option=value format, for example:
	$ php $scriptName *.mp3 width=450 height=75 wave_color=#3F87BE back_color=#FFFFFF
	
Possible options are: (shown their default values):
	width=500 - width of the wave image (in pixels).
	height=100 - height of the wave image (in pixels).
	wave_color=#909296 - color of foreground wave in #RRGGBB format.
	back_color= - color of background in #RRGGBB format.
		If not set or is empty - transparent then.
	type=png - type of wave image: png|gif|jpg.
	wavedir=waves - directory in file system for storing wave image.
		Note 1: This directory is also used for temporary files
		during conversion process and should be writable.
		Note 2: On Windows' systems always use '/' in path instead of '\'.
										
Addition options:
	force=1 - do not seek for wave image in file system.
	nocache=1 - do not use caching mechanism (why?).
	twopass=1 - use two pass conversion for normalization.
";
}
