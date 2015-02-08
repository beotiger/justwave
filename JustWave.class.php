<?php
// uncomment next string and LOG::write instances to have logging
// require_once 'log.class.php';

class JustWave
{
	/* ***************************** */
	/*  		JustWave class					 */
	/*	(c) beotiger 2014-2015 AD		 */
	/*	http://justwave.beotiger.com */	
	/*	https://github.com/beotiger  */
	/*	email: beotiger@gmail.com		 */
	/* ***************************** */

	const PCRE_COLOR = '/^#?[\da-fA-F]{3}([\da-fA-F]{3})?$/'; // check color strings
	const CACHE_FILENAME = 'justwave.sqlite3'; // path for sqlite3 database file
	// path to ffmpeg binary, rename it to 'avconv' if your system has it instead
	const FFMPEG_PATH = 'ffmpeg';

	/**
		Input variables for creating and managing waves 
	**/
	
	/** @var string Container for parameters to fetch from */
	private $_OPTS = array();
	/** @var integer Width of wave */
	private $width = 500;
	/** @var integer Height of wave */
	private $height = 100;
	
	/** @var string Color of main wave */
	private $waveColor = '#909296';
	/** @var string Color of progress wave */
	private $progressColor = '#FF530D';
	/** @var string Color of background, empty string - transparent */
	private $backgroundColor = '';
	
	/** @var string Output mode: dataurl or file */	
	private $mode = 'dataurl';
	/** @var string Directory for storing waves' images */
	private $waveDir = 'waves/';
	
	/** @var boolean Force creating wave in spite of existence */
	private $force = false;
	/** @var boolean We can use cached waves and store new waves in cache */
	private $useCache = true;
	/** @var boolean Use two-pass encoding for normalization */
	private $twopass = false;

	/** @var resource SQlite database handler for caching waves */
	private $db;
	/** @var resource GD image of main wave */
	private $waveImg;
	/** @var resource GD image of progress wave */
	private $progressImg;

	/** @var blob raw data of image for main wave */
	private $waveData;
	/** @var blob raw data of image for progress wave */
	private $progressData;
	
	/**
		Output variables
	**/
	
	/** @var string Status ok|err */
	public $status;
	/** @var string Message */
	public $message;
	/** @var fixed Duration of audio in seconds */
	public $duration;
	/** @var string Url or path to the audio */
	public $url;
	/** @var string MD5 key */
	public $key;
	/** @var string Name of audio */
	public $audio;
	/** @var string Image src or data-url of main wave */
	public $dataUrlWave;
	/** @var string Iamge src or data-url of progress wave */
	public $dataUrlProgress;
	
	/**
	 * Initialize variables according to $this->_OPTS values
	 */
	private function init()
	{
		if(isset($this->_OPTS['mode']))
			$this->mode = strtolower($this->_OPTS['mode']);
		
		if(isset($this->_OPTS['wavedir'])) {
			$this->waveDir = $this->_OPTS['wavedir'];
			// it must be slashed at the end
			if(substr($this->waveDir, -1) != '/')
				$this->waveDir .= '/';
		}
		// try to create directory if it not exists and make it writable
		if(!is_writable($this->waveDir)) {
			@mkdir($this->waveDir, 0755);
			@chmod($this->waveDir, 0755);
		}

		// dimensions of image in pixels
		if(($width = intval(@$this->_OPTS['width'])) > 0)
			$this->width = $width;
		if(($height = intval(@$this->_OPTS['height'])) > 0)
			$this->height = $height;

		// main wave color
		if(@preg_match(JustWave::PCRE_COLOR, $this->_OPTS['wave_color']))
			$this->waveColor = JustWave::htmlColor($this->_OPTS['wave_color']);
		// progress color
		if(@preg_match(JustWave::PCRE_COLOR, $this->_OPTS['prog_color']))
			$this->progressColor = JustWave::htmlColor($this->_OPTS['prog_color']);
		// background color
		if(@preg_match(JustWave::PCRE_COLOR, $this->_OPTS['back_color']))
			$this->backgroundColor = JustWave::htmlColor($this->_OPTS['back_color']);

		// Flags
		// force creating wave images
		if(isset($this->_OPTS['force']))
			$this->force = true;
		// do not use cache at all
		if(isset($this->_OPTS['nocache']))
			$this->useCache = false;
		// use two-pass encoding for normalization
		if(isset($this->_OPTS['twopass']))
			$this->twopass = true;
	}

