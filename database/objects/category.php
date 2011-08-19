<?php
/**
 * @package     Joomla.Platform
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * category ORM
 *
 *
 * @package     Joomla.Platform
 * @subpackage  Objects
 * @since       11.1
 * @tutorial	Joomla.Platform/category.cls
 * @link		http://docs.joomla.org/category
 */
class Category extends JORMDatabaseQuery
{
	/**
	 * List of fields to select.
	 *
	 * @var    Array
	 * @since  11.1
	 */
	protected $_fields = array(
		'id',
		'level',
		'extension',
		'path',
		'title',
		'alias',
		'description',
		'access',
		'params',
		'metadesc',
		'metakey',
		'metadata',
		'hits'
	);
	
	/**
	 * Name of the database table.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $_tbl = '#__categories';
	
	/**
	 * Alias to table.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $_tbl_alias = 'cat';
	
	/**
	 * The JTable Class.
	 *
	 * @var	JTable	A JTable object.
	 * @since  11.1
	 */
	protected $_jtable = array(
		'type' => 'category',
		'prefix' => 'jtable'
	);
	
	/**
	 * Foreign tables references.
	 *
	 * @var    Array
	 * @since  11.1
	 */
	protected $_foreign_tables = array(
		'#__content' => array(
			'jointype' => 'LEFT',
			'conditions' => 'cat.id = a.catid',
			'columns' => array(
				'cat.title AS category_title',
				'c.path AS category_route',
				'c.access AS category_access',
				'c.alias AS category_alias'
			)
		),
		'#__banners' => array(
			'jointype' => 'LEFT',
			'conditions' => 'cat.id = b.catid',
			'columns' => array(
				'cat.title AS category_title',
				'c.path AS category_route',
				'c.access AS category_access',
				'c.alias AS category_alias'
			)
		)
	);
}