<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

JFormHelper::loadFieldClass('list');

/**
 * Form Field class for the Joomla Platform.
 * Supports a one line text field.
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @link        http://www.w3.org/TR/html-markup/input.text.html#input.text
 * @since       11.1
 */
class JFormFieldWbtyDate extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 *
	 * @since  11.1
	 */
	protected $type = 'WbtyDate';
	
	/**
	 * Method to get the field input markup for a generic list.
	 * Use the multiple attribute to enable multiselect.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   11.1
	 */
	protected function getInput()
	{
		JHtml::_('jquery.framework');
		JHtml::script('wbty_components/wbtydate.js', false, true);
		JHtml::stylesheet('wbty_components/form_fields.css', false, true);
		
		$yearSelect = '<select name="yearsSelect" class="wbtydate years"><option>Year</option>';
		$monthSelect = '<select name="monthSelect" class="wbtydate months"><option>Month</option>';
		$daySelect	 = '<select name="daySelect" class="wbtydate days"><option>Day</option>';
		
		// Year select list
		$years = (int)$this->element['years'] ? $this->element['years'] : 100;
		$currentYear = date('Y');
		for ($i = $currentYear; $i >= ($currentYear - $years); $i--) {
			$yearSelect .= '<option value="' . $i .' ">' . $i . '</option>';
		}
		$yearSelect .= '</select>';

		// Month select list
		$months = array(
					'01' => 'January',
					'02' => 'February',
					'03' => 'March',
					'04' => 'April',
					'05' => 'May',
					'06' => 'June',
					'07' => 'July',
					'08' => 'August',
					'09' => 'September',
					'10' => 'October',
					'11' => 'November',
					'12' => 'December'
		);
		foreach ($months as $key => $month) {
			$monthSelect .= '<option value="' . $key .'">' . $month . '</option>';
		}
		$monthSelect .= '</select>';
		
		// Day select list
		for ($i = 1; $i < 32; $i++) {
			$daySelect .= '<option value="' . (($i < 10) ? '0' . $i : $i) .' ">' . $i . '</option>';
		}
		$daySelect .= '</select>';
		
		// Initialize some field attributes.
		$class = ' class="wbtydate-hidden"';
		if ($this->element['class']) $class .= ' ' . $this->element['class'];
		$disabled = ((string) $this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';

		// Initialize JavaScript field attributes.
		$onchange = $this->element['onchange'] ? ' onchange="' . (string) $this->element['onchange'] . '"' : '';

		$dateSelector = $monthSelect . $daySelect . $yearSelect;

		return '<div class="wbtydate-container">' . $dateSelector . '<input type="hidden" name="' . $this->name . '" id="' . $this->id . '" value="'
			. htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '"' . $class . $disabled . $onchange . ' /><div class="clear"></div></div>';
	}
}
