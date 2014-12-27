/** ******************************************************************************************************

	Proof-of-concept: create player using JustWave.class.php
	
	jQuery plugin for creating waveform audio players.
	
	(c) beotiger Dec 2014 AD
		http://justwave.beotiger.com 
		https://github.com/beotiger 
		email: beotiger@gmail.com
	
	Usage:	$.justwave([ [options,] className ]);
	
	options - optional map of options, which can be (shown default values):
			ajax: 'justwave.ajax.php' - server side script for AJAX requests
			width: 500 - width of player
			height: 100 - height of player
			poster: null - poster as background
			wave_color: '#909296' - color of foreground wave
			prog_color: '#FF530D' - color of background wave (progress wave)
			back_color: '' - color of background, if not set or an empty string - transparent then.
											 Note: you can set any picture through `poster` option or attribute of DOM audio element
			mode: 'dataurl' - mode: file|dataurl
			wavedir: 'waves/' - directory in filesystem for storing waveform images
												 Note 1: This directory is also used 
												 	for temporary files during convertion process
												 	and should be writable for php (apache?) user.
												 Note 2: On Windows' systems always use '/' in path instead of '\'.
												 Note 3: If you intend to use only dataurl mode
												 you can set it to '.' (current server's script path) so no extra directories
												 will be created. Do not forget about writableness of the directory.
			buttoncolor: '#A47655' - color of play/pause button background, inners are always white,
			buttonsize: 0 - size of play/pause in pixels, if 0 - its size is 50% of the height of the player,
										but not more than 88 pixels
			showname: 1 - display name of the audio when playback started and while mouse hovering (1 - yes, 0 - no)
			namesize: 15 - size of the name of the audio, in pixels
			showtimes: 1 - show time leds on the player surface (1 - yes, 0 - no)
			nowaves: 0	- 0/1, if 1 - do not generate waves, show only backgrounds in player
									Caution: experimental feature, use with care
		
		Extra options:
			force - do not seek for waveform images in file system
			nocache - do not use caching mechanism (why?)
			twopass - use two pass converting for normalization
								(if waves are in cache or in file system and you do not use nocache/force options
								this option does not take effect then)
			chained - chain players so that when one starts playing all others are paused
					Note: there may be chained and not chained justwave players on one page,
						and chaining mode applies to chained players only.
					You can use `data-chained` attribute for distinct audio elements that should be chained.
					Default behavior: not chained, all players can play simultaneously
			
		If extra option is not set, default behavior will be used,
		which means:
			If waves are in cache they will be fetched from there
			If waves were created previously in file system (and are in a `wavedir` directory)
				they will be fetched from there then.
			Otherwise audio will be converted to .wav format temporally in a `wavedir` directory
				and new waveform images	will be created.
			In `file` mode these images (or one image	if `wave_color` == `prog_color`) 
			will be stored in `wavedir` directory and we get links to them
			in `srcwave` and `srcprogress` JSON-parameters, otherwise we get
			`waveurl` and optional `progressurl` JSON-parameters
			and can use them as data-url sources for our images.
			
			If audio src is a url it will be temporally downloaded to a `wavedir` directory
			and converted	as usual audio and then removed.
		
	All options can be set through `data-` attributes of audio element,	for example: 
	
		<audio data-chained="1" data-mode="file" src="LeoRudenko_Dest.mp3" data-wave_color="#3F87BE" data-prog_color="#2A75AD" data-buttoncolor="#00A588" data-showname="0" width="350" height="100">
		</audio>
		
	Notice that you can use width, height and poster attributes without data-prefix, for they are legal for
	HTML MediaElement. Yes, you can have width, height and poster for your audio elements with JustWave Player!
	But beware of HTML5 validator be confused.
	
	className - class name to filter injected players.
		If not set all audio elements will be injected by the JustWave player.

	We respect preload, autoplay and loop attributes as much as they are respected by the browser.
	But do not use controls attribute in audio element that meant to be justwaved.

	Note: any of this option can be changed dynamically through audio.opts object
				before loading new src in audio element	where audio is an audio JavaScript DOM element.
				E.g.: 
					var audio = $('audio')[0]; 
					audio.opts.width = 400;
					audio.opts.poster = 'new-poster.png';
					audio.opts.buttonsize = 30;
					$(audio).attr('src', 'new_song.mp3');

 ********************************************************************************************************* */
 
