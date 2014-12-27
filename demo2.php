<!DOCTYPE html>
<html>

<head>
	<meta charset="utf-8">
	<title>JustWave Player / Demo 2</title>
	<link rel="shortcut icon" href="posters/favicon64.ico">

	<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
	<script src="js/justwave.player.js"></script>
	<link type="text/css" rel="stylesheet" href="css/justwave.player.css">

	<!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css">
	<!-- Latest compiled and minified JavaScript -->
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>
	<link type="text/css" rel="stylesheet" href="css/styles.css">
	
	<script src="js/demo2.js"></script>
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
            <li class="active"><a href="#">Demo 2</a></li>
						<li><a href="test.php">Test</a></li>
						<li><a href="license.html">License</a></li>
						<li><a href="download.html">Download</a></li>
						<li><a href="http://beotiger.com">beotiger.com</a></li>
          </ul>
        </div><!--/.nav-collapse -->
      </div>
   </nav>

  <div class="container">

		<h1>JustWave Player: Demo 2</h1>
		
				<h2>Choose a song</h2>
				<div id="songs-list">

				<ol class="songs list-group">
		<?php
		foreach (glob('media/*.mp3') as $file)
			echo "\t\t<li class=\"list-group-item\" data-src=\"$file\">" . basename($file) . "</li>\n";
		foreach (glob('media/*.ogg') as $file)
			echo "\t\t<li class=\"list-group-item\" data-src=\"$file\">" . basename($file) . "</li>\n";
		?>
					<li class="list-group-item" data-src="http://everwebcodebox.com/audio-player-master/audio/CosmicTraveler.mp3">http://everwebcodebox.com/audio-player-master/audio/CosmicTraveler.mp3</li>
					<li class="list-group-item" data-src="http://justwave.beotiger.com/media/Sweden%20Village.mp3">http://justwave.beotiger.com/media/Sweden%20Village.mp3</li>
				</ol>
				
				<div>
				<audio width="550" height="345" poster="posters/justwave1.jpg" autoplay></audio>
				</div>

			</div>
			
			<div class="clear alert alert-warning" class="alert alert-warning" role="alert">
			Using <strong>Holly Peers</strong> model star, <strong>Valeria Pooh</strong>, <strong>Victoria</strong> and <strong>Maria</strong> photos which are in a public domain.<br>
			Thank you very much from my heart.
			</div>
			
			
			<h2 class="clear">How to create a simple player with playlist in less than 25 lines of code?</h2>
			<h3>Tip: we need jQuery and JustWave player. That's all!<br>
			<span class="label label-info">Show me the answer</span>
			<code>
		$(document).ready( function() {<br>
		&nbsp;&nbsp; &nbsp;$.justwave();<br>
		&nbsp;&nbsp; &nbsp;// main routine for loading songs<br>
		&nbsp;&nbsp; &nbsp;$(&#39;.songs li[data-src]&#39;).click(function() {<br>
		&nbsp;&nbsp; &nbsp;&nbsp;&nbsp; &nbsp;$(&#39;audio&#39;)[0].src = $(this).attr(&#39;data-src&#39;); // name of an audio<br>
		&nbsp;&nbsp; &nbsp;&nbsp;&nbsp; &nbsp;$(this).addClass(&#39;active&#39;).siblings().removeClass(&#39;active&#39;);<br>
		&nbsp;&nbsp; &nbsp;});<br>
		&nbsp;&nbsp; &nbsp;// start first song<br>
		&nbsp;&nbsp; &nbsp;$(&#39;.songs li[data-src]&#39;).first().click();<br>
		&nbsp;&nbsp; &nbsp;// loop list<br>
		&nbsp;&nbsp; &nbsp;$(&#39;audio&#39;).first().on(&#39;ended&#39;, nextSong).on(&#39;error&#39;, nextSong);<br>
		});<br><br>

		function nextSong()<br>
		{<br>
		&nbsp;&nbsp; &nbsp;var next = $(&#39;.songs li[data-src].active&#39;).next();<br>
		&nbsp;&nbsp; &nbsp;if (!next.length) next = $(&#39;.songs li[data-src]&#39;).first();<br>
		&nbsp;&nbsp; &nbsp;next.addClass(&#39;active&#39;).siblings().removeClass(&#39;active&#39;);<br>
		&nbsp;&nbsp; &nbsp;$(&#39;audio&#39;)[0].src = next.attr(&#39;data-src&#39;);<br>
		}
		</code>
		</h3>

		<div class="well">
				(c) beotiger Andrey Tzar 2015 AD http://justwave.beotiger.com	https://github.com/beotiger	email: beotiger@gmail.com
		</div>	
		
	</div>

</body>
</html>