	/**
	 * Save image files from GD-waves or set data-url
	 */
	private function saveWaves()
	{
		// output first png according to mode parameter
		ob_start();
			call_user_func('imagepng', $this->waveImg);
		$rawImage = ob_get_clean();
		$this->waveData = $rawImage;
		
		if($this->mode == 'dataurl')
			$this->dataUrlWave = 'data:image/png;base64,' . base64_encode($rawImage);
		else {
			$this->dataUrlWave = $this->waveDir . $this->key . '.png';
			file_put_contents($this->dataUrlWave, $rawImage);
		}
    
		if($this->waveColor == $this->progressColor)
			return;

		// repeat for progress wave
		ob_start();
			call_user_func('imagepng', $this->progressImg);
		$rawImage = ob_get_clean();
		$this->progressData = $rawImage;
	
		if($this->mode == 'dataurl')
			$this->dataUrlProgress = 'data:image/png;base64,' . base64_encode($rawImage);
		else {
			$this->dataUrlProgress = $this->waveDir . $this->key . '_bg.png';
			file_put_contents($this->dataUrlProgress, $rawImage);
		}
	} // saveWaves()

	/**
	* Create waves in GD images format from WAV (PCM) file
	* Wave file reading based on a post by "zvoneM" on
	* 	http://forums.devshed.com/php-development-5/reading-16-bit-wav-file-318740.html
	* Completely rewritten the file read loop, kept the header reading intact.
	*
	* Waveform drawing from https://github.com/afreiday/php-waveform-png
	*
	* Reads width * ACCURACY data points from the file and takes the peak value of accuracy values.
	*	The peak is the highest value if mean is > 127 and the lowest value otherwise.
	* Data point is the average of ACCURACY points in the data block.
	*/
	private function createWaves($wavfilename)
	{
		define('ACCURACY', 100); // default optimal accuracy
    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - 
    // Create palette based image
		// if background == '' we convert it to true color image later then
    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - 
    $this->waveImg = imagecreate($this->width, $this->height);

    // fill background of image
    if ($this->backgroundColor == '') {
			// try to choose color that does not match
			// waveColor or progressColor
			// three colors should be sufficient
			$colors = array('#FFFFFF','#000000','#FF0000');
			foreach($colors as $col) {
				$tempBackground = $col;
				if($tempBackground != strtoupper($this->waveColor) &&
							$tempBackground != strtoupper($this->progressColor))
					break;
			}
    } else
			$tempBackground = $this->backgroundColor;

		list($r, $g, $b) = JustWave::html2rgb($tempBackground);
		$transparentColor = imagecolorallocate($this->waveImg, $r, $g, $b);
		imagefilledrectangle($this->waveImg, 0, 0, $this->width, $this->height,	$transparentColor);

    // generate foreground color
		list($r, $g, $b) = JustWave::html2rgb($this->waveColor);
		$waveColor = imagecolorallocate($this->waveImg, $r, $g, $b);
//		$subColor = imagecolorallocate($this->waveImg, (int)$r * 0.5 , (int)$g * 0.5, (int) $b * 0.5);
    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - 
    // Read wave header
    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - 
    $handle = fopen($wavfilename, 'rb');

    $heading[] = fread ($handle, 4);
    $heading[] = bin2hex(fread ($handle, 4));
    $heading[] = fread ($handle, 4);
    $heading[] = fread ($handle, 4);
    $heading[] = bin2hex(fread ($handle, 4));
    $heading[] = bin2hex(fread ($handle, 2));
    $heading[] = bin2hex(fread ($handle, 2));
    $heading[] = bin2hex(fread ($handle, 4));
    $heading[] = bin2hex(fread ($handle, 4));
    $heading[] = bin2hex(fread ($handle, 2));
    $heading[] = bin2hex(fread ($handle, 2));
    $heading[] = fread ($handle, 4);
    $heading[] = bin2hex(fread ($handle, 4));

    if ($heading[5] != '0100') {
			$this->raiseError('Wave file should be a PCM file');
			return false;
		}

    $peek = hexdec(substr($heading[10], 0, 2));
    $byte = $peek / 8;
    $channel = hexdec(substr($heading[6], 0, 2));

    // point = one data point (pixel), width total
    // block = one block, there are $accuracy blocks per point
    // chunk = one data point 8 or 16 bit, mono or stereo
    $filesize  = filesize($wavfilename);
    $chunksize = $byte * $channel;  

    $file_chunks = ($filesize - 44) / $chunksize;
    if ($file_chunks < $this->width) {
			$this->raiseError("Wave file has $file_chunks chunks, " . ($this->width) . ' required');
			return false;
		}

    if ($file_chunks < $this->width * ACCURACY)
			$accuracy = 1;
		else
			$accuracy = ACCURACY;
			
    $point_chunks = $file_chunks / ($this->width);
    $block_chunks = $file_chunks / ($this->width * $accuracy);

    $blocks = array();
    $points = 0; 
    $current_file_position = 44.0; // float, because chunks/point and clunks/block are floats too.
    fseek($handle, 44);

    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - 
    // Read the data points and draw the image
    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - 
    while(!feof($handle))
    {
        // The next file position is the float value rounded to the closest chunk
        // Read the next block, take the first value (of the first channel)
        $real_pos_diff = ($current_file_position - 44) % $chunksize;
        if ($real_pos_diff > ($chunksize / 2)) $real_pos_diff -= $chunksize;
        fseek($handle, $current_file_position - $real_pos_diff);

        $chunk = fread($handle, $chunksize);
        if (feof($handle) && !strlen($chunk)) break; 

        $current_file_position += $block_chunks * $chunksize;

        if ($byte == 1) 
            $blocks[] = ord($chunk[0]); // 8 bit
        else
            $blocks[] = ord($chunk[1]) ^ 128; // 16 bit

        // Do we have enough blocks for the current point?
        if (count($blocks) >= $accuracy)
        {
            // Calculate the mean and add the peak value to the array of blocks
            sort($blocks);
            $mean = (count($blocks) % 2) ? $blocks[(count($blocks) - 1) / 2]
                       : ($blocks[count($blocks) / 2] + $blocks[count($blocks) / 2 - 1]) / 2;    
            if ($mean > 127) $point = array_pop($blocks); else $point = array_shift($blocks);
            
            // Draw 
            $lineheight = round($point / 255 * $this->height);
            imageline($this->waveImg, $points, 0 + ($this->height - $lineheight), $points, 
												$this->height - ($this->height - $lineheight),
												$waveColor);
											
            // update vars
            $points++;
            $blocks = array();
        }
    }

    // close wave file
    fclose ($handle);
		
		// final line
		imageline($this->waveImg, 0, round($this->height / 2), $this->width, 
								round($this->height / 2), $waveColor);

		if($this->waveColor != $this->progressColor) {								
			$this->progressImg = imagecreate($this->width, $this->height);
			imagecopy($this->progressImg, $this->waveImg, 0, 0, 0, 0, $this->width, $this->height);
			// change waveColor to progressColor
			$index = imagecolorclosest($this->waveImg, $r, $g, $b);
			list($r, $g, $b) = JustWave::html2rgb($this->progressColor);
			imagecolorset($this->progressImg, $index, $r, $g, $b);
		}
		
		// try to save transparency
    if ($this->backgroundColor == '') {
			imagealphablending($this->waveImg, false);
			imagesavealpha($this->waveImg, true);
			imagealphablending($this->waveImg, true);
			imagecolortransparent($this->waveImg, $transparentColor);

			if($this->waveColor != $this->progressColor) {
				imagealphablending($this->progressImg, false);
				imagesavealpha($this->progressImg, true);
				imagealphablending($this->progressImg, true);
				imagecolortransparent($this->progressImg, $transparentColor);
			}
    }
		
		return true;
	} // private function createWaves($wavfilename)

