<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Form Field class for the Joomla Platform.
 *
 * Provides a pop up date picker linked to a button.
 * Optionally may be filtered to use user's or server's time zone.
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @since       11.1
 */
class JFormFieldCalendarSelects extends JFormField
{

	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $type = 'CalendarSelects';

	public function __get($name = '') {
		if ($name=='value') {
			if ($this->value) {
				$val = strtotime($this->value);
				$month = strftime('%m', $val);
				$day = strftime('%d', $val);
				$year = strftime('%Y', $val);
			} else {
				$month = $day = $year = '';
			}
			return array('month'=>$month, 'day'=>$day, 'year'=>$year);
		}

		return parent::__get($name);
	}

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string   The field input markup.
	 *
	 * @since   11.1
	 */
	protected function getInput()
	{
		// Initialize some field attributes.
		$format = $this->element['format'] ? (string) $this->element['format'] : '%Y-%m-%d';

		// Build the attributes array.
		$attributes = array();
		if ($this->element['size'])
		{
			$attributes['size'] = (int) $this->element['size'];
		}
		if ($this->element['maxlength'])
		{
			$attributes['maxlength'] = (int) $this->element['maxlength'];
		}
		if ($this->element['class'])
		{
			$attributes['class'] = (string) $this->element['class'];
		}
		if ((string) $this->element['readonly'] == 'true')
		{
			$attributes['readonly'] = 'readonly';
		}
		if ((string) $this->element['disabled'] == 'true')
		{
			$attributes['disabled'] = 'disabled';
		}
		if ($this->element['onchange'])
		{
			$attributes['onchange'] = (string) $this->element['onchange'];
		}
		if ($this->required)
		{
			$attributes['required'] = 'required';
			$attributes['aria-required'] = 'true';
		}

		// Handle the special case for "now".
		if (strtoupper($this->value) == 'NOW')
		{
			$this->value = strftime($format);
		}

		// Get some system objects.
		$config = JFactory::getConfig();
		$user = JFactory::getUser();

		// If a known filter is given use it.
		switch (strtoupper((string) $this->element['filter']))
		{
			case 'SERVER_UTC':
				// Convert a date to UTC based on the server timezone.
				if ((int) $this->value)
				{
					// Get a date object based on the correct timezone.
					$date = JFactory::getDate($this->value, 'UTC');
					$date->setTimezone(new DateTimeZone($config->get('offset')));

					// Transform the date string.
					$this->value = $date->format('Y-m-d', true, false);
				}
				break;

			case 'USER_UTC':
				// Convert a date to UTC based on the user timezone.
				if ((int) $this->value)
				{
					// Get a date object based on the correct timezone.
					$date = JFactory::getDate($this->value, 'UTC');
					$date->setTimezone(new DateTimeZone($user->getParam('timezone', $config->get('offset'))));

					// Transform the date string.
					$this->value = $date->format('Y-m-d', true, false);
				}
				break;
		}

		if ($this->value) {
			$val = strtotime($this->value);
			$month = strftime('%m', $val);
			$day = strftime('%d', $val);
			$year = strftime('%Y', $val);
		} else {
			$month = $day = $year = '';
		}

		$months = array(0 => array('value'=>'', 'text'=>'Month'));
		for ($i = 1; $i <= 12; $i++) {
			$months[$i]['value'] = str_pad($i, 2, '0', STR_PAD_LEFT);
			$dateTimeObj = DateTime::createFromFormat('!m', $i);
			$months[$i]['text'] = $dateTimeObj->format('F');
		}

		$days = array(0 => array('value'=>'', 'text'=>'Day'));
		for ($i = 1; $i <= 31; $i++) {
			$days[$i]['value'] = str_pad($i, 2, '0', STR_PAD_LEFT);
			$days[$i]['text'] = str_pad($i, 2, '0', STR_PAD_LEFT);
		}

		$years = array(0 => array('value'=>'', 'text'=>'Year'));
		for ($i = 1; $i <= 115; $i++) {
			$years[$i]['value'] = date('Y') - $i;
			$years[$i]['text'] = date('Y') - $i;
		}

		$name = (string)$this->element['name'];

		$input = '';

		$input .= JHtmlSelect::genericlist($months, $this->getName($name . '][month'), null, 'value', 'text', $month);

		$input .= JHtmlSelect::genericlist($days, $this->getName($name . '][day'), null, 'value', 'text', $day);

		$input .= JHtmlSelect::genericlist($years, $this->getName($name . '][year'), null, 'value', 'text', $year);

		return $input;
	}
}
