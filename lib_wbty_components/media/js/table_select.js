jQuery(document).ready(function($) { // when document has loaded
	$('.subtable_control').change(function() {
		// make sure that each sub table is set up with one item and no controls
		$('.subtables').each(function() {
			if ($(this).find('a').length==0) {
				var t = setTimeout(function() {
					$('.subtable_control').trigger('change');
				}, 500);
			}
			if ($(this).find('a').length==1) {
				$(this).find('a').trigger('click').hide();
			}
			$(this).find('a').hide();
		});
		$('.subtables').hide();
		
		$('.subtable_control').each(function() {
			$('.subtable-'+$(this).val()).show();
		});
	});
	$('.subtable_control').trigger('change');
});