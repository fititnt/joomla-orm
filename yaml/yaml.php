<?php
/**
 * @package     Joomla.Platform
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * JORM YAML
 *
 * Class to work with yaml files
 *
 * @package     Joomla.Platform
 * @subpackage  Yaml
 * @since       11.1
 * @tutorial	Joomla.Platform/jormyaml.cls
 * @link		http://docs.joomla.org/JORMYaml
 */
class JORMYaml
{
	/**
	 * Load a YAML file
	 * 
	 * @param string $filename
	 * @since 11.1
	 */
	static function loadFile($filename)
	{
		return spyc_load_file($filename);
	}
	
	/**
	 * Find YAML file
	 * 
	 * Return full path file
	 * 
	 * @param array $path
	 * @param string $filename
	 * @since 11.1
	 */
	static function findfile($path,$filename)
	{
		if ($path = JPath::find(self::addIncludePath(), strtolower($filename).'.yml')) {
			// Import the class file.
			return $path;
		}
		else {
			// If we were unable to find the class file in the YAML include paths, raise a warning and return false.
			JError::raiseWarning(0, JText::sprintf('JORMLIB_YAML_ERROR_NOT_SUPPORTED_FILE_NOT_FOUND', $filename));
			return false;
		}
	}
	
	/**
	 * Add a filesystem path where should search for yaml files.
	 * You may either pass a string or an array of paths.
	 *
	 * @param   mixed  A filesystem path or array of filesystem paths to add.
	 *
	 * @return  array  An array of filesystem paths to find YAML file in.
	 *
	 * @link    http://docs.joomla.org/JORMYaml/addIncludePath
	 * @since   11.1
	 */
	public static function addIncludePath($path = null)
	{
		// Declare the internal paths as a static variable.
		static $_paths;

		// If the internal paths have not been initialised, do so with the base table path.
		if (!isset($_paths)) {
			$_paths = array();
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
}