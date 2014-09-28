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
 * Supports an custom SQL select list
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @since       11.1
 */
class JFormFieldDependentsql extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $type = 'Dependentsql';
	
	protected function getOptions()
	{
		// Initialize variables.
		$options = array();

		// Initialize some field attributes.
		$key = $this->element['key_field'] ? (string) $this->element['key_field'] : 'value';
		$value = $this->element['value_field'] ? (string) $this->element['value_field'] : (string) $this->element['name'];
		$translate = $this->element['translate'] ? (string) $this->element['translate'] : false;
		
		$query = (string) $this->element['query'];

		// Get the database object.
		$db = JFactory::getDBO();

		// Set the query and get the result list.
		$db->setQuery($query);
		$this->items = $db->loadObjectlist($key);

		// Check for an error.
		if ($db->getErrorNum())
		{
			JError::raiseWarning(500, $db->getErrorMsg());
			return $options;
		}

		// Build the field options.
		if (!empty($this->items))
		{
			foreach ($this->items as $item)
			{
				if ($translate == true)
				{
					$options[] = JHtml::_('select.option', $item->$key, JText::_($item->$value));
				}
				else
				{
					$options[] = JHtml::_('select.option', $item->$key, $item->$value);
				}
			}
		}

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
	
	protected function getInput()
	{
		JHTML::script('wbty_components/dependentsql.js', false, true);
		
		// Initialize variables.
		$html = array();
		$attr = '';

		// Initialize some field attributes.
		$attr .= $this->element['class'] ? ' class="' . (string) $this->element['class'] . '"' : '';

		// To avoid user's confusion, readonly="true" should imply disabled="true".
		if ((string) $this->element['readonly'] == 'true' || (string) $this->element['disabled'] == 'true')
		{
			$attr .= ' disabled="disabled"';
		}

		$attr .= $this->element['size'] ? ' size="' . (int) $this->element['size'] . '"' : '';
		$attr .= $this->multiple ? ' multiple="multiple"' : '';

		// Initialize JavaScript field attributes.
		$attr .= $this->element['onchange'] ? ' onchange="' . (string) $this->element['onchange'] . '"' : '';

		// Get the field options.
		$options = (array) $this->getOptions();
		
		$attr .= $this->element['dependent_field'] ? ' data-dependent-field="' . (string) $this->element['dependent_field'] . '"' : '';

		// Create a read-only list (no name) with a hidden input to store the value.
		if ((string) $this->element['readonly'] == 'true')
		{
			$html[] = JHtml::_('select.genericlist', $options, '', trim($attr), 'value', 'text', $this->value, $this->id);
			$html[] = '<input type="hidden" name="' . $this->name . '" value="' . $this->value . '"/>';
		}
		// Create a regular list.
		else
		{
			$html[] = JHtml::_('select.genericlist', $options, $this->name, trim($attr), 'value', 'text', $this->value, $this->id);
		}
		
		
		$dependent_value = $this->element['dependent_value'] ? (string) $this->element['dependent_value'] : false;
		foreach ($html as $k=>$h) {
			$dom = new DOMDocument();
			$select = $dom->loadHTML($h);
			$newoptions = array();
			
			$options =& $dom->getElementsByTagName('option');
			foreach ($options as $option) {
				$id = $option->getAttribute('value');
				$dependency = $this->items[$id]->$dependent_value;
				$option->setAttribute('data-dependent-value', $dependency);
			}
			$html[$k] = $dom->saveHTML();
		}
		
		return implode($html);
	}
}
