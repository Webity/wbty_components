<?php
/**
 * @package AkeebaReleaseSystem
 * @copyright Copyright (c)2010-2011 Nicholas K. Dionysopoulos
 * @license GNU General Public License version 3, or later
 * @version $Id: ars.php 123 2011-04-13 07:47:16Z nikosdion $
 */

class plgsystemwbty_componentsInstallerScript {
	
	function postflight($type, $parent) {
		require_once(dirname(__FILE__) . '/install.wbty_components.php');
	}
}