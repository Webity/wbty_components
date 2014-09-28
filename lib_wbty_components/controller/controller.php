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


// check for Joomla 2.5
if (!class_exists('JControllerLegacy')) {
	jimport('joomla.application.component.controller');
	class JControllerLegacy extends JController {};
}


/**
 * {formview} controller class.
 */
class WbtyController extends JControllerLegacy
{

    function __construct() {
        parent::__construct();
    }
	
}