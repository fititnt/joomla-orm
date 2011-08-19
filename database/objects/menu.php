<?php
/**
 * @package     Joomla.Platform
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Menu ORM
 *
 *
 * @package     Joomla.Platform
 * @subpackage  Objects
 * @since       11.1
 * @tutorial	Joomla.Platform/menu.cls
 * @link		http://docs.joomla.org/menu
 */
class Menu extends JORMDatabaseQuery
{
	/**
	 * Name of the database table.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $_tbl = '#__menu';
	
	/**
	 * The JTable Class.
	 *
	 * @var	JTable	A JTable object.
	 * @since  11.1
	 */
	protected $_jtable = array(
		'type' => 'menu',
		'prefix' => 'jtable'
	);
}