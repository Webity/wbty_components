if (!check4Copy) {
	var check4Copy = function() {
		$cloned = jQuery('.addCloned');
		
		if ($cloned.length == 0) { return; }
		
		$cloned.each(function(i, e) {
			var index = i;
			jQuery(this).find('input, select, textarea').each(function(i, e) {
				new_str = '[' + index + ']';
				if (jQuery(e).attr('name') === undefined) {
					return;
				}
				position = jQuery(e).attr('name').lastIndexOf('[');
				if (position > -1) {
					output = [jQuery(e).attr('name').slice(0, position), new_str, jQuery(e).attr('name').slice(position)].join('');
					jQuery(e).attr('name', output);
				} else {
					jQuery(e).attr('name', e.attr('name') + new_str);
				}
			});
		});
		
		return true;
	}
}

function ajaxsave(url, data, context) {
	jQuery.ajax({
		url: url,
		data: data,
		dataType: 'json',
		type: 'POST',
		context: context,
		success: function(msg) {
			if (msg.error) {
				alert(msg.error);
			} else {
				jQuery('.subtables fieldset > div').show();
				if (msg.id) {
					jQuery(this).closest('fieldset').find('.id').val(msg.id);
				}
			}
			if (msg.token) {
				window.token = msg.token;
			}

			if (msg.data && jQuery(this).attr('data-update')) {
				if (jQuery(this).attr('data-update') == 'append') {
					//console.log(jQuery('#' + jQuery(this).attr('data-fieldset') + '-published'));
					jQuery('#' + jQuery(this).attr('data-fieldset') + '-published').append('<fieldset id="'+ jQuery(this).attr('data-fieldset') + msg.id + '" />');
					jQuery('#' + jQuery(this).attr('data-fieldset') + msg.id).html(msg.data);
				} else {
					data = jQuery(msg.data);
					if (data.attr('id') == jQuery(this).attr('data-update')) {
						insert = data.html();
					} else {
						insert = data.find('#' + jQuery(this).attr('data-update')).html();
					}

					jQuery('#' + jQuery(this).attr('data-update')).html(insert);
				}
			}
			jQuery(this).remove();
		},
		error: function (x, status, error) {
			console.log(status + '-' + error);
			console.log(x);
		}
	})
}

function maintain_checkout() {
	if (parseInt(jQuery('.id').first().val()) > 0) {
		url = window.juri_base + 'index.php';
		task = jQuery('form fieldset').first().attr('data-controller') +'.ajax_checkout';
		id = jQuery('.id').first().val();
		jQuery.ajax({
			url: url,
			dataType: 'json',
			data: {
				option: jQuery('#option').val(),
				task: task,
				id: id
			},
			type: 'POST',
			success: function(msg) {
				if (msg.error) {
					alert(msg.error);
				} else {
					if (msg.id != jQuery('.id').first().val()) {
						alert('Checkout seems to have failed.');
					}
				}
				if (msg.token) {
					window.token = msg.token;
				}
			},
			error: function (x, status, error) {
				console.log(status + '-' + error);
				console.log(x);
			}
		});
	}
	setTimeout(function() {maintain_checkout();}, 60000);
}
setTimeout(function() {maintain_checkout();}, 30000);


