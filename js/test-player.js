$(document).ready( function() {
	$.justwave();
	
	// main routine for loading songs
	$('.songs li[data-src]').click(function() {
		var src = $(this).attr('data-src'),
				au = $('audio')[0];
		
		var params = {};
	
		$('#params input, #params select').not('[type=checkbox]').each(function() {
			params[$(this).attr('id')] = $(this).val();
		});
		
		$('#params input[type=checkbox]').each(function() {
			if($(this).is(':checked'))
				params[$(this).attr('id')] = 1;
			else au.opts[$(this).attr('id')] = undefined;
		});
		
		$.extend(au.opts, params);
		
		// clear output table
		$('#output').find('input, textarea').val('');
		
		// and fill out data sent table
		var v, p, s = '';
		delete au.opts.audio; // it will be set by justwave.player from audio.src

		for(p in au.opts) {
			v = au.opts[p] || '';
			s += '<tr><th>' + p + '</th><td><input value="' +
					htmlspecialchars(v) + '"></td></tr>';
		}
		$('#datasent').html(s);
		
		au.src = src; // name of an audio
		$(this).addClass('active').siblings().removeClass('active');
	});

	// global handler for fetching data from AJAX requests
	$(document).ajaxSuccess(function(e, xhr, settings) {
		data = $.parseJSON(xhr.responseText);
		if($.isPlainObject(data))
			for(p in data)
				$('#output').find('.' + p).val(data[p]);
		// total size of fetched data
		$('#output').find('.totalsize').val(xhr.responseText.length);
	});

	$.ajaxSetup({
		global: true
	});
	
});

function htmlspecialchars(text) {
	if(typeof text == 'string')
  return text
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;')
	  .replace(/\x0A/g, ' ')
	  .replace(/\x0D/g, ' ');
	else return text;
}
