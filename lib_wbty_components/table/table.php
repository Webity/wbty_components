<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Database
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;
jimport('jooma.database.table');
/**
 * Abstract Table class
 *
 * Parent class to all tables.
 *
 * @package     Joomla.Platform
 * @subpackage  Table
 * @link        http://docs.joomla.org/JTable
 * @since       11.1
 * @tutorial	Joomla.Platform/jtable.cls
 */
abstract class WbtyTable extends JTable
{
	
	public function load($keys = null, $reset = true)
	{
		if (!parent::load($keys, $reset)) {
			return false;
		}

		// Our components require javascript to keep the items checked out
		// that needs to make a call every minute to keep the item locked for that user.
		if (property_exists($this, 'checked_out_time') && property_exists($this, 'checked_out')) {
			$base = JDate::getInstance($this->checked_out_time)->toUnix();
			if ($base > 0 && JDate::getInstance()->toUnix() - $base > 120) {
				if ($this->checked_out == JFactory::getUser()->id) {
					$this->checkOut(JFactory::getUser()->id);
				} else {
					$this->checkIn();
				}
			}
		}
		return true;
	}

	public function bind($array, $ignore = '')
	{
		if (is_array($array)) {
			if (isset($array['params']) && is_array($array['params'])) {
				$registry = new JRegistry();
				$registry->loadArray($array['params']);
				$array['params'] = (string)$registry;
			}
	
			if (isset($array['metadata']) && is_array($array['metadata'])) {
				$registry = new JRegistry();
				$registry->loadArray($array['metadata']);
				$array['metadata'] = (string)$registry;
			}
		} elseif (is_object($array)) {
			if (isset($array->params) && is_array($array->params)) {
				$registry = new JRegistry();
				$registry->loadArray($array->params);
				$array->params = (string)$registry;
			}
	
			if (isset($array->metadata) && is_array($array->metadata)) {
				$registry = new JRegistry();
				$registry->loadArray($array->metadata);
				$array->metadata = (string)$registry;
			}
		}
		return parent::bind($array, $ignore);
	}

    public function check() {
        $user = JFactory::getUser();

        //If there is an ordering column and this is a new row then get the next ordering value
        if (property_exists($this, 'ordering') && $this->id == 0) {
            $this->ordering = self::getNextOrder();
        }

        if ($this->id == 0 && property_exists($this, 'created_by') && property_exists($this, 'created_time')) {
            $this->created_by = $user->id;
            $this->created_time = JFactory::getDate()->toSQL();
        }

        if (property_exists($this, 'modified_by') && property_exists($this, 'modified_time')) {
            $this->modified_by = $user->id;
            $this->modified_time = JFactory::getDate()->toSQL();
        }

        return parent::check();
    }


    /**
     * Method to set the publishing state for a row or list of rows in the database
     * table.  The method respects checked out rows by other users and will attempt
     * to checkin rows that it can after adjustments are made.
     *
     * @param    mixed    An optional array of primary key values to update.  If not
     *                    set the instance property value is used.
     * @param    integer The publishing state. eg. [0 = unpublished, 1 = published]
     * @param    integer The user id of the user performing the operation.
     * @return    boolean    True on success.
     * @since    1.0.4
     */
    public function publish($pks = null, $state = 1, $userId = 0)
    {
        // Initialise variables.
        $k = $this->_tbl_key;

        // Sanitize input.
        JArrayHelper::toInteger($pks);
        $userId = (int) $userId;
        $state  = (int) $state;

        // If there are no primary keys set check to see if the instance key is set.
        if (empty($pks))
        {
            if ($this->$k) {
                $pks = array($this->$k);
            }
            // Nothing to set publishing state on, return false.
            else {
                $this->setError(JText::_('JLIB_DATABASE_ERROR_NO_ROWS_SELECTED'));
                return false;
            }
        }

        // Build the WHERE clause for the primary keys.
        $where = $k.'='.implode(' OR '.$k.'=', $pks);

        // Determine if there is checkin support for the table.
        if (!property_exists($this, 'checked_out') || !property_exists($this, 'checked_out_time')) {
            $checkin = '';
        }
        else {
            $checkin = ' AND (checked_out = 0 OR checked_out = '.(int) $userId.')';
        }

        // Update the publishing state for rows with the given primary keys.
        $this->_db->setQuery(
            'UPDATE `'.$this->_tbl.'`' .
            ' SET `state` = '.(int) $state .
            ' WHERE ('.$where.')' .
            $checkin
        );
        $this->_db->query();

        // Check for a database error.
        if ($this->_db->getErrorNum()) {
            $this->setError($this->_db->getErrorMsg());
            return false;
        }

        // If checkin is supported and all rows were adjusted, check them in.
        if ($checkin && (count($pks) == $this->_db->getAffectedRows()))
        {
            // Checkin the rows.
            foreach($pks as $pk)
            {
                $this->checkin($pk);
            }
        }

        // If the JTable instance value is in the list of primary keys that were set, set the instance.
        if (in_array($this->$k, $pks)) {
            $this->state = $state;
        }

        $this->setError('');
        return true;
    }

}
