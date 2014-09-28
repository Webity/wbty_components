;(function($) { // when document has loaded
	var wbty_soc_hidden_forms = [];
	var searchorcreateajaxsave = function(url, data, context) {
		jQuery.ajax({
			url: url,
			data: data,
			dataType: 'json',
			type: 'POST',
			context: context,
			success: function(msg) {
				if (msg.error) {
					alert(msg.error);
				}
				if (msg.token) {
					window.token = msg.token;
				}

				if (msg.data && msg.text && jQuery(this).attr('data-select')) {
					jQuery('#' + jQuery(this).attr('data-select')).select2('data', {id: msg.id, text: msg.text});
				}
				console.log(msg);
				jQuery(this).remove();
			},
			error: function (x, status, error) {
				console.log(status + '-' + error);
				console.log(x);
			}
		})
	}

	$.fn.wbtySearchOrCreate = function(options) {
		// base to hold last search term
		var search_term = '';
		$(document).on('wbty-soc-setup', null, {'obj': this}, function(e) {
			obj = e.data.obj;
			// have to check if parent is visible since it is a hidden input field
			if (!obj.parent().is(':visible')) {
				return;
			}
			obj.select2('destroy');
			obj.select2({
			    placeholder: options.placeholder,
			    minimumInputLength: options.min_input,
			    allowClear: (options.allow_clear ? 'true' : 'false'),
			    ajax: {
			        url: window.juri_base + "index.php?option="+options.option+"&task="+options.list_controller+"."+options.search_function+"",
			        dataType: 'jsonp',
			        quietMillis: options.quietMillis,
			        data: function (term, page) { // page is the one-based page number tracked by Select2
			        	// hold search term for use elsewhere
			        	search_term = term;
			            return {
			                filter_search: term, //search term
			                limit: options.page_limit, // page size
			                limitstart: (page-1) * options.page_limit, // page number
			            };
			        },
			        results: function (data, page) {
			            var more = (page * options.page_limit) < data.total; // whether or not there are more results available
			            if (!data.total) {
				            data.items.push({id:'none',text:'<span>** No items found. **</span>'});
				        }

			            if (options.create_function != 'false') {
				 			if (!more) {
					 			data.items.push({id:'create',text:'<div class="wbty-soc-create"><i class="icon-plus"></i> Create new</div>'});
					 		}
					 	}
			            // notice we return the value of more so Select2 knows if more results can be loaded
			            return {results: data.items, more: more};
			        }
			    },
			    initSelection: function(element, callback) {
			        // the input tag has a value attribute preloaded that points to a preselected movie's id
			        // this function resolves that id attribute to an object that select2 can render
			        // using its formatResult renderer - that way the movie name is shown preselected
			        var id=$(element).val();
			        if (id!=="") {
			            $.ajax({
			            	url: window.juri_base + "index.php?option="+options.option+"&task="+options.form_controller+"."+options.load_function+"",
			                data: {
			                    id: id
			                },
			                dataType: "jsonp",
							error: function (x, status, error) {
								if (console && typeof console.log == 'function') {
									console.log(status + '-' + error);
									console.log(x);
								}
							}
			            }).done(function(data) { callback(data); });
			        }
			    },
			    dropdownCssClass: "bigdrop", // apply css that makes the dropdown taller
			    escapeMarkup: function (m) { return m; } // we do not want to escape markup since we are displaying html in results
			});
		});

		$(document).on("select2-selecting", "input[id='" + this.attr('id') + "']", function(e) { 
			$(this).parent().find('.create-form, .update-form').remove();
			if (e.object.id == 'none') {
				return false;
			}

			// clear any previous review or update methods
			if ($(this).next('div').hasClass('create-form') || $(this).next('div').hasClass('update-form')) {
				$(this).next('div').remove();
			}

			if (e.object.id == 'create') {
				hidden_class = 'hidden-form-' + $(this).attr('data-create-form');
				if (!wbty_soc_hidden_forms[hidden_class]) {
					wbty_soc_hidden_forms[hidden_class] = $('.' + hidden_class).first().html();
					$('.' + hidden_class).remove();
				}
				if (wbty_soc_hidden_forms[hidden_class]) {
					hidden_form = wbty_soc_hidden_forms[hidden_class];
				}
				if (hidden_form) {
					$(this).after('<div class="create-form inset-form" data-option="'+options.option+'" data-controller="'+options.form_controller+'" data-function="'+options.create_function+'" data-select="'+this.id+'">' + hidden_form + '<div class="input-group"><button class="btn btn-danger wbty-cancel"><i class="icon-arrow-left"></i> Cancel Create</button><button class="btn btn-success wbty-create">Create Record <i class="icon-arrow-right"></i></button></div></div>');
					$(document).trigger('wbty-soc-setup');
				} else {
					alert('Error loading create form');
				}
				$form = $(this).next('div.create-form');
				if ($form.find('.search-all').length > 0) {
					$form.find('.search-all').val(search_term);
				}
				if ($form.find('.search-first').length > 0 || $form.find('.search-rest').length > 0) {
					pieces = search_term.split(" ");
					start = pieces.splice(0, 1);
					end = pieces.join(" ");
					$form.find('.search-first').val(start);
					$form.find('.search-rest').val(end);
				}
			} else {
				// let's load the object so that they can update it
				$(this).after('<fieldset class="update-form inset-form form-horizontal" data-option="'+options.option+'" data-controller="'+options.form_controller+'" data-id="' + e.object.id + '"><img src="'+window.juri_root+'/media/wbty_components/img/load.gif" /></fieldset>');
				$(this)
					.next('.update-form')
					.load(window.juri_root+'/index.php?option='+options.option+'&task='+options.form_controller+'.ajaxUpdate&id=' + e.object.id, function() {
						if (jQuery.isFunction($.fn.wbtyAddForm) && $('#hidden-forms')) {
							$('#hidden-forms')
								.find('label').addClass('control-label').end()
								.find('.id').removeAttr('id').end()
								.wbtyAddForm();
						}
					});
	 		}
		});
	}

	console.log('code?');

	$(document).on('click', '.create-form .wbty-create', function(e) {
		console.log(e);
		$fields = $(this).closest('.create-form');
		
		// make sure to save editors
		$('.wfEditor').each(function() {
			$(this).html(tinyMCE.get($(this).attr('id')).save());
		});
		
		if (!$fields.attr('data-controller') || !$fields.attr('data-option')) {
			alert('Improper form could not be submitted. Please notify system administrator.');
			return;
		}
		
		url = window.juri_base + 'index.php?option='+$fields.attr('data-option')+'&task='+ $fields.attr('data-controller') + '.' + $fields.attr('data-function');
		
		if ($fields.attr('data-append')) {
			url = url + '&' + $fields.attr('data-append');
		}
		
		$fields.after($('<p style="text-align:center;" data-select="'+$fields.attr('data-select')+'"><img src="'+window.juri_root+'media/wbty_components/img/load.gif" /></p>'));
		
		data = $fields.find(':input')
					.filter(function() {
						if ($(this).parent().is(':visible')) {
							return true;
						}
						return false;
					})
					.serializeArray();

		tok = new Object;
		tok.value = 1;
 
		if (typeof window.token === 'undefined') {
			input = $(this).closest('form').find('input').last();
			if (input.val() == 1) {
				window.token = input.attr('name');
			} else {
				$('form').each(function() {
					input = $(this).find('input').last();
					if (input.val() == 1) {
						window.token = input.attr('name');
					}
				});
			}
		}

		tok.name = window.token;
		data.push(tok);

		searchorcreateajaxsave(url, data, $fields.next());

		$fields.remove();
	});

	$('body').on('click', '.create-form .wbty-cancel', function(e) {
		$('#' + $(this).closest('.create-form').attr('data-select')).select2("val", "");
		$(this).closest('.create-form').remove();
	});

	$(document).on('wbty_setup_complete', function() {
		$(document).trigger('wbty-soc-setup');
	});

	$(document).on('wbty-soc-setup', function() {
		$(document).trigger('wbty_setup');
	});

	setTimeout(function() {
		$(document).trigger('wbty-soc-setup');
	}, 100);
}(jQuery));
