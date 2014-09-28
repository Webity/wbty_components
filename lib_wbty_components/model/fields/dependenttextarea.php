<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

JFormHelper::loadFieldClass('textarea');

/**
 * Supports an custom SQL select list
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @since       11.1
 */
class JFormFieldDependenttextarea extends JFormFieldTextarea
{
	var $script_added = false;
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $type = 'Dependenttextarea';
	
	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   11.1
	 */
	protected function getInput()
	{
		// Load our javascript if it has not yet been loaded
		if (!$this->script_added) {
			$this->loadScript();
		}
		
		// Initialize some field attributes.
		$class = $this->element['class'] ? ' class="' . (string) $this->element['class'] . '"' : '';
		$disabled = ((string) $this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';
		$columns = $this->element['cols'] ? ' cols="' . (int) $this->element['cols'] . '"' : '';
		$rows = $this->element['rows'] ? ' rows="' . (int) $this->element['rows'] . '"' : '';
		$required = $this->required ? ' required="required" aria-required="true"' : '';
		$dependent_field = $this->element['dependent_field'] ? ' data-dependent-field="' . (string) $this->element['dependent_field'] . '"' : '';
		$dependent_values = $this->element['dependent_values'] ? ' data-dependent-values="' . (string) $this->element['dependent_values'] . '"' : '';

		// Initialize JavaScript field attributes.
		$onchange = $this->element['onchange'] ? ' onchange="' . (string) $this->element['onchange'] . '"' : '';

		return '<textarea name="' . $this->name . '" id="' . $this->id . '"' . $columns . $rows . $class . $disabled . $onchange . $required . $dependent_field . $dependent_values . '>'
			. htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '</textarea>';
	}
	
	private function loadScript() {
		$document = JFactory::getDocument();
		
		ob_start();
		?>
        
jQuery(document).ready(function($) {
	var dependenttextarea = function() {
		$('textarea[data-dependent-field]').each(function() {
	    	var field = $(this).attr('data-dependent-field');
	        var values = $(this).attr('data-dependent-values').split('|');
	        var textarea = $(this);

	        $(document).on('change', '[name="'+field+'"]', function(e) {
	        	if ($.inArray($(this).find('option:selected').val(), values) < 0) {
	            	textarea.attr("disabled", "disabled");
	            } else {
	            	textarea.removeAttr('disabled');
	        	}
	        });
	        $('[name="'+field+'"]').trigger('change');
	    });
	}
	$(document).on('wbty_setup', function() {
		dependenttextarea();
	});

	dependenttextarea();
});
        
        <?php
		$script = ob_get_contents();
		ob_end_clean();
		$document->addScriptDeclaration($script);
		
		$this->script_loaded = true;
		return true;
	}
}
