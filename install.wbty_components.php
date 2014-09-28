<?php
/**
 * @package Wbty_gallery
 * @copyright Copyright (C) 2012-2013. All rights reserved.
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 * @author Webity <info@makethewebwork.com> - http://www.makethewebwork.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

// Install modules and plugins -- BEGIN

// -- General settings
jimport('joomla.installer.installer');
$db = & JFactory::getDBO();


if( version_compare( JVERSION, '1.6.0', 'ge' ) ) {
	// Thank you for removing installer features in Joomla! 1.6 Beta 13 and
	// forcing me to write ugly code, Joomla!...
	$src = dirname(__FILE__);
} else {
	$src = $this->parent->getPath('source');
}

$installer = new JInstaller;
$result = $installer->install($src.'/lib_wbty_components');
$status->library = array('name'=>'Wbty Component Library','result'=>$result);

// enable plugin
$query = "UPDATE #__extensions SET enabled=1 WHERE type='plugin' AND element='wbty_components' AND folder='system'";
$db->setQuery($query)->execute();
	

// Install libraries, modules, and plugins -- END

// Finally, show the installation results form
?>
<h1>Wbty Components</h1>

<h2>Welcome!</h2>

<p>Thank you for installing Wbty Components.</p>