<?php
/**
 * @package     Joomla.Platform
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.base.object');

/**
 * JClass Option
 *
 * Options Class based on Mootools Class.Extras Options
 *
 * @package     Joomla.Platform
 * @subpackage  Class
 * @since       11.1
 * @tutorial	Joomla.Platform/jclassoption.cls
 * @link		http://docs.joomla.org/JClassOption
 */
class JORMClassOptions extends JObject
{
	private $_default = array();
	
	/**
	 * Create recived options array properties and set by default settings
	 * 
	 * @param array $options
	 * @since 11.1
	 */
	public function __construct(array $options = array())
	{
		//set default config
		$this->_default = $options;
	}
	
	/**
	 * Merge options with default array
	 * 
	 * @since 11.1
	 */
	public function santizeOptions($options,$compareOptions = null)
	{
		if( empty($this->_default) || !is_array($compareOptions) ) return $options;
		if( is_null($compareOptions) ) $compareOptions = $this->_default;;
		
		return array_merge_recursive($options,$compareOptions);
	}
	
	/**
	 * Set options array
	 * 
	 * @return self instance
	 * @since 11.1
	 */
	public function setOptions($options)
	{
		$options = $this->santizeOptions($options);
		
		foreach($options as $option_key => $option_value){
			$this->set($option_key,$option_value);
		}
		
		return $this;
	}
	
	/**
	 * Check if property exists
	 * 
	 * @param $property
	 * @return Returns TRUE if the property exists, FALSE if it doesn't exist or NULL in case of an error. 
	 */
	function hasProperty($property)
	{
		return property_exists($this,$property);
	}
}