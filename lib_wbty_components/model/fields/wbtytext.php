<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

JFormHelper::loadFieldClass('text');

/**
 * Form Field class for the Joomla Platform.
 * Supports a one line text field.
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @link        http://www.w3.org/TR/html-markup/input.text.html#input.text
 * @since       11.1
 */
class JFormFieldWbtyText extends JFormFieldText
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 *
	 * @since  11.1
	 */
	protected $type = 'WbtyText';

	public function __get($name)
	{
		switch ($name) {
			case 'display_value':
				return $this->$name;
			default:
				return parent::__get($name);
		}
	}
	
	public function setup(&$element, $value, $group = null)
	{
		if (!parent::setup($element, $value, $group)) {
			return false;
		}
		
		$this->display_value = (string) $element['display_value'];
		return true;
	}
}
