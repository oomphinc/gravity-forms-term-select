jQuery(document).ready(function($) {
	// set up the chosen fields
	$('.gform-term-select').each(function(){
		var $el = $(this)
		  , options = {}
		  , max
		;
		if (max = $el.data('max-selected')) {
			options.max_selected_options = max;
		}
		$el.chosen(options);
	});
});
