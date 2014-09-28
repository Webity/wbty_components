<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Supports an custom SQL select list
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @since       11.1
 */
class JFormFieldDependentModalSQL extends JFormFieldModalSQL
{
	var $script_added = false;
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $type = 'DependentModalSQL';
	
	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   11.1
	 */
	public function getInput()
	{
		// Load our javascript if it has not yet been loaded
		if (!$this->script_added) {
			$this->loadScript();
		}

		$input = parent::getInput();
		
		// Initialize some field attributes.
		$dependent_field = $this->element['dependent_field'] ? ' data-dependent-field="' . (string) $this->element['dependent_field'] . '"' : '';
		$dependent_values = $this->element['dependent_values'] ? ' data-dependent-values="' . (string) $this->element['dependent_values'] . '"' : '';

		$input = str_replace(
			'<select',
			'<select '.$dependent_field . $dependent_values, 
			$input);

		return $input;
	}
	
	private function loadScript() {
		$document = JFactory::getDocument();
		
		ob_start();
		?>
        
jQuery(document).ready(function($) {
	var dependentmodalsql = function() {
		$('select[data-dependent-field]').each(function() {
	    	var field = $(this).attr('data-dependent-field');
	        var values = $(this).attr('data-dependent-values').split('|');
	        var select = $(this);

	        console.log(field);

	        $(document).on('change', '[name="'+field+'"]', function(e) {
	        	console.log(values);
	        	if ($.inArray($(this).find('option:selected').val(), values) < 0) {
	            	select.attr("disabled", "disabled");
	            	console.log('disabled');
	            } else {
	            	select.removeAttr('disabled');
	            	console.log('enabled');
	        	}
	        });
	        $('[name="'+field+'"]').trigger('change');
	    });
	}
	$(document).on('wbty_setup', function() {
		dependentmodalsql();
	});

	dependentmodalsql();
});
        
        <?php
		$script = ob_get_contents();
		ob_end_clean();
		$document->addScriptDeclaration($script);
		
		$this->script_loaded = true;
		return true;
	}
}
