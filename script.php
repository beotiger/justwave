<?php

if(!isset($_GET['mode'])) {
	header('Location: script.php?mode=file&wave_color=F00&prog_color=F00&back_color=00AC00');
	die;
}

require_once 'JustWave.class.php';

// we accept parameters as GET data
// e.g.: script.php?mode=file&wave_color=F00&prog_color=F00&back_color=00AC00
$justwave = new JustWave('GET');
// create waveform image(s)
$justwave->create('media/We Wish You.mp3');

if($justwave->status == 'ok') {
  echo 'Duration of We Wish You.mp3 = ' . $justwave->duration . '<br>';
  echo 'See waveform image under the link: <a href="' . $justwave->dataUrlWave . '">waveform</a>';
}
else
  echo 'Failed! Message = ' . $justwave->message;
