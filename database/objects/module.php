<?php
/**
 * @package     Joomla.Platform
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Module ORM
 *
 *
 * @package     Joomla.Platform
 * @subpackage  Objects
 * @since       11.1
 * @tutorial	Joomla.Platform/module.cls
 * @link		http://docs.joomla.org/module
 */
class Module extends JORMDatabaseQuery
{
	/**
	 * Name of the database table.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $_tbl = '#__modules';
	
	/**
	 * Alias to table.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $_tbl_alias = 'm';
	
	/**
	 * The JTable Class.
	 *
	 * @var	JTable	A JTable object.
	 * @since  11.1
	 */
	protected $_jtable = array(
		'type' => 'Module',
		'prefix' => 'jtable'
	);
	
	/**
	 * Reference to Menu ORM
	 * 
	 * @since  11.1
	 */
	public function Menu()
	{
		$menu = self::getInstance('Menu');
		$menu->_query = &$this->_query;
		$menu->_tbl_alias = 'menu';
		$menu->_query->select('mm.menuid');
		$menu->_query->leftJoin('#__modules_menu AS mm ON(mm.moduleid = m.id)');
		$menu->_query->leftJoin('#__menu AS menu ON(menu.id = mm.menuid)');
		
		//Create a reference to back to scope
		$menu->addReference($this->getName(),get_class($this));
		
		return $menu;
	}
}