<?php
/**
 * @package     Joomla.Platform
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * JORM Database Query class
 *
 * Joomla Object Relational Map Query
 *
 * @package     Joomla.Platform
 * @subpackage  Database
 * @since       11.1
 * @tutorial	Joomla.Platform/jormdatabasequery.cls
 * @link		http://docs.joomla.org/JORMDatabaseQuery
 */
class JORMDatabaseQuery
{
	/**
	 * List of fields to select.
	 *
	 * @var    Array
	 * @since  11.1
	 */
	protected $_fields	= '';
	
	/**
	 * Name of the database table.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $_tbl	= '';
	
	/**
	 * Alias to table.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $_tbl_alias	= '';
	
	/**
	 * JDatabase connector object.
	 *
	 * @var    object
	 * @since  11.1
	 */
	protected $_db;
	
	/**
	 * The JTable Class.
	 *
	 * @var	JTable	A JTable object.
	 * @since  11.1
	 */
	protected $_jtable = array(
		'prefix' => 'JTable',
		'type' => null
	);
	
	/**
	 * Foreign tables references.
	 *
	 * @var    Array
	 * @since  11.1
	 */
	protected $_foreign_tables = array();
	
	/**
	 * The JDatabaseQuery Class.
	 *
	 * @var	JDatabaseQuery	A JDatabaseQuery object.
	 * @since  11.1
	 */
	protected $_query;
	
	/**
	 * Reference to objects.
	 *
	 * @var    Array Object
	 * @since  11.1
	 */
	protected $_references = array();
	
	/**
	 * Constructor class can recive another JORMDatabaseQuery object by reference
	 * 
	 * @since 11.1
	 */
	public function __construct($reference=null)
	{
		//checking object
		if( is_object($reference) )
		{
			$reflection = new ReflectionClass(get_class($reference));
			if($reflection->isSubclassOf(__CLASS__)){
				//Copy query instance
				$this->query = &$reference->query;
				//Initialize
				$this->_initialize();
				//Auto join
				$this->_autoJoin($reference);
				//Create a reference to back to scope
				$this->addReference($reference->getName(),get_class($reference));
			}
		}
		
		// Set internal variables.
		$this->_db 		= JFactory::getDbo(); 
		$this->_query 	= $this->_db->getQuery(true);
		//Initialize
		$this->_initialize();
		//Create select
		$this->_createSelect();
	}
	
	/**
	 * Create a instance from object that extends JDatabaseQuery Class these objects helps to construct a query builder
	 * 
	 * @since 11.1
	 */
	public static function getInstance($queryObject,$reference=null)
	{
		// Sanitize and prepare the table class name.
		$queryObject = preg_replace('/[^A-Z0-9_\.-]/i', '', $queryObject);
		$queryObjectClass = ucfirst($queryObject);
		
		// Only try to load the class if it doesn't already exist.
		if (!class_exists($queryObjectClass)) {
			// Search for the class file in the JTable include paths.
			jimport('joomla.filesystem.path');

			if ($path = JPath::find(self::addIncludePath(), strtolower($queryObject).'.php')) {
				// Import the class file.
				require_once $path;

				// If we were unable to load the proper class, raise a warning and return false.
				if (!class_exists($queryObjectClass)) {
					JError::raiseWarning(0, JText::sprintf('JLIB_DATABASE_ERROR_CLASS_NOT_FOUND_IN_FILE', $queryObjectClass));
					return false;
				}
			}
			else {
				// If we were unable to find the class file in the JTable include paths, raise a warning and return false.
				JError::raiseWarning(0, JText::sprintf('JLIB_DATABASE_ERROR_NOT_SUPPORTED_FILE_NOT_FOUND', $queryObject));
				return false;
			}
		}
		
		// Instantiate a new helper class and return it.
		return new $queryObjectClass($reference);
	}
	
