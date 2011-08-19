<?php
/**
 * @package     Joomla.Platform
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * UserGroup ORM
 *
 *
 * @package     Joomla.Platform
 * @subpackage  Objects
 * @since       11.1
 * @tutorial	Joomla.Platform/usergroup.cls
 * @link		http://docs.joomla.org/usergroup
 */
class UserGroup extends JORMDatabaseQuery
{
	/**
	 * List of fields to select.
	 *
	 * @var    Array
	 * @since  11.1
	 */
	protected $_fields = array(
		'grp.id',
		'grp.parent_id',
		'grp.title'
	);
	
	/**
	 * Name of the database table.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $_tbl = '#__usergroups';
	
	/**
	 * Alias to table.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $_tbl_alias = 'grp';
	
	/**
	 * The JTable Class.
	 *
	 * @var	JTable	A JTable object.
	 * @since  11.1
	 */
	protected $_jtable = array(
		'type' => 'usergroup',
		'prefix' => 'jtable'
	);
}