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
	 * Name
	 * 
	 * @var string
	 * @since 11.1
	 */
	protected $_name;
	
	/**
	 * List of fields to select.
	 *
	 * @var    Array
	 * @since  11.1
	 */
	protected $_fields	= '';
	
	/**
	 * Table prefix
	 * 
	 * @var string
	 * @since 11.1
	 */
	protected $_tbl_prefix = '';
	
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
	protected $_jtable = array();
	
	/**
	 * Foreign tables references.
	 *
	 * @var    Array
	 * @since  11.1
	 */
	protected $_foreign_tbls = array();
	
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
		// Set internal variables.
		$this->_db 		= JFactory::getDbo(); 
		
		//checking object
		if( is_object($reference) )
		{
			JORMDatabaseQueryException::checkObjectSubclass($reference);
			
			//Copy query instance
			$this->query = &$reference->query;
			//Initialize
			$this->_initialize();
			//Auto join
			$this->_autoJoin($reference);
			//Create a reference to back to scope
			$this->addReference($reference->getName(),get_class($reference));
		}
		else{
			$this->_query 	= $this->_db->getQuery(true);
			//Initialize
			$this->_initialize();
			//Create select
			$this->_createSelect();
		}
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
					JError::raiseWarning(0, JText::sprintf('JORMLIB_OBJECT_ERROR_CLASS_NOT_FOUND_IN_FILE', $queryObjectClass));
					return false;
				}
			}
			else {
				// If we were unable to find the class file in the JTable include paths, raise a warning and return false.
				JError::raiseWarning(0, JText::sprintf('JORMLIB_OBJECT_ERROR_NOT_SUPPORTED_FILE_NOT_FOUND', $queryObject));
				return false;
			}
		}
		
		// Instantiate a new helper class and return it.
		return new $queryObjectClass($reference);
	}
	
	/**
	 * Create a dinamyc instance of array options passed by reference
	 * 
	 * @param Array options
	 * @param JORMDatabaseQuery reference
	 * @since 11.1
	 */
	public static function createInstance(array $options,JORMDatabaseQuery $reference = null)
	{
		$instance = new JORMDatabaseQuery();
		
		//check default options
		$instance->_options($options);
		
		//initialize vars
		$instance->_name = $options['name'];
		$instance->_fields = $options['fields'];
		$instance->_tbl_prefix = $options['tbl_prefix'];
		$instance->_tbl = $options['tbl'];
		$instance->_tbl_alias = $options['tbl_alias'];
		$instance->_jtable = $options['jtable'];
		$instance->_foreign_tbls = $options['foreign_tbls'];
		$instance->_references = $options['references'];
		
		//Initialize
		$instance->_initialize();
		
		//Create select
		if( is_object($reference) )
		{
			$instance->_query = &$reference->_query;
			
			$instance->_autoJoin($reference);
			//Create a reference to back to scope
			$instance->addReference($reference->getName(),get_class($reference));
		}
		else {
			$instance->_createSelect();
		}
			
		
		return $instance;
	}
	
	/**
	 * Create a reference to another JORMDatabaseQuery Object
	 * 
	 * @param string $alias
	 * @param string|array|JORMDatabaseQuery Object $config
	 */
	public function addReference($alias,$config)
	{
		//clean alias name
		$alias	= preg_replace('/[^A-Z0-9_]/i', '', $alias);
		
		//check array
		if( is_array($config) )
			$config = $this->_options($config);
		//check object
		if( is_object($config) ){
			JORMDatabaseQueryException::checkObjectSubclass($config);
		}
		
		$this->_references[$alias] = $config;
		
		return $this;
	} 
	
	/**
	 * Check default options variables
	 * 
	 * @param array $options
	 */
	private function _options(array &$options)
	{
		$default_options = array(
			//name
			'name' => '',
			//select fields
			'fields' => array(),
			//table prefix
			'tbl_prefix' => '',
			//table alias
			'tbl_alias' => null,
			//reference to anothers
			'references' => array(),
			//foreign tables
			'foreign_tbls' => array(),
			//jtable config
			'jtable' => array(
				'type' => null,
				'prefix' => 'JTable',
				'tbl' => '',
				'tbl_key' => '',
				'db' => $this->_db
			)
		);
		
		foreach($default_options as $default_option_key => $default_option_value)
		{
			//set default option
			if( !isset($options[$default_option_key]) || empty($options[$default_option_key]) )
			{
				$options[$default_option_key] = $default_option_value;
			}
			
			if( is_array($default_options[$default_option_key]) && !empty($default_options[$default_option_key]) && !empty($options[$default_option_key]) )
			{
				foreach($default_options[$default_option_key] as $arr_option_key => $arr_option_value)
				{
					//set default jtable prefix config
					if(!isset($options[$default_option_key][$arr_option_key]))
						$options[$default_option_key][$arr_option_key] = $arr_option_value;
				}
			}
			
			//check references
			if( !empty($options[$default_option_key]) && $default_option_key == 'references' && is_array($options[$default_option_key]) ){
				foreach($options[$default_option_key] as $reference){
					$this->_options($reference);
				}
			}
		}
	}
	
	/**
	 * This function will build a select on table
	 * 
	 * @since 11.1
	 */
	private function _createSelect()
	{
		if( empty($this->_fields) && empty($this->_tbl) ) return;
		
		$tmp_fields = $this->_fields;
		foreach($tmp_fields as &$field)
			$field = $this->_addAliasToField($field);
		
		$this->_query->select($tmp_fields)->from($this->_getTable());
	}
	
	/**
	 * Return complete table name and alias or only table name/alias 
	 * 
	 * @param boolean mode
	 * @since 11.1
	 */
	private function _getTable($mode=false)
	{
		$table = $this->_tbl_prefix . $this->_tbl;
		if($mode){			
			if( !empty($this->_tbl_alias) ) $table = $this->_tbl_alias;
			return $table;
		}
		
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
		if( !array_key_exists($reference->_tbl, $this->_foreign_tbls) ) return;
		
		$foreign = $this->_foreign_tbls[$reference->_tbl];
		
		$join_type 		= $foreign['jointype'];
		$join_columns 	= $foreign['joincolumn'];
		$columns 		= $foreign['column'];
		$conditions = $this->_getTable();
		
		$arrJoinColumns = array();
		if( array_key_exists(0, $join_columns) )
		{
			foreach($join_columns as $join_column){
				$arrJoinColumns[] = $reference->_getTable(true).$join_column['name'].' = '.$this->_getTable(true).$join_column['referencedColumnName'];
			}
		}
		else{
			$arrJoinColumns[] = $reference->_addAliasToField($join_columns['name']).' = '.$this->_addAliasToField($join_columns['referencedColumnName']);
		}
		
		$conditions .= ' ON ('.implode(' AND ',$arrJoinColumns).')';
		
		//create join type
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
		
		//add columns to select
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
		$columns = $this->_db->getTableColumns($this->_tbl_prefix.$this->_tbl);
		
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
		if( !empty($this->_jtable) && is_array($this->_jtable) )
		{
			$this->_instanceJTable($this->_jtable);
		}
	}
	
	/**
	 * Create or get instance of JTable class
	 * 
	 * @param array Config
	 * @since 11.1
	 */
	public function _instanceJTable(array $config)
	{
		if(!empty($config['tbl_key']) && !empty($config['tbl']) && ($config['db'] instanceof JDatabase))
		{
			$jtable = new JORMDatabaseTable($config['tbl'], $config['tbl_key'], $config['db']);
		}
		else if(isset($config['type']) && !empty($config['type']) && isset($config['prefix']) && !empty($config['prefix'])){
			$jtable = JORMDatabaseTable::getInstance($config['type'],$config['prefix']);
		}
		else {
			$jtable = $config;
		}
		
		$this->_jtable = $jtable;
		
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
		var_dump($property);
		
		if(isset($this->$property)){
			$this->$property = $value;
		}
		else{
			exit;
			if(!($this->_jtable instanceof JTable)) throw new Exception(JText::_('You must set JTable Class'),500);
			
			$this->_jtable->set($property,$value);
		}
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
		
		//check to call another instance
		$return = $this->_callReference($method);
		if(is_object($return)){
			return $return;
		}
		
		//check if method is a field
		$return = $this->_callField($method, $arguments);
		if(is_object($return)){
			return $return;
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
		
		JORMDatabaseQueryException::callMethodNotExists($method,$this);
	}
	
	/**
	 * Checking referenced config and return a JORMDatabaseQuery object when exists, or false
	 * 
	 * @param string, object, array $method
	 * @throws Exception
	 * @return JORMDatabaseQuery object or FALSE
	 * @since 11.1
	 */
	final function _callReference($method)
	{
		//check if method is a reference
		if( array_key_exists($method, $this->_references) ){
			$reference_data = $this->_references[$method];
			
			/**
			 * If reference is a string try to get instance
			 */
			if( is_string($reference_data) && !class_exists($reference_data) )
				$reference = self::getInstance($reference_data,$this);
			/**
			 * If reference is an array create a new instance
			 */
			else if( is_array($reference_data) ){
				$reference_data['name'] = $method;
				$reference = self::createInstance($reference_data,$this);
			}
				
			/**
			 * Check object class
			 */
			JORMDatabaseQueryException::checkObjectSubclass($reference);
			
			return $reference;
		}
		
		return false;
	}
	
	/**
	 * Retrun field with table name or table alias
	 * 
	 * @param string $field
	 */
	private function _addAliasToField($field)
	{
		return $this->_getTable(true).'.'.$field;
	}
	
	/**
	 * Check if call method is a table field and return self if exists, else returns false
	 * 
	 * @param string $method
	 * @param array $arguments
	 * @return Object or Boolean
	 */
	protected function _callField($method,$arguments)
	{
		$count_arguments = count($arguments);
		
		$field_key = array_search($method, $this->_fields);
		
		//check if exists on fields list
		if( array_search($method, $this->_fields) !== false ){
			$string = $this->_addAliasToField($method);
			
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
		
		return false;
	}
}