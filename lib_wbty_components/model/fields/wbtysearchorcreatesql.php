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
 * Supports an custom SQL select list
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @since       11.1
 */
class JFormFieldWbtySearchOrCreateSQL extends JFormFieldText
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $type = 'WbtySearchOrCreateSQL';

	/**
	 * Method to get the custom field options.
	 * Use the query attribute to supply a query to generate the list.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   11.1
	 */

	public function setup(&$element, $value, $group = null)
	{
		if (!parent::setup($element, $value, $group)) {
			return false;
		}
		
		$this->display_value = (string) $element['display_value'];
		return true;
	}
	 
	public function getInput() {
		static $done;

		if ($done === NULL) {
			$done = array();
		}

		if(JDEBUG){
			JHtml::_('script', 'wbty_components/select2.min.js', false, true);
		} else {
			JHtml::_('script', 'wbty_components/select2.js', false, true);
		}
		JHtml::_('script', 'wbty_components/search-or-create.js', false, true);
		JHtml::_('stylesheet', 'wbty_components/select2.css', false, true);

		$id = $this->element['id'] ? (string) $this->element['id'] : '';
		$this->id = $this->getId((string) $id, $this->fieldname);

		$option = $this->element['option'] ? (string) $this->element['option'] : '';
		$min_input = $this->element['min_input'] ? (string) $this->element['min_input'] : 3;
		$placeholder = $this->element['placeholder'] ? (string) $this->element['placeholder'] : 'Make a selection';
		$list_controller = $this->element['list_controller'] ? (string) $this->element['list_controller'] : '';
		$form_controller = $this->element['form_controller'] ? (string) $this->element['form_controller'] : '';
		$search_function = $this->element['search_function'] ? (string) $this->element['search_function'] : 'ajaxListSearch';
		$create_function = $this->element['create_function'] ? (string) $this->element['create_function'] : 'ajaxCreate';
		$load_function = $this->element['load_function'] ? (string) $this->element['load_function'] : 'ajaxLoad';
		$quietMillis = $this->element['quietMillis'] ? (string) $this->element['quietMillis'] : 100;
		$page_limit = $this->element['page_limit'] ? (string) $this->element['page_limit'] : 5;
		$allow_clear = $this->element['allow_clear'] ? (bool) $this->element['allow_clear'] : false;

		$html = array();

		if (!$done[$this->id]) :
		ob_start();
?>

jQuery(document).ready(function($) {
	$("#<?php echo $this->id; ?>").wbtySearchOrCreate({
		'placeholder': '<?php echo $placeholder; ?>',
		'min_input': '<?php echo $min_input; ?>',
		'option': '<?php echo $option; ?>',
		'list_controller': '<?php echo $list_controller; ?>',
		'form_controller': '<?php echo $form_controller; ?>',
		'search_function': '<?php echo $search_function; ?>',
		'create_function': '<?php echo $create_function; ?>',
		'load_function': '<?php echo $load_function; ?>',
		'quietMillis': '<?php echo $quietMillis; ?>',
		'page_limit': '<?php echo $page_limit; ?>',
		'allow_clear': '<?php echo $allow_clear; ?>'
	});
});
<?php
		$script = ob_get_clean();
		JFactory::getDocument()->addScriptDeclaration($script);

		$done[$this->id] = true;
		endif; 

		$attr = '';

		// Initialize some field attributes.
		$attr .= $this->element['class'] ? ' class="' . (string) $this->element['class'] . ' select2"' : '';
		$attr .= $this->element['form'] ? ' data-create-form="' . (string) $this->element['form'] . '"' : '';

		// To avoid user's confusion, readonly="true" should imply disabled="true".
		if ((string) $this->element['readonly'] == 'true' || (string) $this->element['disabled'] == 'true')
		{
			$attr .= ' disabled="disabled"';
		}

		$attr .= $this->element['size'] ? ' size="' . (int) $this->element['size'] . '"' : '';
		$attr .= $this->multiple ? ' multiple="multiple"' : '';
		$attr .= $this->required ? ' required="required" aria-required="true"' : '';

		// Initialize JavaScript field attributes.
		$attr .= $this->element['onchange'] ? ' onchange="' . (string) $this->element['onchange'] . '"' : '';

		$html[] = '<div><input type="hidden" id="' . $this->id . '" name="' . $this->name . '" value="' . $this->value . '" '.$attr.' /></div>';
		return implode($html);
		
	}

	protected function getLabel() {
		$label = parent::getLabel();
		static $forms_done;

		if (!is_array($forms_done)) {
			$forms_done = array();
		}
		// this has to go with the label, otherwise select2 likes to remove it ?
		// also, we only make one per page and then use javascript to move them around as necessary
		$form = $this->element['form'] ? (string) $this->element['form'] : '';
		$force_form = ((string)$this->element['force_form']) == 'true' ? true : false;
		ob_start();
		if ($form && (!$forms_done[$form] || $forms_done[$form] == $this->id) || $force_form) {
			$hidden_form = JForm::getInstance($form, $form);
			echo  JHtml::_('wbty.buildEditForm', $hidden_form, true, 'hidden-form-'.$form);
			$forms_done[$form] = $this->id;
		}
		$label .= ob_get_clean();
		return $label;
	}

	public function getLabelText() {
		$text = $this->element['label'] ? (string) $this->element['label'] : (string) $this->element['name'];
		$text = $this->translateLabel ? JText::_($text) : $text;

		return $text;
	}

	public function getDisplayValue() {
		$option = $this->element['option'] ? (string) $this->element['option'] : '';
		$form_controller = $this->element['form_controller'] ? (string) $this->element['form_controller'] : '';

		$input = JFactory::getApplication()->input;

		$path = JPATH_BASE . '/components/' . strtolower($option) . '/controllers/' . strtolower($form_controller) . '.php';
		if (file_exists($path)) {
			require_once($path);

			$class = ucfirst(substr($option, 4)) . 'Controller' . ucfirst($form_controller);

			$controller = new $class;

			$old_id = $input->get('id');
			$input->set('id', $this->value);

			$return = $controller->ajaxLoad(false);
		}

		$input->set('id', $old_id);

		if ($return['text']) {
			return $return['text'];
		} else {
			return $this->value;
		}
	}

	public function setName($name) {
		$this->name = $this->getName($name);
		$this->id = $this->getId('', $name);
		return $this;
	}

	public function setValue($value) {
		$this->value = $value;
		return $this;
	}
}
