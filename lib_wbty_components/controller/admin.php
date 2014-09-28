<?php
/**
 * @version     {version}
 * @package     {com_name}
 * @copyright   {copyright}
 * @license     {license}
 * @author      {author}
 */

// No direct access.
defined('_JEXEC') or die;

jimport('legacy.controller.admin');

// check for Joomla 2.5
if (!class_exists('JControllerAdmin')) {
	jimport('joomla.application.component.controlleradmin');
}

/**
 * {Listview} list controller class.
 */
class WbtyControllerAdmin extends JControllerAdmin
{
	public function ajaxListSearch() {
		$app = JFactory::getApplication();
		$input = $app->input;

		$model = $this->getListModel();

		$items = $model->getItems();
		$q = (string)$model->getDbo()->getQuery();
		$total = $model->getTotal();

		foreach ($items as $key=>$item) {
			$item->text = $this->formatAjaxSearch($item);
			$items[$key] = $item;
		}

		$return = array();
		$return['items'] = $items;
		$return['total'] = $total;

		if (JDEBUG) {
			$return['last_query'] = $q;
			$return['start'] = $model->getStart();
			$return['list-start'] = $model->getState('list.start');
			$return['list-limit'] = $model->getState('list.limit');
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

	protected function formatAjaxSearch($item) {
		return isset($item->text) ? $item->text : '';
	}

}