	/**
	 * Converts audio input into WAV PCM
	 * By default uses 2-pass for normalization 
	 * 	and determining duration of an audio
	 *
 	 * @param string $src Path to an audio file
	 */
	private function convert($src)
	{
		$out = $m = array();
		$db = '';
		
		if($this->twopass) {
			// Normalize
			@exec(JustWave::FFMPEG_PATH . " -i \"$src\" -af \"volumedetect\" -f null /dev/null 2>&1", $out);
			
			for($i = 0, $c = count($out); $i < $c; $i++)
				if(preg_match('/max_volume:\s+-?(\d+)/', $out[$i], $m)) {
					if(intval($m[1]) > 0)
						$db = "-af \"volume={$m[1]}dB\"";
					break;
				}
		}
		
		$out = $m = array(); // ffmpeg output
		// waveDir uses for temporary wav files
		$wav = $this->waveDir . uniqid('wave') . '.wav';
		// convert mp3 to wav using FFMPEG
		$cmd = JustWave::FFMPEG_PATH . " -y -i \"$src\" $db \"$wav\" 2>&1";
		@exec($cmd, $out);
/*
		LOG::write('FFMPEG command:' . $cmd);
		LOG::write('FFMPEG output:');
		LOG::write(var_export($out, true));
*/
		// WAV file has 44 bytes length header
		if(@filesize($wav) < 44) {
			@unlink($wav);
			$this->raiseError('Conversion failed');
			return false;
		}

		// try to find duration
		for($i = 0, $c = count($out); $i < $c; $i++)
			if(preg_match('/Duration: (\d\d):(\d\d):(\d\d)\.(\d+),/', $out[$i], $m)) {
				$this->duration = $m[1] * 3600 + $m[2] * 60 + $m[3] . '.' . $m[4];
				break;
			}
		
		// return file name of the new WAV file
		return $wav;
	}

