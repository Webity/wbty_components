jQuery(document).ready(function($) {
	window.depselects = new Array;
	$('select[data-dependent-field]').each(function() {
    	field = $(this).attr('data-dependent-field');
        window.depselects[$(this).attr('id')] = $(this).clone();
        $('[name='+field+']').on('change', $.proxy(function(e) {
        	$(this).find('option').remove();
            console.log(window.depselects[$(this).attr('id')].find('option[data-dependent-value='+$(e.currentTarget).val()+']'));
            $(this).append(window.depselects[$(this).attr('id')].find('option[data-dependent-value='+$(e.currentTarget).val()+']').clone());
        }, this));
        $('[name='+field+']').trigger('change');
    });
});