	/**
	 * Create a dinamyc instance of array options passed by reference
	 * 
	 * @since 11.1
	 */
	public static function createInstance(array $options)
	{
		$instance = new JORMDatabaseQuery();
		
		//initialize vars
		$instance->_fields = isset($options['fields']) ? $options['fields'] : array() ;
		$instance->_tbl = $options['tbl'];
		$instance->_tbl_alias = isset($options['tbl_alias']) ? $options['tbl_alias'] : null ;
		$instance->_jtable = ( isset($options['jtable']) && is_array($options['jtable']) ) ? $options['jtable'] : array() ;
		//check jable prefix
		if( !empty($instance->_jtable) && !isset($instance->_jtable['prefix']) ){
			$instance->_jtable['prefix'] = 'JTable';
		}
		
		//Initialize
		$instance->_initialize();
		
		//Create select
		$instance->_createSelect();
		
		return $instance;
	}
	
	/**
	 * This function will build a select on table
	 * 
	 * @since 11.1
	 */
	private function _createSelect()
	{
		if( empty($this->_fields) && empty($this->_tbl) ) return;
		
		$this->_query->select($this->_fields)->from($this->_getTable());
	}
	
	/**
	 * 
	 * 
	 * @since 11.1
	 */
	private function _getTable()
	{
		$table = $this->_tbl;
		if( !empty($this->_tbl_alias) ) $table .= ' AS '.$this->_tbl_alias;
		
		return $table;
	}
	
	/**
	 * Return a JTable instance
	 * 
	 * @since 11.1
	 * @return JTable Object
	 */
	public function getJTable()
	{
		return $this->_jtable;
	}
	
	/**
	 * Check the autojoin between JORMDatabaseQuery objects
	 * 
	 * @since 11.1
	 */
	private function _autoJoin($reference)
	{
		if( !array_key_exists($reference->_table, $this->_foreign_tables) ) return;
		
		$foreign = $this->_foreign_tables[$reference->_table];
		$join_type 	= $foreign['jointype'];
		$columns 	= isset($foreign['columns']) ? $foreign['columns'] : array() ;
		$conditions = $this->_getTable()
					.$foreign['conditions'];
		
		switch($join_type)
		{
			case 'left':
				$this->_query->leftJoin($conditions);
				break;
			case 'rigth':
				$this->_query->rightJoin($conditions);
				break;
			default:
				$this->_query->join($join_type,$conditions);
				break;
		}
		
		if( !empty($columns) )
			$this->_query->select($columns);
	}
	
	/**
	 * Initialize some variables
	 * 
	 * @since 11.1
	 */
	protected function _initialize()
	{
		if( empty($this->_tbl) ) return;
		
		//get table columns
		$columns = $this->_db->getTableColumns($this->_tbl);
		
		//check column type and add to countable work if has a numeric type
		foreach($columns as $field => $field_type)
		{
			switch($field_type)
			{
				case 'tinyint':
				case 'int':
					JORMInflector::addCountable($field);
			}
		}

		//set the select fields if empty
		if( empty($this->_fields) )
			$this->_fields = array_keys($columns);
			
		//check config of JTable class
		if( !empty($this->_jtable) && is_array($this->_jtable) && isset($this->_jtable['type']) && isset($this->_jtable['prefix']) )
		{
			$this->_instanceJTable($this->_jtable['type'],$this->_jtable['prefix']);
		}
	}
	
	/**
	 * Instance a JTable class
	 * 
	 * @since 11.1
	 */
	public function _instanceJTable($type,$prefix='JTable')
	{
		$this->_jtable = JTable::getInstance($type,$prefix);
		
		return $this;
	}
	
	/**
	 * Return name of class or self name property
	 * 
	 * @since 11.1
	 */
	public function getName()
	{
		return !empty($this->_name) ? $this->_name : get_class($this) ;
	}
	
	/**
	 * Add path to helper classes
	 * 
	 * @since 11.1
	 */
	public static function addHelperPath($path = null)
	{
		JORMDatabaseQueryHelper::addIncludePath($path);
	}
	
