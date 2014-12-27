// create simple player with playlist in 25 lines of code
$(document).ready( function() {
	$.justwave();
	// main routine for loading songs
	$('.songs li[data-src]').click(function() {
		// attach random poster
		$('audio')[0].opts.poster = 'posters/justwave' + parseInt(Math.random() * 7 + 1) + '.jpg';
		$('audio')[0].src = $(this).attr('data-src'); // name of an audio
		$(this).addClass('active').siblings().removeClass('active');
	});
	// start first song
	$('.songs li[data-src]').first().click();
	// loop list
	$('audio').first().on('ended', nextSong).on('error', nextSong);
	
	$('span.label').click(function() {
		$(this).next().toggle();
	}).next().hide();
});

function nextSong()
{
	var next = $('.songs li[data-src].active').next();
	if (!next.length) next = $('.songs li[data-src]').first();
	next.addClass('active').siblings().removeClass('active');
	// attach random poster
	$('audio')[0].opts.poster = 'posters/justwave' + parseInt(Math.random() * 7 + 1) + '.jpg';
	$('audio')[0].src = next.attr('data-src');
}
