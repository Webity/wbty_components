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
class JFormFieldWbtyCalendar extends JFormField
{

	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $type = 'WbtyCalendar';

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
					$this->value = $date->format('Y-m-d H:i:s', true, false);
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
					$this->value = $date->format('Y-m-d H:i:s', true, false);
				}
				break;
		}

		return self::calendar($this->value, $this->name, $this->id, $format, $attributes);
	}

	public static function calendar($value, $name, $id, $format = '%Y-%m-%d', $attribs = null)
	{
		static $done;

		if ($done === null)
		{
			$done = false;
		}

		$readonly = isset($attribs['readonly']) && $attribs['readonly'] == 'readonly';
		$disabled = isset($attribs['disabled']) && $attribs['disabled'] == 'disabled';

		if (is_array($attribs))
		{
			$attribs = JArrayHelper::toString($attribs);
		}

		JHtml::_('jquery.framework');
		JHtml::_('script', 'wbty_components/jquery-ui-datepicker.min.js', false, true);
		JHtml::_('stylesheet', 'wbty_components/jquery-ui-datepicker.min.css', false, true);

		if (!$readonly && !$disabled)
		{
			if (!$done) {
				$done = true;

				JFactory::getDocument()->addScriptDeclaration('jQuery(document).ready(function($) {$("body").on("focus", ".wbtydatepicker", function(){if (!$(this).hasClass("hasDatepicker")){$(this).datepicker({changeMonth: true,changeYear: true,showButtonPanel: true});$(this).datepicker("show");}});});');
			}

			return '<div class="input-append"><input type="text" class="hasTooltip wbtydatepicker" title="' . (0 !== (int) $value ? JHtml::_('date', $value, null, null) : '')
				. '" name="' . $name . '" id="' . $id . '" value="' . htmlspecialchars($value, ENT_COMPAT, 'UTF-8') . '" ' . $attribs . ' />'
				. '</div>';
		}
		else
		{
			return '<input type="text" class="hasTooltip" title="' . (0 !== (int) $value ? JHtml::_('date', $value, null, null) : '')
				. '" value="' . (0 !== (int) $value ? JHtml::_('date', $value, 'Y-m-d H:i:s', null) : '') . '" ' . $attribs
				. ' /><input type="hidden" name="' . $name . '" id="' . $id . '" value="' . htmlspecialchars($value, ENT_COMPAT, 'UTF-8') . '" />';
		}
	}
}
