<?php
/**
 * @package     Joomla.Platform
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * User ORM
 *
 *
 * @package     Joomla.Platform
 * @subpackage  Objects
 * @since       11.1
 * @tutorial	Joomla.Platform/user.cls
 * @link		http://docs.joomla.org/user
 */
class User extends JORMDatabaseQuery
{
	/**
	 * List of fields to select.
	 *
	 * @var    Array
	 * @since  11.1
	 */
	protected $_fields = array(
		'u.id',
		'u.name',
		'u.email',
		'u.username',
		'u.gid',
		'u.registerDate',
		'u.lastvisitDate',
		'u.activation',
		'u.params'
	);
	
	/**
	 * Name of the database table.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $_tbl = '#__users';
	
	/**
	 * Alias to table.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $_tbl_alias = 'u';
	
	/**
	 * The JTable Class.
	 *
	 * @var	JTable	A JTable object.
	 * @since  11.1
	 */
	protected $_jtable = array(
		'type' => 'user',
		'prefix' => 'jtable'
	);
	protected $_foreign_tables = array(
		'#__content' => array(
			'jointype' => 'left',
			'condition' => 'a.created_by = u.id'
		)
	);
	
	/**
	 * Reference to UserGroup ORM
	 * 
	 * @since  11.1
	 */
	public function userGroup()
	{
		$userGroup = self::getInstance('UserGroup');
		$userGroup->_query = &$this->_query;
		
		$userGroup->_query->leftJoin('#__user_usergroup_map AS map2 ON map2.user_id = u.id');
		$userGroup->_query->select('grp.title AS usergroup');
		$userGroup->_query->leftJoin('#__usergroup AS grp ON grp.group_id = u.gid');
		
		//Create a reference to back to scope
		$userGroup->addReference($this->getName(),get_class($this));
		
		return $userGroup;
	}
}