	/**
	 * Instance a Helper class that do stuffs like: render modules, dump data, etc.
	 * 
	 * @since 11.1
	 */
	public function getHelper($helper)
	{
		return JORMDatabaseQueryHelper::getInstance($helper, $this);
	}
	
	/**
	 * Add a filesystem path where JTable should search for table class files.
	 * You may either pass a string or an array of paths.
	 *
	 * @param   mixed  A filesystem path or array of filesystem paths to add.
	 *
	 * @return  array  An array of filesystem paths to find JTable classes in.
	 *
	 * @link    http://docs.joomla.org/JTable/addIncludePath
	 * @since   11.1
	 */
	public static function addIncludePath($path = null)
	{
		// Declare the internal paths as a static variable.
		static $_paths;

		// If the internal paths have not been initialised, do so with the base table path.
		if (!isset($_paths)) {
			$_paths = array(dirname(__FILE__) . '/objects');
		}

		// Convert the passed path(s) to add to an array.
		settype($path, 'array');

		// If we have new paths to add, do so.
		if (!empty($path) && !in_array($path, $_paths)) {
			// Check and add each individual new path.
			foreach ($path as $dir)
			{
				// Sanitize path.
				$dir = trim($dir);

				// Add to the front of the list so that custom paths are searched first.
				array_unshift($_paths, $dir);
			}
		}

		return $_paths;
	}
	
	/**
	 * Set a property on JTable that control 
	 * 
	 * @since 11.1
	 */
	public function __set($property,$value)
	{
		if(!($this->_jtable instanceof JTable)) throw new Exception(JText::_('You must set JTable Class'),500);
		
		$this->_jtable->set($property,$value);
	}
	
	/**
	 * This function will check method and callback using these order
	 * 
	 * 1 - Field check
	 * 2 - JTable 
	 * 2 - JDatabaseQuery
	 * 3 - JDatabase
	 * 
	 * @since 11.1
	 */
	public function __call($method,$arguments)
	{
		settype($arguments, 'array');
		
		$count_arguments = count($arguments);
		
		//check if method is a reference
		if( array_key_exists($method, $this->_references) ){
			return $this->_references[$method];
		}
		
		//check if method is a field
		$field_key = array_search($method, $this->_fields);
		
		//check if exists on fields list
		if( array_search($method, $this->_fields) !== false ){
			$table = $this->_tbl;
			if( !empty($this->_tbl_alias) ) $table = $this->_tbl_alias;
			
			$string = $table.'.'.$method;
			
			//check if is one argument set the condition equal argument
			if( $count_arguments == 1 ){
				$string .= '=' . $this->_db->quote($arguments[0],true);
			}
			else{
				//add quote to every argument
				foreach($arguments as $argument)
					$this->_db->quote($argument,true);
				
				//if countable field change the comparison method to IN, else use LIKE
				if( JORMInflector::countable($method) )
				{
					$string .= ' IN('. implode(',',$arguments) .')';
				}
				else{
					$string .= ' LIKE("'. implode('","',$arguments) .'")';
				}
			}
			
			//add to where clause
			$this->_query->where($string);
			return $this;
		}
		
		/**
		 * Call JTable methods
		 */
		if( method_exists($this->_jtable, $method) )
		{
			return call_user_method_array($method, $this->_jtable, $arguments);
		}
		
		/**
		 * Call JDatabaseQuery methods
		 */
		if( method_exists($this->_query, $method) )
		{
			call_user_method_array($method, $this->_query, $arguments);
			return $this;
		}
		
		/**
		 * Call JDatabase methods
		 */
		if( method_exists($this->_db, $method) )
		{
			$this->_db->setQuery($this->_query);
			return call_user_method_array($method, $this->_db, $arguments);
		}
		
		throw new Exception(JText::sprintf('Undefined method %s on class '.get_class($this),$method),500);
	}
}