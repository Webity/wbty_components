jQuery(document).ready(function($) { // when document has loaded

	if (!window.wbty_modal_added) {
		var Relations = new Object;
		var table_id = $('#jform_id').val();
		var option = $('#option').val();
		var form_name = $('#form_name').val();
		
		$('.relationalTable').each( function() {
			Relations[$(this).attr('id')] = new Object;
		});
		
		if ($('#jform_id').val()) {
			$('input.set2id').val( $('#jform_id').val() );
		} else {
			$('input.set2id').val( '|set2id|' );
		}
			
		$(document).on( 'click', 'a.link-add', function(event) { // when you click the add link
			event.preventDefault();
			$(this).before('<li><img src="' + $(this).attr('data-loader-url') + '" /></li>');
			var $target = $(this).closest('ul').children('li').last();
			
			$.ajax({
				type: "POST",
				url: cachebust($(this).attr('href')),
				data: {subtable_key: $(this).closest('ul').children('li').length},
				success: function (resp) {
					$target.append($(resp));
					
					$target.find('link').each(function (i, e) {
						if ($(e).attr('rel')=='stylesheet') {
							$('head').append($(e));
						}
					});
					
					$target.html($target.find('fieldset'));
					$target.find('fieldset').append('<div class="clr"></div><a href="#" class="link-remove btn btn-warning btn-small">Remove item</a>');
					window.fireEvent('domready');
				}
			  });
		});
		
		
		$(document).on('click', 'a.link-remove', function(event) { // similar to the previous, when you click remove link
			$(this).closest('li').html('').hide();
			event.preventDefault();
		});
		
		$(document).on('click', 'a[data-toggle=modal]', function (e) {
			e.preventDefault();
			var target = $(this).attr('data-target');
			$('#'+target+' .form-content').html('<img src=\"' + $(this).attr('data-loading') + '\" />');
			
			$('#'+target+'Save').val($(this).attr('data-save'));
			$('#'+target+' .modal-name').text($(this).attr('data-name'));
			document.wbty_callback = $(this).siblings('select');
			$.ajax({
				url: cachebust($(this).attr('href')),
				success: function (resp) {
					$('#'+target+' .form-content').append($(resp));
					
					$('#'+target+' .form-content').find('link').each(function (i, e) {
						if ($(e).attr('rel')=='stylesheet') {
							$('head').append($(e));
						}
					});
					
					$('#'+target+' .form-content').html($('#'+target+' .form-content').find('form'));
					$('#'+target+' .form-content form .toolbar-list').hide();
					window.fireEvent('domready');
				}
			  });
			$('#'+target).modal('show').css({
				width: '750px',
				'margin-left': function () {
					return -($(this).width() / 2);
				}
			});
		});
		
		$(document).on('click', '.modal-save', function () {
			form = $('#myModal form');
			form.find('[name=task]').val($('#myModalSave').val());
			data = form.serialize();
			window.value = form.find('.default_col').val();
			$.post(form.attr('action'), data, function(data) {
				if (data != 'error') {
					 document.wbty_callback.append($("<option/>", {
							value: data,
							text: window.value,
							selected: 'selected'
						}));
				}
			});
		});
		
		$('body').append($('<div class="modal hide fade" id="myModal"><input type="hidden" name="myModalSave" id="myModalSave" value="" /><input type="hidden" name="myModalCallback" id="myModalCallback" value="" /><div class="modal-header"><a class="close" data-dismiss="modal">×</a><h3 class="modal-name">Add</h3></div><div class="modal-body"><div class="row-fluid"><div class="span12 form-content"></div></div></div><div class="modal-footer"><a href="#" class="btn btn-primary" data-dismiss="modal">Cancel</a><a href="#" data-dismiss="modal" class="btn btn-success modal-save">Save & Close</a></div></div>'));
		
		window.wbty_modal_added = 1;
	}
});

function cachebust(url) {
	if (url.indexOf('?')!==-1) {
		url = url + '&t=' + new Date;
	} else {
		url = url + '?t=' + new Date;
	}
	return url;
}