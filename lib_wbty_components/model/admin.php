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

// check for Joomla 2.5
if (!class_exists('JModelAdmin')) {
	jimport('joomla.application.component.modeladmin');
}

/**
 * {Name} model.
 */
abstract class WbtyModelAdmin extends JModelAdmin
{
	public function __construct($config = array())
	{
		parent::__construct($config);
		JForm::addFieldPath(dirname(__FILE__) . '/fields');
	}
	
	public function getItem($pk = null)
	{
		if ($result = parent::getItem($pk))
		{
			// Convert the created and modified dates to local user time for display in the form.
			jimport('joomla.utilities.date');
			$tz = new DateTimeZone(JFactory::getApplication()->getCfg('offset'));

			if (intval($result->created_time))
			{
				$date = new JDate($result->created_time);
				$date->setTimezone($tz);
				$result->created_time = $date->toSql(true);
			}
			else
			{
				$result->created_time = null;
			}

			if (intval($result->modified_time))
			{
				$date = new JDate($result->modified_time);
				$date->setTimezone($tz);
				$result->modified_time = $date->toSql(true);
			}
			else
			{
				$result->modified_time = null;
			}
		}

		return $result;
	}

	/**
	 * Prepare and sanitise the table prior to saving.
	 *
	 * @since	1.6
	 */
	protected function prepareTable(&$table)
	{
		jimport('joomla.filter.output');

		if (empty($table->id)) {

			// Set ordering to the last item if not set
			if (@$table->ordering === '') {
				$db = JFactory::getDbo();
				$db->setQuery('SELECT MAX(ordering) FROM {#__table}');
				$max = $db->loadResult();
				$table->ordering = $max+1;
			}
			
			$user =& JFactory::getUser();
			$table->created_by = $user->id;
			$table->created_time = strftime('%Y-%m-%d %H:%M:%S');
			$this->table_id = 0;
		} else {
			$this->table_id = $table->id;
		}
		
		$table->modified_by = $user->id;
		$table->modified_time = strftime('%Y-%m-%d %H:%M:%S');
	}
	
