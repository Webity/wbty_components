<?php
/**
 * @version     {version}
 * @package     {com_name}
 * @copyright   {copyright}
 * @license     {license}
 * @author      {author}
 */

// No direct access
defined('_JEXEC') or die;


jimport('legacy.controller.form');

// check for Joomla 2.5
if (!class_exists('JControllerForm')) {
	jimport('joomla.application.component.controllerform');
}


/**
 * {formview} controller class.
 */
class WbtyControllerForm extends JControllerForm
{

    function __construct() {
        parent::__construct();
    }

    public function edit($key = null, $urlVar = null)
	{
		$return = parent::edit($key, $urlVar);

		if (!$return) {
			$this->setRedirect(
				JRoute::_(
					'index.php?option=' . $this->option . '&view=' . $this->view_list
					. $this->getRedirectToListAppend(), false
				)
			);
		}

		return $return;
	}
	
	function ajaxCreate() {
		$this->model = $this->getModel();
		$jinput = JFactory::getApplication()->input;
		$data = $jinput->get($this->view_form, array(), 'ARRAY');

		$return = array(json_encode($data));
		$token = JSession::checkToken();
		if ($token && $id = $this->model->save($data, array())) {
			require_once(JPATH_COMPONENT_ADMINISTRATOR . '/helpers/ajax.php');
			$helper_name = $this->com_name . 'HelperAjax';
			$helper = new $helper_name();
			$form = $this->model->getForm(array(), true, 'jform', $id);

			$return['id'] = $id;
			$return['text'] = $this->formatAjaxCreate($id);
			$return['data'] = $helper->link_html($this->view_form, $id, $form);
			$return['token'] = JSession::getFormToken();
		} else {
			if (!$token) {
				$return['error'] = "Invalid Token";
			} else {
				$return['error'] = "Error saving record.";
			}
			$return['token'] = JSession::getFormToken();
		}
		echo json_encode($return);
		exit();
	}

	function ajaxLoad($json = true) {
		$input = JFactory::getApplication()->input;
		$id = $input->get('id', 0, 'INT');

		$return['id'] = $id;
		$return['text'] = $this->formatAjaxCreate($id);

		if (!$json) {
			return $return;
		}

		header('Content-type: application/json');

		if ($input->get('callback')) {
	        print $input->get('callback')."(";
	    }
		echo json_encode($return);
	    if ($input->get('callback')) {
	        print ")";
	    }
		exit();
	}

	protected function formatAjaxCreate($id) { return ''; }
	
	function ajax_save() {
		$this->model = $this->getModel();
		$jinput = JFactory::getApplication()->input;
		$jform = $jinput->get('jform', array(), 'ARRAY');
		$data = $jform[$this->view_form];

		$return = array(json_encode($data));
		if (JSession::checkToken() && $id = $this->model->save($data, array())) {
			require_once(JPATH_COMPONENT_ADMINISTRATOR . '/helpers/ajax.php');
			$helper_name = $this->com_name . 'HelperAjax';
			$helper = new $helper_name();
			$form = $this->model->getForm(array(), true, 'jform', $id);

			$return['id'] = $id;
			$return['data'] = $helper->link_html($this->view_form, $id, $form);
			$return['token'] = JSession::getFormToken();
		} else {
			$return['error'] = "error";
			$return['token'] = JSession::getFormToken();
		}
		echo json_encode($return);
		exit();
	}

	function ajax_checkout() {
		$app = JFactory::getApplication();
		
		if (!$id = $app->input->get('id', 0)) {
			echo json_encode(array('error'=>'No id set'));
			exit();
		}

		$this->model = $this->getModel();
		$table = $this->model->getTable();

		$table->load($id);
		$checkout = $table->checkout(JFactory::getUser()->id);

		$return = array();
		if ($table->id == $id && $checkout) {
			$return['id'] = $id;
			$return['token'] = JSession::getFormToken();
		} else {
			$return['error'] = "Unable to load or checkout record";
			$return['token'] = JSession::getFormToken();
		}

		echo json_encode($return);
		exit();
	}

	function ajax_state() {
		$app = JFactory::getApplication();

		if (!$id = $app->input->get('id', 0)) {
			echo json_encode(array('error'=>'ID set incorrectly'));
			exit();
		}
		
		$state = $app->input->get('state_val', 0);
		if (!($state == 1 || $state == -2)) {
			echo json_encode(array('error'=>'Invalid state setting' . $state));
			exit();
		}

		$this->model = $this->getModel();
		$status = $this->model->publish($id, $state);

		$return = array();
		if ($status) {
			$return['id'] = $id;
			$return['token'] = JSession::getFormToken();
			$return['state'] = $state;
		} else {
			$return['error'] = "Unable to update state of item";
			$return['token'] = JSession::getFormToken();
		}

		echo json_encode($return);
		exit();
	}

	function ajax_order() {
		$app = JFactory::getApplication();
		if (!$ids = $app->input->get('ids', array(), 'ARRAY')) {
			echo json_encode(array('error'=>'IDs set incorrectly'));
			exit();
		}

		$this->model = $this->getModel();
		$status = $this->model->setOrder($ids);

		$return = array();
		if ($status) {
			$return['success'] = true;
		} else {
			$return['error'] = "Unable to reorder items";
		}

		echo json_encode($return);
		exit();
	}
	
}