<?php
//////////////////////////////////////////////////////////////////
////////////////		Try Logging		/////////////////////////
////////////////////////////////////////////////////////////////
class LOG
{
// use STATIC rendering
	static private $fplog;	// file handler for logging
	
	// open logging file for writing
	static public function start($flogname = 'log.txt') {
		self::$fplog = fopen($flogname,'ab');
	}
	
	static public function stop() {
		fclose(self::$fplog);
	}

	static public function write($s, $usedate = true) {
	// пишем в лог-файл строку $s,
	// $date - вставлять ли в лог дату/время текущие
		if($usedate) 
			$tim = '['.date('Y-m-d H:i:s').'] ';
		else
			$tim = '';
		fwrite(self::$fplog,$tim.$s."\n");
	}
}

LOG::start();	// start default logging
