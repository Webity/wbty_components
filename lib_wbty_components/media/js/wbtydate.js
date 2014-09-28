jQuery(document).ready(function($) {
	$('select.wbtydate').on('change', function() {
		var Container = $(this).parent('.wbtydate-container');
		var year = Container.find('.years').val();
		var month = Container.find('.month').val();
		var day = Container.find('.day').val();
		
		if (typeof year === 'number' && typeof month === 'number' && typeof day === 'number') Container.find('.wbtydate-hidden').val(year + '-' + month + '-' + day);
	});
});