(function($) {

	$.justwave = function(options, classOnly)
	{
				// player markup
			var html = '\
<div class="justwave_wrapper">\
	<img class="justwave_wave" src="" alt="" ondragstart="return false"/>\
	<div class="justwave_playhead">\
		<img class="justwave_progress" src="" alt="" ondragstart="return false"/>\
		<span class="justwave_curpos">00:00</span>\
	</div>\
	<span class="justwave_duration">00:00</span>\
	<span class="justwave_curfocus">00:00</span>\
	<span class="justwave_songname"></span>\
	<button class="justwave_playpause">\
		<svg width="100%" height="100%" viewBox="0 0 44 44" xmlns="http://www.w3.org/2000/svg">\
		<circle r="21.5" cy="21.5" cx="21.5" stroke="#30C000" fill="#30C000"/>\
		<path d="M31,21.5L17,33l2.5-11.5L17,10L31,21.5z" fill="#FFF" class="justwave_play"/>\
		<g class="justwave_pause" fill="#FFF">\
			<rect height="19" width="5" y="12" x="15"/><rect height="19" width="5" y="12" x="23"/>\
		</g>\
		</svg>\
	</button>\
</div>';

		var els = $('audio');
		// reduce elements' set if classOnly is defined and is a string
		if(typeof options == 'string')
			classOnly = options;

		if(typeof classOnly == 'string')
			els = els.filter('.' + classOnly);
		
		els.each(function() {
			var p = this, // audio DOM element
					$p = $(this);	// jquery element
			// default options, see descriptions above
				p.opts = {
					ajax: 'justwave.ajax.php',	// path to server ajax script generating waveforms
					width: 500,	// width of player
					height: 100,	// height of player
					wave_color: '#909296',	// main color of waveform
					prog_color: '#FF530D',	// progress color of waveform
					back_color: '',	// background color of player, blank for transparent
					buttoncolor: '#A47655',	// color of play/pause button
					buttonsize: 0,	// size of play/pause button in pixels
					showname: 1,	// show audio name
					namesize: 15,	// font-size of audio name in pixels
					showtimes: 1,	// show time leds on the player surface
					nowaves: 0	// if 1 - do not generate waves, show only backgrounds in player
				};

			if($.isPlainObject(options))
				$.extend(p.opts, options);
			
			// override default and common options
			// with current element's attributes
			if($p.attr('width'))
				p.opts.width = $p.attr('width');
			if($p.attr('height'))
				p.opts.height = $p.attr('height');
			if($p.attr('poster'))
				p.opts.poster = $p.attr('poster');
			
			// from http://stackoverflow.com/questions/2048720/get-all-attributes-from-a-html-element-with-javascript-jquery
			// answered by Roland Bouman, thanks a lot
			// Note: data-width, data-height overlap their counterparts width, height
			for (var attr, i = 0, attrs = p.attributes, n = attrs.length; i < n; i++) {
					attr = attrs[i];
					if(attr.nodeName.substring(0, 5) == 'data-')
						p.opts[attr.nodeName.substring(5)] = attr.nodeValue;
			}
			
			// chain player?
			if(p.opts.chained)
				$p.addClass('justwave_chained');
			
			// create simple but elegant wave player IMHO
			$p.after(html)
			.on('loadedmetadata', function(e) {
			//	meta data of audio is loaded
				var p = e.target,
						song = $(p).next();
						
				// clear waveforms
				clearWaves(p);
				// set size and color of play/pause button
				// and show play button
				normalizePlayPauseButton(p);

				p.xduration = p.duration;
				song.find('.justwave_duration').text(toMinSec(p.xduration));
				if(!!+p.opts.showtimes)
					song.find('.justwave_duration, .justwave_curpos').show();
				else
					song.find('.justwave_duration, .justwave_curpos').hide();

				// fetch audio path from attribute not from property
				// if it's available for property holds url to audio
				if($(p).attr('src'))
					p.opts.audio = $(p).attr('src');
				else
					p.opts.audio = p.currentSrc;
					
				var songName = song.find('.justwave_songname').css('font-size', p.opts.namesize + 'px')
					.text(decodeURIComponent(p.opts.audio.replace(/.+[\\\/]/, '')));
				// show the song name for a while
				if(!!+p.opts.showname)
					songnameShow(songName, true);
				
				if(!+p.opts.nowaves)
				 $.ajax(p.opts.ajax, 
					{ dataType: 'json',	type: 'POST',	data: p.opts }
				).done(function(data) 
				{
					// find main container and image waves
					var song = $(p).next(),
							waveImg = song.find('.justwave_wave'),
							progImg = song.find('.justwave_progress');
					
					if(data.status == 'ok') {	// got waves
						waveImg.width(p.opts.width);
						progImg.width(p.opts.width);
					
						// remove background
						song.css('background', '');
						song.find('.justwave_playhead').css('background', '');
						if(p.opts.poster)
							song.css('background-image', 'url(' + p.opts.poster + ')');
						
						// image sources are waveforms
						waveImg.attr('src', data.waveurl);
						if(p.opts.wave_color == p.opts.prog_color)
							progImg.attr('src', data.waveurl);
						else
							progImg.attr('src', data.progressurl);

					}
					// importing duration from server
					// for some browsers may fail
					p.xduration = parseFloat(data.duration);
					if(!p.xduration)
						p.xduration = p.duration;
					song.find('.justwave_duration').text(toMinSec(p.xduration));
				}); // done
			}) // loadedmetadata
			.on('playing', function(e) {
				var p = e.target,
						song = $(p).next();
					// play buttons
				song.find('.justwave_pause').show();
				song.find('.justwave_play').hide();
				
				// chain players together
				if(p.opts.chained)
					$('audio.justwave_chained').each(function() {
						if(this != p) this.pause();
					});
			})
			.on('pause', function(e) {
				var song = $(e.target).next();
					// play buttons
				song.find('.justwave_pause').hide();
				song.find('.justwave_play').show();
			})
			.on('error', function(e) {
				var p = e.target,
						song = $(p).next();

				clearWaves(p);
				// disable play/pause buttons
				song.find('.justwave_pause, .justwave_play').hide();
				song.find('.justwave_playpause').prop('disabled', true);
				// display the base name of failed audio
				song.find('.justwave_songname').text($(p).attr('src').replace(/.+[\\\/]/, ''));
				song.find('.justwave_duration').text('00:00');
				p.xduration = 0;
				song.find('.justwave_duration, .justwave_curpos, .justwave_curfocus').hide();
			})
			.on('timeupdate', function(e) {
				updatePlayhead(this);
			})
			.on('ended', function(e) {
				this.pause();
			});
			
			// clear waveforms images
			clearWaves(p);
			// set size and color of play/pause button
			// and show play button
			normalizePlayPauseButton(p);

			// additional callbacks for scrubber time update
			// and play/pause buttons
			var song = $p.next();
			// move playhead
			song.click(function(e) {
				e.preventDefault();
				var p = $(this).prev()[0],
						scrl = findScroll(this),
						mouseX = e.pageX - scrl.scrLeft - leftPos(this);
						
				p.currentTime = mouseX * p.xduration / this.offsetWidth;
				// update cursor
				updatePlayhead(p);
			}).mousemove(function(e) {
					var scrl = findScroll(this),
							mouseX = e.pageX - scrl.scrLeft - leftPos(this),
							mouseY = e.pageY - scrl.scrTop - topPos(this),
							song = this,
							p = $(this).prev()[0];
							
					$(song).find('.justwave_curfocus').text(toMinSec(mouseX / this.offsetWidth * p.xduration))
					.css({ 	top: mouseY - 7 + 'px', 
									left: mouseX + 7 + 'px'});
					// show song name for a while while moving cursor over player
					if(!!+p.opts.showname)
						songnameShow($(song).find('.justwave_songname'));
			}).mouseleave(function() {
				$(this).find('.justwave_curfocus').hide();
				$(this).find('.justwave_playpause').fadeOut(1000);
			}).mouseenter(function() {
				var p = $(this).prev()[0]
				$(this).find('.justwave_playpause').stop(false,true).fadeIn(500);
				if(!!+p.opts.showtimes)
					$(this).find('.justwave_curfocus').show();
			});
			
			// bind play/pause button
			song.find('.justwave_playpause').click(function(e) {
				e.stopPropagation();
				e.preventDefault();
				// target an audio element
				var song = $(this).parent(),
						p = song.prev()[0];

				// rewind if ended
				if(p.ended)
					p.currentTime = 0;
				if(p.paused)
					p.play();
				else
					p.pause();
				// song.find('.justwave_pause, .justwave_play').toggle();
			})
			.mouseenter(function(e) {
				// e.stopPropagation();
				$(this).siblings('.justwave_curfocus').hide();
			})
			.mouseleave(function(e) {
				// e.stopPropagation();
				// audio element
				var p = $(this).parent().prev()[0];
				if(!!+p.opts.showtimes)
					$(this).siblings('.justwave_curfocus').show();
			});
		}); // each
	
	}; // $.justwave()

	/**
		Helper functions
	 */
		
	// Move cursor and show current time of a track
	// p - audio element
	var updatePlayhead = function(p)
	{
		var newWidth = p.currentTime / p.xduration * 100,
			song = $(p).next();
		if(newWidth <= 100.10)
			song.find('.justwave_playhead').width(newWidth + '%'); //$('#song').width());
		song.find('.justwave_curpos').text(toMinSec(p.currentTime));
	},

	// Convert time parameter which is in seconds to min:sec 
	// with leading zeros like 03:09 for example
	toMinSec = function(time)
	{
		var min = Math.floor(time / 60), // minutes
				sec = Math.floor(time % 60); // seconds
		if(isNaN(time))
			return '00:00';
		return (min < 10 ? '0' : '') + min + ':' + (sec < 10 ? '0' : '') + sec;
	},
	
	// Show name of the song for a while (~ 7s?)
	// el - jquery object of div, containing audio name
	songnameShow = function(el, force)
	{
		if(force) el.stop(true, true).hide();
		
		if(!el.is(':visible'))
			el.fadeIn(300, function() {
				el.fadeOut(7000, 'swing');
			});
	},
	
	// find left position of element
	leftPos = function(elem)
	{
		var curleft = 0;
		if (elem.offsetParent) {
			do { curleft += elem.offsetLeft; } while (elem = elem.offsetParent);
		}
		return curleft;
	},
	
	// find top position of element
	topPos = function(elem)
	{
		var curtop = 0;
		if (elem.offsetParent) {
			do { curtop += elem.offsetTop; } while (elem = elem.offsetParent);
		}
		return curtop;
	},
	// find scroll positions when in fixed/absolute container
	findScroll = function(elem)
	{
		var scrLeft = 0,
				scrTop = 0,
				offEl = $(elem).offsetParent().css('position').toLowerCase();
		
		// respect positioned elements
		if(offEl == 'fixed' /* || offEl == 'absolute' */) {
			scrLeft = $(window).scrollLeft();
			scrTop = $(window).scrollTop();
		}
			
		return {
			scrLeft: scrLeft,
			scrTop: scrTop
		};
	},
	
	// clear waveforms by backgroundand set size of the palyer
	// p - audio element
	clearWaves = function(p)
	{
		// find main container and image waves
		var song = $(p).next();
		// images
		song.find('.justwave_wave').width(0).attr('src','');
		song.find('.justwave_progress').width(0).attr('src','');
		// wrapper
		song.width(p.opts.width).height(p.opts.height)
			.css('background', p.opts.wave_color);
		if(p.opts.poster)
			song.css('background-image', 'url(' + p.opts.poster + ')');
		song.find('.justwave_playhead').css('background', p.opts.prog_color);
	},
	
	// size of play/pause button is flexible
	// height of player ought to be lesser than width
	normalizePlayPauseButton = function(p)
	{
		var song = $(p).next(),
				ppSize;
				
		if(p.opts.buttonsize)
			ppSize = p.opts.buttonsize;
		else {
			ppSize = parseInt(p.opts.height * 0.50);
			if(ppSize > 88)
				ppSize = 88;
			if(ppSize < 33)
				ppSize = p.opts.height - 1;
		}
		song.find('.justwave_playpause')
			.width(ppSize).height(ppSize).prop('disabled', false)
			.find('circle').attr({ stroke: p.opts.buttoncolor, fill: p.opts.buttoncolor });

		// we can start playing this audio
		song.find('.justwave_pause').hide();
		song.find('.justwave_play').show();
	};
	
})(jQuery);
