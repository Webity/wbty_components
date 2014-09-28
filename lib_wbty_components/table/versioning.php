<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Database
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;
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
abstract class WbtyTableVersioning extends WbtyTable
{
	// special version of the store function to save the current record as a new row if a current record exists
	public function store($updateNulls = false)
	{
		// Initialise variables.
		$k = $this->_tbl_key;
		if (!empty($this->asset_id))
		{
			$currentAssetId = $this->asset_id;
		}

		// The asset id field is managed privately by this class.
		if ($this->_trackAssets)
		{
			unset($this->asset_id);
		}

		// If a primary key exists update the object, otherwise insert it.
		if ($this->$k)
		{
			// load the currect row from the database
			$query = $this->_db->getQuery(true);
			$query->select('*');
			$query->from($this->_tbl);
			$query->where($this->_tbl_key . '=' . $this->$k);
			$this->_db->setQuery($query);
			$old_row = $this->_db->loadObject();

			// unset the primary key
			$key = $this->_tbl_key;
			unset($old_row->$key);
			// also unset checked out and checked out time, if exist
			if (isset($old_row->checked_out)) { unset($old_row->checked_out); }
			if (isset($old_row->checked_out_time)) { unset($old_row->checked_out_time); }

			// set the base_id value, which is necessary to link back to the main row
			$old_row->base_id = $this->$k;

			// store the new row
			$check = $this->_db->insertObject($this->_tbl, $old_row);

			// if we successfully made the new row, then update the old (i.e. base row) to the new values
			if ($check) {
				$stored = $this->_db->updateObject($this->_tbl, $this, $this->_tbl_key, $updateNulls);
			}
		}
		else
		{
			$stored = $this->_db->insertObject($this->_tbl, $this, $this->_tbl_key);
		}

		// If the store failed return false.
		if (!$stored)
		{
			$e = new JException(JText::sprintf('JLIB_DATABASE_ERROR_STORE_FAILED', get_class($this), $this->_db->getErrorMsg()));
			$this->setError($e);
			return false;
		}

		// If the table is not set to track assets return true.
		if (!$this->_trackAssets)
		{
			return true;
		}

		if ($this->_locked)
		{
			$this->_unlock();
		}

		//
		// Asset Tracking
		//

		$parentId = $this->_getAssetParentId();
		$name = $this->_getAssetName();
		$title = $this->_getAssetTitle();

		$asset = JTable::getInstance('Asset', 'JTable', array('dbo' => $this->getDbo()));
		$asset->loadByName($name);

		// Re-inject the asset id.
		$this->asset_id = $asset->id;

		// Check for an error.
		if ($error = $asset->getError())
		{
			$this->setError($error);
			return false;
		}

		// Specify how a new or moved node asset is inserted into the tree.
		if (empty($this->asset_id) || $asset->parent_id != $parentId)
		{
			$asset->setLocation($parentId, 'last-child');
		}

		// Prepare the asset to be stored.
		$asset->parent_id = $parentId;
		$asset->name = $name;
		$asset->title = $title;

		if ($this->_rules instanceof JAccessRules)
		{
			$asset->rules = (string) $this->_rules;
		}

		if (!$asset->check() || !$asset->store($updateNulls))
		{
			$this->setError($asset->getError());
			return false;
		}

		// Create an asset_id or heal one that is corrupted.
		if (empty($this->asset_id) || ($currentAssetId != $this->asset_id && !empty($this->asset_id)))
		{
			// Update the asset_id field in this table.
			$this->asset_id = (int) $asset->id;

			$query = $this->_db->getQuery(true);
			$query->update($this->_db->quoteName($this->_tbl));
			$query->set('asset_id = ' . (int) $this->asset_id);
			$query->where($this->_db->quoteName($k) . ' = ' . (int) $this->$k);
			$this->_db->setQuery($query);

			if (!$this->_db->execute())
			{
				$e = new JException(JText::sprintf('JLIB_DATABASE_ERROR_STORE_FAILED_UPDATE_ASSET_ID', $this->_db->getErrorMsg()));
				$this->setError($e);
				return false;
			}
		}

		return true;
	}


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
        foreach ($pks as $pk) {
        	$this->load($pk);
        	if ($this->checked_out != 0 && $this->checked_out != $userId) {
        		continue;
        	}
        	$this->state = $state;
        	$this->store();
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
