<?php
/**
 * @package     Joomla.Platform
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Content ORM
 *
 *
 * @package     Joomla.Platform
 * @subpackage  Objects
 * @since       11.1
 * @tutorial	Joomla.Platform/category.cls
 * @link		http://docs.joomla.org/category
 */
class Content extends JORMDatabaseQuery
{
	/**
	 * List of fields to select.
	 *
	 * @var    Array
	 * @since  11.1
	 */
	protected $_fields = array(
		'id',
		'title',
		'alias',
		'title_alias',
		'introtext',
		'fulltext',
		'created',
		'catid',
		'created_by',
		'creted_by_alias',
		'modified',
		'metakey',
		'metadesc',
		'version',
		'hits',
		'metadata',
		'featured',
		'ordering',
		'attribs'
	);
	/**
	 * Name of the database table.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $_tbl = '#__content';
	
	/**
	 * Alias to table.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $_tbl_alias = 'a';
	
	/**
	 * The JTable Class.
	 *
	 * @var	JTable	A JTable object.
	 * @since  11.1
	 */
	protected $_jtable = array(
		'type' => 'content',
		'prefix' => 'jtable'
	);
}