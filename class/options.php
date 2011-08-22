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
class JClassOptions extends JObject
{
	private $_default = array();
	
	/**
	 * Create recived options array properties and set by default settings
	 * 
	 * @param array $options
	 */
	public function __construct(array $options = array())
	{
		//set default config
		$this->_default = $options;
	}
	
	public function santizeOptions($options,$compareOptions = null)
	{
		if( empty($this->_default) || !is_array($compareOptions) ) return $options;
		if( is_null($compareOptions) ) $compareOptions = $this->_default;;
		
		foreach($compareOptions as $default_key => $default_value)
		{
			if( !property_exists($this, $default_key) )
			{
				$this->$default_key = $default_value;
			}
			
			if( is_array($options[$default_key]) && !empty($options[$default_key]) ){
				$options[$default_key] = $this->santizeOptions($options[$default_key],$this->_default[$default_key]);
			}
		}
		
		return $options;
	}
	
	/**
	 * Set options array
	 * 
	 * @return self instance
	 */
	public function setOptions()
	{
		$options = $this->santizeOptions(func_get_args());
		call_user_method_array('set', $this, $options);
		
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