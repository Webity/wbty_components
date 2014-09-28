<?php
/**
 * @copyright	Copyright (C) 2013 Webity. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

/**
 * Preloader for Wbty_components
 *
 * @package		Webity.Wbty_components
 * @subpackage	System.wbty_components
 */
class plgSystemWbty_components extends JPlugin
{
	public function onAfterInitialise()
	{
		// preload WBTY Components Library based on Wbty Prefix
		JLoader::registerPrefix('Wbty', JPATH_LIBRARIES . '/wbty_components');
		JLoader::registerPrefix('WBTY', JPATH_LIBRARIES . '/wbty_components');

		// register JHtml class for use throughout site
		WbtyJhtml::register();
	}
}
