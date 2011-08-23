<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Database
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.database.table');

/**
 * Object Table class
 *
 * Parent class to all tables.
 *
 * @package     Joomla.Platform
 * @subpackage  Table
 * @since       11.1
 * @tutorial	Joomla.Platform/jormdatabasetable.cls
 * @link		http://docs.joomla.org/JORMDatabaseTable
 */
class JORMDatabaseTable extends JTable
{
	/**
	 * Call to special method when exists.
	 * This methods will recive value to has be changed or modified.
	 * 
	 * @since 11.1
	 */
	public function set($property,$value)
	{
		$method_name = 'set'.$property;
		if( method_exists($this,$method_name) )
		{
			call_user_method($method_name, $this,$value);
		}
		else{
			parent::set($property,$value);
		}
	}
}