	function save($data) {
		// Initialise variables;
		$dispatcher = JDispatcher::getInstance();
		$table = $this->getTable();
		$key = $table->getKeyName();
		$pk = (!empty($data[$key])) ? $data[$key] : (int) $this->getState($this->getName() . '.id');
		$isNew = true;

		// Include the content plugins for the on save events.
		JPluginHelper::importPlugin('content');

		// Allow an exception to be thrown.
		try
		{
			// Load the row if saving an existing record.
			if ($pk > 0)
			{
				$table->load($pk);
				$isNew = false;
			}

			// Bind the data.
			if (!$table->bind($data))
			{
				$this->setError($table->getError());
				return false;
			}

			// Prepare the row for saving
			$this->prepareTable($table);
			
			// Check the data.
			if (!$table->check())
			{
				$this->setError($table->getError());
				return false;
			}
			
			// Trigger the onContentBeforeSave event.
			$result = $dispatcher->trigger($this->event_before_save, array($this->option . '.' . $this->name, &$table, $isNew));
			if (in_array(false, $result, true))
			{
				$this->setError($table->getError());
				return false;
			}
			
			// Store the data.
			if (!$table->store())
			{
				$this->setError($table->getError());
				return false;
			}
			
			// Clean the cache.
			$this->cleanCache();

			// Trigger the onContentAfterSave event.
			$dispatcher->trigger($this->event_after_save, array($this->option . '.' . $this->name, &$table, $isNew));
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		$pkName = $table->getKeyName();

		if (isset($table->$pkName))
		{
			$this->setState($this->getName() . '.id', $table->$pkName);
		}
		$this->setState($this->getName() . '.new', $isNew);
		
		$this->table_db = $table->getDbo();
		
		if ($table->id) {
			$this->table_id = $table->id;
		} elseif($this->table_id==0) {
			$id = $this->table_db->insertid();
			$this->table_id = $id;
		}
		
		return $this->table_id;
	}
	
	protected function saveRelational($table, $key, $array) {
		if (!$this->table_id) {
			return false;
		}
		
		// Remove any old related items
		$query = 'DELETE FROM #__'.$this->com_name.'_'.$table.' WHERE '.$this->list_name.'_id='.$this->table_id;
		$this->_db->setQuery($query)->query();
		
		if ($array) {
			foreach ($array as $id) {
				$query = 'INSERT INTO #__'.$this->com_name.'_'.$table.' SET '.$key.'='.$id.', '.$this->list_name.'_id='.$this->table_id;
				$this->_db->setQuery($query)->query();
			}
		}
		
		return true;
	}
	
	public function getRelational($table, $key, $id) {
		$query = 'SELECT '.$key.' FROM #__'.$this->com_name.'_'.$table.' WHERE '.$this->list_name.'_id='.$id;
		return $this->_db->setQuery($query)->loadColumn();
	}
	
	protected function save_sub($name, $values, $parent_key) {
		require_once (JPATH_COMPONENT. '/models/'.$name.'.php' );
		
		// check for Joomla 2.5
		$model = class_exists('JModelLegacy') ? JModelLegacy::getInstance( $name, $this->com_name.'Model' ) : JModel::getInstance( $name, $this->com_name.'Model' );
		
		$ids = array();
		foreach($values as $key => $value) {
			$jform = array();
			foreach ($value as $k=>$v) {
				if ($v=='|set2id|') {
					$v = $this->table_id;
				}
				$jform[$k] = $v;
			}
			$jform[$parent_key] = $this->table_id;
			$ids[] = $model->save($jform);
		}
		return $this->clean_sub($name, $ids, $parent_key, $model);
	}
	
	protected function clean_sub($name, $ids, $parent_key, &$model) {
		if ($ids) {
			$where = 'id NOT IN ('.implode(',', $ids).') AND ';
		} else {
			$where = '';
		}
		$table_name = $model->getTable()->getTableName();
		$query = 'UPDATE '.$table_name.' SET state=-2 WHERE '.$where.$parent_key.'='.$this->_db->quote($this->table_id);
		return $this->_db->setQuery($query)->query();
	}
	
	protected function loadForm($name, $source = null, $options = array(), $clear = false, $xpath = false)
	{
		// Handle the optional arguments.
		$options['control'] = JArrayHelper::getValue($options, 'control', false);

		// Create a signature hash.
		$hash = md5($source . serialize($options));

		// Check if we can use a previously loaded form.
		if (isset($this->_forms[$hash]) && !$clear)
		{
			return $this->_forms[$hash];
		}

		// Get the form.
		WbtyForm::addFormPath(JPATH_COMPONENT . '/models/forms');
		WbtyForm::addFieldPath(JPATH_COMPONENT . '/models/fields');

		try
		{
			$form = WbtyForm::getInstance($name, $source, $options, false, $xpath);

			if (isset($options['load_data']) && $options['load_data'])
			{
				// Get the data for the form.
				$data = $this->loadFormData();
			}
			else
			{
				$data = array();
			}

			// Allow for additional modification of the form, and events to be triggered.
			// We pass the data because plugins may require it.
			$this->preprocessForm($form, $data);

			// Load the data into the form after the plugins have operated.

			// preprocess due to bug in JForm bindLevel
			if (is_array($data)) {
				foreach ($data as $key=>$d) {
					if ($d instanceof JObject) {
						// Handle a JObject.
						$data[$key] = $d->getProperties();
					}
				}
			}
			
			$form->bind($data);

		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());
			return false;
		}

		// Store the form for later.
		$this->_forms[$hash] = $form;

		return $form;
	}

	public function setOrder($ids) {
		$db = JFactory::getDbo();

		foreach ($ids as $index=>$id) {
			$data = new stdClass();
			$data->id = (int)$id;
			$data->ordering = $index;

			$db->updateObject($this->getTable()->getTableName(), $data, 'id');
		}

		return true;
	}
}