jQuery(document).ready(function($) {
	$('<div id="wbtyModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="wbtyModalLabel" aria-hidden="true">  <div class="modal-header">    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>    <h3 id="wbtyModalLabel">Loading</h3>  </div>  <div class="modal-body">    <p></p>  </div>  <div class="modal-footer">    <button class="btn btn-primary save-btn" data-dismiss="modal" aria-hidden="true">Save</button>    <button class="btn btn-primary" data-dismiss="modal" aria-hidden="true">Cancel</button>  </div></div>').appendTo('body');
	var $editbox = $('<div class="editbox">  <div class="edit-header">    <button type="button" class="close" data-dismiss="editbox">×</button>    <h3>Loading</h3>  </div>  <div class="edit-body">    <p></p>  </div>  <div class="edit-footer">    <button class="btn btn-primary edit-save-btn" aria-hidden="true">Save</button>    <button class="btn btn-primary" data-dismiss="editbox" aria-hidden="true">Cancel</button>  </div></div>');
	
	var $buttons = $('<div class="edit-buttons"><button class="btn btn-primary edit-save-btn" aria-hidden="true">Save</button>    <button class="btn edit-reset-btn" data-dismiss="editbox" aria-hidden="true">Reset</button></div>');
	
	var $inlinebuttons = $('<div class="edit-buttons"><button class="btn btn-primary inline-edit-save-btn" aria-hidden="true">Save</button>    <button class="btn inline-edit-reset-btn" data-dismiss="editbox" aria-hidden="true">Reset</button></div>');
	
	var $addAnother = $('<button class="btn edit-add-btn" aria-hidden="true">Add Another</button>');
	var $copyBtn = $('<button class="btn edit-copy-btn" aria-hidden="true">Copy</button>');
	
	var $modify = $('<div class="modify-buttons" style="float:right;"><button class="btn btn-primary edit-modify-btn" aria-hidden="true">Modify</button></div>');
	
	$('.sortable').sortable();
	$('.tabs').tabs();

	$('body').on('click', '.btn', function(e) {
		if (!$(this).hasClass('allowDefault')) {
			e.preventDefault();
		}
	});

	if (parseInt($('.parentform .id').val()) < 1) {
		$('.subtables fieldset > div').hide();
	}

	$('#toolbar-save button, #toolbar-save a').attr('onclick', '');
	$('#toolbar-save button, #toolbar-save a').click(function(e) {
		e.preventDefault();

		// make sure to save editors
		$('.wfEditor').each(function() {
			$(this).html(tinyMCE.get($(this).attr('id')).save());
		});

		// save any inline edit forms
		$('.inline-edit-save-btn').trigger('click');

		// save primary
		data = $('fieldset.parentform').serializeArray();
		token = new Object;
		token.name = ((typeof window.token === 'undefined') ? window.token : $('fieldset.parentform').closest('form').find('input').last().attr('name'));
		token.value = 1;
		data.push(token);

		$(this).after('<img src="'+window.juri_root+'/media/wbty_components/img/load.gif" class="loading" />');
		task = $('fieldset.parentform').attr('data-task');
		url = 'index.php?option=' + $('#option').val() + '&task='+task;

		ajaxsave(url, data, $(this).parent().find('.loading'));
		console.log(data);
		return false;
	});

	$('.save-primary').click(function() {
		// make sure to save editors
		$('.wfEditor').each(function() {
			$(this).html(tinyMCE.get($(this).attr('id')).save());
		});

		data = $(this).closest('fieldset').serializeArray();
		token = new Object;
		token.name = ((typeof window.token === 'undefined') ? window.token : $(this).closest('form').find('input').last().attr('name'));
		token.value = 1;
		data.push(token);

		$(this).after('<img src="'+window.juri_root+'/media/wbty_components/img/load.gif" class="loading" />');
		task = $(this).closest('fieldset').attr('data-task');
		url = 'index.php?option=' + $('#option').val() + '&task='+task;

		ajaxsave(url, data, $(this).parent().find('.loading'));

	});
	
	$('.section-edit fieldset')
		.find('.edit-form').hide().end()
		.find('.edit-values').append($modify).hide().end()
		.first().find('.edit-form').show().append($buttons.find('.edit-save-btn').text('Continue').end());
	$('.section-edit fieldset.dependency').hide().end();
	
	$(document).on('click', '.edit-save-btn', function(e) {
		var missingField = false;
		var fieldset = $(this).closest('fieldset');
		$.each(fieldset.serializeArray(), function(i, field) {
			if ($('[name="'+field.name+'"]').prop('tagName') == 'SELECT') {
				field.value = $("[name='"+field.name+"'] option:selected").text();
			}
			if ($('[name="'+field.name+'"]').hasClass('required') && !$('[name="'+field.name+'"]').val()) {
				$("label[for='"+$('[name="'+field.name+'"]').attr('id')+"']").addClass('invalid');
				$('[name="'+field.name+'"]').closest('label').addClass('invalid');
				missingField = true;
			}
			name = field.name.replace(/\[/g,'_').replace(/\]/g,'');
			fieldset.find('.edit-values span.'+name).text(field.value);
		});
		if (missingField) { 
			$('html, body').animate({
				scrollTop: $('.invalid').first().offset().top
			}, 1000);
			return false;
		}
		
		// check for submit
		if ($(this).text() == 'Submit') {
			check4Copy();
			$(this).closest('form').submit();
			return;
		}
		
		setEdit(fieldset.next());
		
		$(document).trigger('wbty_setup');
	});
	
	$(document).on('click', '.edit-modify-btn', function(e) {
		setEdit($(this).closest('fieldset'));
	});
	
	$(document).on('click', '.edit-add-btn', function(e) {
		if (!$(this).closest('fieldset').hasClass('addCloned')) {
			$(this).closest('fieldset').addClass('addCloned');
		}
		
		$(this).closest('fieldset').clone().insertAfter($(this).closest('fieldset'));
		fieldset = $(this).closest('fieldset').next();
		
		fieldset
			.find("input[type=text], textarea, select").val("").end()
			.find('.edit-values span').text('').end();
		
		$(this).closest('fieldset').find('.edit-save-btn').click();
	});
	
	var setEdit = function (fieldset) {
		if (fieldset.hasClass('dependency')) {
			if (fieldset.data('value') && fieldset.data('field')) {
				if (fieldset.parent().find('[name="'+fieldset.data('field')+'"]').val() == fieldset.data('value')) {
					fieldset.show();
				} else {
					fieldset.hide();
					setEdit(fieldset.next());
					return;
				}
			}
		}
		
		fieldset.siblings()
			.find('.edit-form').hide(500).end()
			.find('.edit-values').hide().end()
		.end()
		.prevAll()
			.find('.edit-values').show().end()
		.end()
		.find('.edit-form').show(500).end()
		.find('.edit-buttons').remove().end()
		.find('.edit-values').hide().end();
		
		if (fieldset.next().length) {
			fieldset.find('.edit-form').show().append($buttons.find('.edit-save-btn').text('Continue').end());
		} else {
			fieldset.find('.edit-form').show().append($buttons.find('.edit-save-btn').text('Submit').end());
		}
		if (fieldset.hasClass('multiple')) {
			fieldset.find('.edit-form .edit-buttons').prepend($addAnother);
		} else {
			fieldset.find('.edit-form .edit-add-btn').remove();
		}
		
		if (fieldset.attr('data-copy')) {
			copy_set = $('fieldset[name='+fieldset.attr('data-copy')+']');
			if (copy_set.length) {
				name = copy_set.find('legend').text();
				fieldset.find('.edit-buttons').append($copyBtn).find('.edit-copy-btn').text('Copy ' + name);
			}
		}
	}
	
	/* - - - - BREAK - - - - - */
	
	
	var hidden_forms = $('#hidden-forms').html();
	$('#hidden-forms').remove();
	$(document).on('wbty_setup', function() {
		
		$('.select2-container').remove();
		//$('select').select2({width: '80%'});
		
		//$('.calendar').parent().find('input[type=text]').datepicker({ dateFormat: "M dd',' yy" });
		//$('.calendar').hide();
	});
	$(document).trigger('wbty_setup');
	
	$(document).on('click', '.inline-edit-trigger', function(e) {
		$this = $(this);
		
		// remove already open instances of the edit box.
		$('.edit-form').remove();
		
		if (fieldset_name = $this.attr('data-fieldset')) {
			var fieldset = null;
			$(hidden_forms).each(function() {
				if ($(this).attr('name') == fieldset_name) {
					fieldset = $(this);
				}
			});
		}
		if (!fieldset || fieldset.length < 1) {
			alert('Error loading form');
			return false;
		}
		
		$this.closest('fieldset').append(
			fieldset.find('.edit-form')
				.attr('data-controller', $this.attr('data-controller'))
				.attr('data-update', $this.attr('data-update'))
				.attr('data-fieldset', $this.attr('data-fieldset'))
				.attr('data-append', $this.attr('data-append'))
				.end()
			.find('.parent_id').val($('.parentform .id').val()).end()
			.html()
		);

		form = $this.closest('fieldset').find('.edit-form');
		if ($this.hasClass('edit-item')) {
			values = $.parseJSON($this.closest('fieldset').find('.record_values').val());
			$.each(values, function(i, value) {
				$('.edit-form [name="'+i+'"]').val(value);
			})
		}
		
		form.append(
			$inlinebuttons
				.find('.inline-edit-save-btn').text('Submit').end()
				.find('.inline-edit-reset-btn').text('Cancel').end()
		).find('select');//.select2({width: '80%'});
		
		//$('.calendar').parent().find('input[type=text]').datepicker({ dateFormat: "M dd',' yy" });
		//$('.calendar').hide();
		
		form.find('.inline-edit-save-btn').attr('disabled', 'disabled');
		url = window.juri_base + 'index.php?option=' + $('#option').val() + '&task='+ $this.attr('data-controller') + '.edit';
		
		if ($this.attr('data-append')) {
			url = url + '&' + $this.attr('data-append');
		}
		
		$.ajax({
			url: url,
			type: 'GET',
			context: form,
			success: function(resp) {
				$(this).find('form').append($(resp).find('.component-content form input').last());
				$(this).find('.inline-edit-save-btn').removeAttr('disabled');
			}
		});
		
		$('html, body').animate({
				scrollTop: parseInt(form.offset().top) - 100
			},
			'fast');
		
		return false;
	});
	
	$('body').on('click', '.inline-edit-save-btn', function(e) {
		$fields = $(this).closest('.edit-form');
		
		// make sure to save editors
		$('.wfEditor').each(function() {
			$(this).html(tinyMCE.get($(this).attr('id')).save());
		});
		
		if (!$fields.attr('data-controller')) {
			alert('Improper form could not be submitted. Please notify system administrator.');
			return;
		}
		
		url = window.juri_base + 'index.php?option=' + $('#option').val() + '&task='+ $fields.attr('data-controller') + '.ajax_save';
		
		if ($fields.attr('data-append')) {
			url = url + '&' + $fields.attr('data-append');
		}
		
		$(this).closest('.edit-form').after($('<p style="text-align:center;" data-update="'+$(this).closest('.edit-form').attr('data-update')+'" data-fieldset="'+$(this).closest('.edit-form').attr('data-fieldset')+'"><img src="'+window.juri_root+'media/wbty_components/img/load.gif" /></p>'));
		
		data = $fields.closest('.edit-form').find('input, textarea, select').serializeArray();

		token = new Object;
		token.name = ((typeof window.token === 'undefined') ? window.token : $(this).closest('form').find('input').last().attr('name'));
		token.value = 1;
		data.push(token);

		ajaxsave(url, data, $(this).closest('.edit-form').next());

		$(this).closest('.edit-form').remove();
	});

	$('body').on('click', '.state-change', function(e) {
		if ($(this).hasClass('delete')) {
			state = -2;
		} else if ($(this).hasClass('restore')) {
			state = 1;
		} else {
			alert('Error changing state of item.');
		}

		if (fieldset_name = $(this).attr('data-fieldset')) {
			var fieldset = null;
			$(hidden_forms).each(function() {
				if ($(this).attr('name') == fieldset_name) {
					fieldset = $(this);
				}
			});
		}

		id_name = $(fieldset).find('.id').attr('name');
		values = $.parseJSON($(this).closest('fieldset').find('.record_values').val());
		id = values[id_name];

		url = window.juri_base + 'index.php';

		$.ajax({
			url: url,
			dataType: 'json',
			data: {
				option: $('#option').val(),
				task: $(this).attr('data-fieldset') + '.ajax_state',
				id: id,
				state_val: state
			},
			type: 'POST',
			context: this,
			success: function(msg) {
				if (msg.error) {
					alert(msg.error);
				} else {
					fieldset = $(this).closest('fieldset');
					if (msg.state == 1) {
						$(this).removeClass('btn-success').removeClass('restore').addClass('delete').addClass('btn-danger').text('Remove');
						$('#' + $(this).attr('data-fieldset') + '-published').append(fieldset);
					} else {
						$(this).addClass('btn-success').addClass('restore').removeClass('delete').removeClass('btn-danger').text('Restore');
						$('#' + $(this).attr('data-fieldset') + '-trashed').append(fieldset);
					}
					
				}
				if (msg.token) {
					window.token = msg.token;
				}
			},
			error: function (x, status, error) {
				console.log(status + '-' + error);
				console.log(x);
			}
		});
	});
	
	$('body').on('click', '.inline-edit-reset-btn', function(e) {
		$(this).closest('.edit-form').remove();
	});
	
	$('body').on('click', '.edit-copy-btn', function(e) {
		fieldset = $('fieldset[name=' + $(this).closest('fieldset').attr('data-copy') + ']');
		
		var this_form;
		this_form = $(this).closest('.edit-form');
		
		if (fieldset.length) {
			$.each(fieldset.serializeArray(), function (index, input) {
				array = input.name.split("][");
				base_name = array[array.length - 1];
				this_form.find('[name$="' + base_name + '"]').val(input.value);
			});
		}
	});
	
	$('body').keypress(function(e) {
		if (e.which == 13) {
			el = $(document.activeElement);
			if (!el.is('textarea') && el.closest('.edit-form').length == 1) {
				// submit the form!
				el.closest('.edit-form').find('.inline-edit-save-btn').trigger('click');
			}
		}
	});

	// ordering save function
	$('.sortable').on('sortstop', function(event, ui) {
		var ids = [];
		$.each($(this).find('.id'), function(i, v) {
			ids.push($(this).val());
		});

		url = window.juri_base + 'index.php';
		controller = $(this).find('.edit-item').attr('data-controller');
		$.ajax({
			url: url,
			data: {
				option: $('#option').val(),
				task: controller + '.ajax_order',
				ids: ids
			},
			type: 'POST'
		})
	});
	
	/* - - - - BREAK - - - - - */
	
	$(document).on('click', '.ajax-remove', function(e) {
		var $this = $(this);
		window.update = $(this).closest('.item-wrap');
		url = $(this).attr('href');
		if (url.indexOf('?')!==-1) {
			url = url + '&tmpl=component';
		} else {
			url = url + '?tmpl=component';
		}
		
		$.ajax({
			url: url,
			type: 'GET',
			success: function(resp) {
				if (console !== undefined) {
					console.log(resp);
				}
				if (resp) {
					window.update.remove();
				} else {
					alert("Error removing the item.");
				}
			}
		});
		return false;
	});
});