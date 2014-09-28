<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

JFormHelper::loadFieldClass('sql');

/**
 * Supports an custom SQL select list
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @since       11.1
 */
class JFormFieldModalSQL extends JFormFieldSQL
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $type = 'ModalSQL';

	/**
	 * Method to get the custom field options.
	 * Use the query attribute to supply a query to generate the list.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   11.1
	 */
	 
	public function getInput() {
		$input = parent::getInput();
		return $input . $this->getModal();
		
	}
	public function getModal() {
		if (!$this->element['add_modal_new']) {
			return '';
		}
		
		return '<a class="btn btn-primary" id="modal-btn" data-toggle="modal" data-target="myModal" data-name="Add New" data-save="'.$this->element['modal_view_name'].'.ajax_save" href="index.php?option='.$this->element['modal_com_name'].'&view='.$this->element['modal_view_name'].'&layout=edit&tmpl=component" data-loading="'.JURI::root(true).'/media/system/img/modal/spinner.gif" >New</a>';
	}

	public function getDisplayValue() {
		$key = $this->element['key_field'] ? (string) $this->element['key_field'] : 'value';
		$value = $this->element['value_field'] ? (string) $this->element['value_field'] : (string) $this->element['name'];
		$translate = $this->element['translate'] ? (string) $this->element['translate'] : false;
		$query = (string) $this->element['query'];

		// Get the database object.
		$db = JFactory::getDBO();

		// Set the query and get the result list.
		$db->setQuery($query);
		$items = $db->loadObjectlist($key);

		return $items[$this->value]->$value;
	}
}
