jQuery(function ($) {
	$( ".ytsl-click_div" ).click(function() {
		$(this).replaceWith( $(this).data('iframe') );
	});
});
