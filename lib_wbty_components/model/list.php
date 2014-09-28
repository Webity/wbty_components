<?php
/**
 * @version     {version}
 * @package     {com_name}
 * @copyright   {copyright}
 * @license     {license}
 * @author      {author}
 */

defined('_JEXEC') or die;

// check for Joomla 2.5
if (!class_exists('JModelList')) {
	jimport('joomla.application.component.modellist');
}

/**
 * Methods supporting a list of {Name} records.
 */
class WbtyModelList extends JModelList
{
	function processSearch(&$query, $search = array(), $text_fields = array()) {
		if (is_array($search)) {
			foreach ($search as $key=>$val) {
				switch($key) {
					case 'text_search':
						if ($val) {
							$vals = explode(' ', $val);
							foreach ($vals as $val) {
								$where = array();
								$search = $this->_db->quote('%'.$this->_db->escape($val, true).'%');
								foreach ($text_fields as $field) {
									$where[] = 'a.'.$field.' LIKE ('.$search.')';
								}
								if ($where) {
									$query->where('(('.implode(') OR (', $where).'))');
								}
							}
						}
						break;
					default:
						if ($val) {
							$query->where('a.'.$key.'='.$this->_db->quote($val));
						}
						break;
				}
			}
		}
	}
}