	/**
	 * Check cache for existing waves
	 */
	private function checkCache()
	{
		// may we use caching mechanism?
		if(!$this->useCache)
			return false;

		$md5 = $this->key;

		try {
			$this->db = new PDO('sqlite:' . JustWave::CACHE_FILENAME);
			$this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$this->db->exec('CREATE TABLE IF NOT EXISTS waves (
													md5key TEXT, filename TEXT,
													width INTEGER, height INTEGER, fgrd TEXT, pgrd TEXT, bgrd TEXT,
													wavedata BLOB, progressdata BLOB,	duration FLOAT, dtm DATETIME,
												PRIMARY KEY (md5key))');
			
			$request = $this->db->query("SELECT filename, wavedata, progressdata, duration 
						FROM waves WHERE md5key='$md5' LIMIT 1");
			list($filename, $waveData, $progressData, $this->duration) = 
				$request->fetch(PDO::FETCH_NUM);

			if(!$filename)
				return false;

			// waves are in cache
			if($this->mode == 'dataurl')
				$this->dataUrlWave = 'data:image/png;base64,' . base64_encode($waveData);
			else {
				$this->dataUrlWave = $this->waveDir . $this->key . '.png';
				file_put_contents($this->dataUrlWave, $waveData);
			}
			
			if($this->waveColor != $this->progressColor) {
				// repeat for progress wave
				if($this->mode == 'dataurl')
					$this->dataUrlProgress = 'data:image/png;base64,' . base64_encode($progressData);
				else {
					$this->dataUrlProgress = $this->waveDir . $this->key . '_bg.png';
					file_put_contents($this->dataUrlProgress, $progressData);
				}
			}
			else if($this->mode != 'dataurl') // wave images are the same
				$this->dataUrlProgress = $this->waveDir . $this->key . '.png';

			$this->status = 'ok';
			$this->message = 'In cache';

			return true;
		}
		catch(PDOException $e) {
			// LOG::write('** PDOException' . $e->getMessage());
			return false;
		}
	}

	/**
	 * Check for existence of waves' images in output directory
	 *
 	 * @param string $md5 Key
	 */
	private function waveExists()
	{
		// force to create new waves
		if($this->force)
			return false;

		$waveName = $this->waveDir . $this->key . '.png';
		$progressName = $this->waveDir . $this->key . '_bg.png';

		if(!file_exists($waveName))
			return false;
		if($this->waveColor != $this->progressColor 
					&& !file_exists($progressName))
			return false;
		
		// NOTE: does not define duration of an audio
		$this->status = 'ok';
		$this->message = 'Images exist';

		if($this->mode != 'dataurl') {
			$this->dataUrlWave = $this->dataUrlProgress = $waveName;
			if($this->waveColor != $this->progressColor)
				$this->dataUrlProgress = $progressName;

			return true;
		}

		$this->dataUrlWave = 'data:image/png;base64,' . 
			base64_encode(file_get_contents($waveName));

		if($this->waveColor != $this->progressColor)
			$this->dataUrlProgress = 'data:image/png;base64,' . 
				base64_encode(file_get_contents($progressName));

		return true;
	}

	/**
	 * Caching waves images in SQlite database
	 *
	 */
	private function cache()
	{
		// may we use caching mechanism?
		if(!$this->useCache)
			return;	// nope!
			
		try {
			$stm = $this->db->prepare("INSERT INTO waves
						VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
			$stm->execute(array($this->key, $this->url,
							$this->width, $this->height,
							$this->waveColor, $this->progressColor, $this->backgroundColor,
							$this->waveData, $this->progressData,
							$this->duration, date('Y-m-d H:i:s')));
		}
		catch(PDOException $e) { 
			// LOG::write('** PDOException' . $e->getMessage()); 
		}
	}
	
	/**
	 * Set status and message for failed result
	 * 
	 * @param string $message Output message
	 */
	private function raiseError($message)
	{
		$this->status = 'err';
		$this->message = $message;
	}

	/**
	 * Validate HTML color in format (#)RGB or (#)RRGGBB
	 * and convert it to #RRGGBB value
	 *
	 * @param string $color Color in (#)RGB or (#)RRGGBB format
	*/
	private static function htmlColor($color)
	{
		if(strlen($color) < 4)
			$color = '#' . $color;
		if(strlen($color) == 4) // #RGB
			$color = '#' . substr($color,1,1) . substr($color,1,1)  .
				substr($color,2,1) . substr($color,2,1) .
				substr($color,3,1) . substr($color,3,1);
		if(strlen($color) == 6) // RRGGBB
			$color = '#' . $color;
		return strtoupper($color);
	}

	/**
	 * Convert HTML 6-hexed RGB color to decimal numbers
	 *
	 * @param string $color Color in #RRGGBB format
	*/
	private static function html2rgb($color)
	{
		return array(
		 hexdec(substr($color, 1, 2)),
		 hexdec(substr($color, 3, 2)),
		 hexdec(substr($color, 5, 2))
		);
	}

	/**
	 * Constructor
	 *
	 * @param string $opts Container for parameters to fetch from (POST/GET/ARGV/REQUEST/COOKIE/SESSION)
 	 * @param string $argv Array of parameters in form key=value. Needed when $opts == 'ARGV' only
	*/
	public function __construct($opts = 'POST', $argv = null)
	{
		switch(strtoupper($opts)) {
			case 'POST': $this->_OPTS = $_POST; break;
			case 'GET': $this->_OPTS = $_GET; break;
			case 'REQUEST': $this->_OPTS = $_REQUEST; break;
			case 'COOKIE': $this->_OPTS = $_COOKIE; break;
			case 'SESSION': $this->_OPTS = $_SESSION; break;

			case 'ARGV': // for command line routines
				foreach($argv as $value)
						if(list($key, $val) = explode('=', $value, 2)) {
							$this->_OPTS[$key] = $val;
						}
				break; // case 'ARGV'

			default: $this->_OPTS = $_POST;
		}
		
		// init JustWave parameters according to _OPTS
		$this->init();
		return $this;
	}

	/**
	 * Create waves' images from audio
	 *
	 * @param string $url Path or url to audio file (it may be not only an mp3 file)
	*/
	public function create($url)
	{
		$this->url = urldecode($url);
		
		$bn = basename($this->url);
		if(strlen($bn) < 4)
				$bn = 'Wave_' . hash('crc32', $url) . hash('crc32b', $url) . '.mp3';
		
		$this->audio = $bn;
		
		// find hash
		$this->key = md5($this->url . $this->width . $this->height .
			$this->waveColor . $this->progressColor . $this->backgroundColor);
		
		// find waves in cache
		if($this->checkCache())
			return;

		// find waves in output dir
		if($this->waveExists())
			return;
	
		// let's do some neat cleaning to prevent injection
		$mp3 = str_replace(array('"','&',"\\"), '', $url);

		// LOG::write('!! We have path to audio as ' . $mp3);
		
		// convert audio to WAV PCM using FFMPEG
		if(!($wav = $this->convert($mp3)))
			return;

		// create waves
		if(!($this->createWaves($wav))) {
			@unlink($wav);
			return;
		}
		// Delete temporary files.
		// Or may be we should use temp directory
		// in order not to hassle with this?
		@unlink($wav);

		$this->saveWaves();
		$this->cache();
		
		$this->status = 'ok';
		$this->message = 'New wave(s) created';
		
		return $this;
	}
	
	/**
	 * Return class output variables in JSON format
	 */
	public function json()
	{
		return json_encode(array(
			'status' => $this->status,
			'message' => $this->message,
			'duration' => $this->duration,
			'key' => $this->key,
			'name' => $this->audio,
			'waveurl' => $this->dataUrlWave,
			'progressurl' => $this->dataUrlProgress
		));
	}
	
} // JustWave class
