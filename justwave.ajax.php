<?php

	/* ***************************** */
	/*  		JustWave project				 */
	/*	(c) beotiger 2014-2015 AD		 */
	/*	http://justwave.beotiger.com */	
	/*	https://github.com/beotiger  */
	/*	email: beotiger@gmail.com		 */
	/* ***************************** */

require_once 'JustWave.class.php';

if(isset($_POST['audio'])) {
	// make class instance with default to POST parameters
	$justwave = new JustWave();
	// create waveform image(s)
	$justwave->create($_POST['audio']);
	// return as JSON data
	die($justwave->json());
}

die(json_encode(array('status' => 'err', 'message' => 'No source audio parameter')));
