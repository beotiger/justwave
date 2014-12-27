<!DOCTYPE html>
<html>

<head>
	<meta charset="utf-8">
	<title>JustWave Player / Demo 1</title>
	<link rel="shortcut icon" href="posters/favicon64.ico">

	<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
	<script src="js/justwave.player.js"></script>
	<link type="text/css" rel="stylesheet" href="css/justwave.player.css">

	<!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css">
	<!-- Optional theme -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap-theme.min.css">	
	
	<!-- Latest compiled and minified JavaScript -->
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>
	
	<link type="text/css" rel="stylesheet" href="css/styles.css">
	
	<script src="js/demo1.js"></script>
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
            <li class="active"><a href="#">Demo 1</a></li>
            <li><a href="demo2.php">Demo 2</a></li>
						<li><a href="test.php">Test</a></li>
						<li><a href="license.html">License</a></li>
						<li><a href="download.html">Download</a></li>
						<li><a href="http://beotiger.com">beotiger.com</a></li>
          </ul>
        </div><!--/.nav-collapse -->
      </div>
   </nav>

  <div class="container">
		<h1>JustWave Player: Demo 1</h1>
		
		<h2>Chained players</h2>
		
		<h3>Demis Russos - Goodbye My Love Goodbye (demo)<br>
		<audio data-chained="1" class="justwave" src="media/Demis Russos - Goodbye My Love Goodbye[demo].mp3" data-wave_color="#3232C8" data-prog_color="#3232C8" data-back_color="#C0C0C0" width="500" height="80" data-showname="0" data-buttoncolor="#337AB7"></audio>
		<span class="label label-primary">Show player's code</span>
		<code>
			&lt;audio data-chained=&quot;1&quot; class=&quot;justwave&quot; src=&quot;media/Demis Russos - Goodbye My Love Goodbye[demo].mp3&quot; data-wave_color=&quot;#3232C8&quot; data-prog_color=&quot;#3232C8&quot; data-back_color=&quot;#C0C0C0&quot; width=&quot;500&quot; height=&quot;80&quot; data-showname=&quot;0&quot;&gt;&lt;/audio&gt;
		</code>
		</h3>
		
		<h3>KirillTV - Always<br>
		<audio class="justwave" width="450" height="100" data-chained="1" data-wave_color="#FF871E" data-prog_color="#10FF1D" data-buttoncolor="#00A588">
			<source src="media/KirillTV - Always.mp3" type="audio/mpeg">
			<source src="media/KirillTV - Always.ogg" type="audio/ogg">
		</audio>
		<span class="label label-primary">Show player's code</span>
		<code>
			&lt;audio class=&quot;justwave&quot; width=&quot;450&quot; height=&quot;100&quot; data-chained=&quot;1&quot; data-wave_color=&quot;#FF871E&quot; data-prog_color=&quot;#10FF1D&quot; data-buttoncolor=&quot;#00A588&quot;&gt;<br>
				&nbsp;&nbsp;&lt;source src=&quot;media/KirillTV - Always.mp3&quot; type=&quot;audio/mpeg&quot;&gt;<br>
				&nbsp;&nbsp;&lt;source src=&quot;media/KirillTV - Always.ogg&quot; type=&quot;audio/ogg&quot;&gt;<br>
			&lt;/audio&gt;
		</code>
		</h3>
		<h3>ZX Spectrum - My Life<br>
		<audio data-chained="1" class="justwave" src="media/zx.spectrum_life.mp3" data-wave_color="#2D2D2D" data-prog_color="#CCCCCC" data-back_color="#444444" data-type="gif" width="550" height="80" data-buttonsize="70" data-buttoncolor="#111111" data-showtimes="0"></audio>
		<span class="label label-primary">Show player's code</span>
		<code>
			&lt;audio data-chained=&quot;1&quot; class=&quot;justwave&quot; src=&quot;media/zx.spectrum_life.mp3&quot; data-wave_color=&quot;#2D2D2D&quot; data-prog_color=&quot;#CCCCCC&quot; data-back_color=&quot;#444444&quot; data-type=&quot;gif&quot; width=&quot;550&quot; height=&quot;80&quot; data-buttonsize=&quot;70&quot; data-buttoncolor=&quot;#111111&quot; data-showtimes=&quot;0&quot;&gt;&lt;/audio&gt;
		</code>
		</h3>
		
		<h2>Not chained</h2>
		
		<h3>Queens of Dogtown - I Remember You (demo)</h3>
		<div class="alert alert-warning" role="alert">
		Uses image from <b>Californication</b> TV Series (<i>http://www.imdb.com/title/tt0904208/</i>) 
		</div>
		<audio preload="none" class="justwave" src="media/Queens of Dogtown - I Remember You[demo].mp3" data-wave_color="#E9E9E9" data-prog_color="#858585" width="600" height="369" poster="posters/hank.jpg"></audio>
		<h3><span class="label label-primary">Show player's code</span>
		<code>
			&lt;audio preload=&quot;none&quot; class=&quot;justwave&quot; src=&quot;media/Queens of Dogtown - I Remember You[demo].mp3&quot; data-wave_color=&quot;#E9E9E9&quot; data-prog_color=&quot;#858585&quot; width=&quot;600&quot; height=&quot;369&quot; poster=&quot;posters/hank.jpg&quot;&gt;&lt;/audio&gt;
		</code>
		</h3>

		<h3>Elvis Presley - Pretty woman (demo)</h3>
		<div class="alert alert-warning" role="alert">
		Uses image from <b>(Cancelled) The Request Box</b> double CD album (<i>http://www.elvisinfonet.com/cdnews_RequestBox.html</i>) 
		</div>
		<audio preload="none" data-buttoncolor="#FF530D" class="justwave" poster="posters/elvis.jpg" src="media/Elvis Presley - Pretty woman[demo].mp3" width="300" height="300"></audio>
		<h3><span class="label label-primary">Show player's code</span>
		<code>
			&lt;audio preload=&quot;none&quot; data-buttoncolor=&quot;#FF530D&quot; class=&quot;justwave&quot; poster=&quot;posters/elvis.jpg&quot; src=&quot;media/Elvis Presley - Pretty woman[demo].mp3&quot; width=&quot;300&quot; height=&quot;300&quot;&gt;&lt;/audio&gt;
		</code>
		</h3>

		<h2>Browser's native player</h2>
		<h3>Queens of Dogtown - I Remember You (demo)<br>
		<audio preload="none" controls src="media/Queens of Dogtown - I Remember You[demo].mp3"></audio>
		<span class="label label-primary">Show player's code</span>
		<code>
			&lt;audio preload=&quot;none&quot; controls src=&quot;media/Queens of Dogtown - I Remember You[demo].mp3&quot;&gt;&lt;/audio&gt;
		</code>
		</h3>
		
		<div class="well">
				(c) beotiger Andrey Tzar 2015 AD http://justwave.beotiger.com	https://github.com/beotiger	email: beotiger@gmail.com
		</div>	

	</div>	
</body>
</html>
