<!DOCTYPE html>
<html>

<head>
	<meta charset="utf-8">
	<title>JustWave Player / Test</title>
	<link rel="shortcut icon" href="posters/favicon64.ico">

	<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
	<script src="js/justwave.player.js"></script>
	<link type="text/css" rel="stylesheet" href="css/justwave.player.css">

	<!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css">
	<!-- Latest compiled and minified JavaScript -->
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>
	<link type="text/css" rel="stylesheet" href="css/styles.css">

	<script src="js/test-player.js"></script>
</head>

<body>
	<nav class="navbar navbar-inverse navbar-fixed-top">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
        <a class="navbar-brand" href="index.html">JustWave</a>
        </div>
        <div id="navbar" class="collapse navbar-collapse">
          <ul class="nav navbar-nav">
            <li><a href="player.html">JustWave Player</a></li>
            <li><a href="demo1.php">Demo 1</a></li>
            <li><a href="demo2.php">Demo 2</a></li>
						<li class="active"><a href="#">Test</a></li>
						<li><a href="license.html">License</a></li>
						<li><a href="download.html">Download</a></li>
						<li><a href="http://beotiger.com">beotiger.com</a></li>
          </ul>
        </div><!--/.nav-collapse -->
      </div>
   </nav>

  <div class="container">

		<h1>JustWave Player: Test</h1>
		<div id="songs-list">
		
			<h2>Choose a song:</h2>
			<ol class="songs list-group test">
		<?php
		foreach (glob('media/*.mp3') as $file)
			echo "\t\t<li class=\"list-group-item\" data-src=\"$file\">" . basename($file) . "</li>\n";
		foreach (glob('media/*.ogg') as $file)
			echo "\t\t<li class=\"list-group-item\" data-src=\"$file\">" . basename($file) . "</li>\n";
		?>
					<li class="list-group-item" data-src="http://everwebcodebox.com/audio-player-master/audio/CosmicTraveler.mp3">http://everwebcodebox.com/audio-player-master/audio/CosmicTraveler.mp3</li>
					<li class="list-group-item" data-src="http://justwave.beotiger.com/media/Sweden%20Village.mp3">http://justwave.beotiger.com/media/Sweden%20Village.mp3</li>
				<li class="list-group-item" data-src="NoSong_there.mp3">Fake song.mp3</li>
			</ol>

		<div id="test-player">
			<audio autoplay></audio>
		</div>

		</div>

		<table class="test"><tr>
		<td>
			<fieldset id="params">
				<legend>Options</legend>
				<table><tr>
				<tr><th>Width:</th><td><input id="width" value="500"></td></tr>
				<tr><th>Height:</th><td><input id="height" value="100"></td></tr>
				<tr><th>Wave color:</th><td><input id="wave_color" value="#909296"></td></tr>
				<tr><th>Progress color:</th><td><input id="prog_color" value="#FF530D"></td></tr>
				<tr><th>Background color:</th><td><input id="back_color" value=""></td></tr>
				<tr><th>Poster:</th><td><input id="poster" value=""></td></tr>

				<th>Mode:</th><td>
					<select id="mode">
						<option value="dataurl">dataurl</option>
						<option value="file">file</option>
					</select>
				</td></tr>
				
				<tr><th>Button color:</th><td><input id="buttoncolor" value="#A47655"></td></tr>
				<tr><th>Button size (in px):</th><td><input id="buttonsize" value=""></td></tr>
				<tr><th>Name size (in px):</th><td><input id="namesize" value="15"></td></tr>
				<tr><th>Show name:</th><td><input type="checkbox" id="showname" checked></td></tr>
				<tr><th>Show times:</th><td><input type="checkbox" id="showtimes" checked></td></tr>
				
				<tr><th>Force:</th><td><input type="checkbox" id="force"></td></tr>
				<tr><th>No cache:</th><td><input type="checkbox" id="nocache"></td></tr>
				<tr><th>Two pass:</th><td><input type="checkbox" id="twopass"></td></tr>
				<tr><th>No waves*:</th><td><input type="checkbox" id="nowaves"></td></tr>
			</table>
			</fieldset>
		</td>
		
		<td>
			<fieldset id="demo3-datasent">
				<legend>Data sent</legend>
				<table id="datasent">
				</table>
			</fieldset>
		</td>
		
		<td>
		<fieldset id="output">
			<legend>Response</legend>
			
			<table>
				<tr><th>Total size of response</th><td><input class="totalsize"></td></tr>
				<tr><th>status</th><td><input class="status"></td></tr>
				<tr><th>message</th><td><input class="message"></td></tr>
				<tr><th>name</th><td><input class="name"></td></tr>
				<tr><th>key</th><td><input class="key"></td></tr>
				<tr><th>duration</th><td><input class="duration"></td></tr>
				<tr><th>waveurl</th><td><textarea class="waveurl"></textarea></td></tr>
				<tr><th>progressurl</th><td><textarea class="progressurl"></textarea></td></tr>
			</table>
		</fieldset>
		
		</td>
		</tr></table>
		
		<h3>Edit options and choose a song from a list above.</h3>
		
		<div class="well">
				(c) beotiger Andrey Tzar 2015 AD http://justwave.beotiger.com	https://github.com/beotiger	email: beotiger@gmail.com
		</div>	
		
	</div>
</body>
</html>
