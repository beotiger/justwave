		// Note: we will wrapper audio with justwave class only
		// so the last one without this class but with controls will be untouched

		// If we call $.justwave() without parameters
		// all audio elements will be injected by justwave player
		$(document).ready( function() {
			$.justwave('justwave');
			
			$('span.label').click(function() {
				$(this).next().toggle();
			}).next().hide();
			
		});

