<?php
/**
 * @package     Joomla.Platform
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * JORM Inflector class
 *
 * Inflector class
 *
 * @package     Joomla.Platform
 * @subpackage  Inflector
 * @since       11.1
 * @tutorial	Joomla.Platform/jorminflector.cls
 * @link		http://docs.joomla.org/JORMInflector
 */
abstract class JORMInflector
{
	/**
	 * List of countables fields.
	 *
	 * @var    Array
	 * @since  11.1
	 */
	static private $_contable = array(
	);
	
	/**
	 * Return true if word is countable.
	 *
	 * @var    Array
	 * @since  11.1
	 */
	static function countable($word)
	{
		return (array_search($word,self::$_contable) !== false) ? true : false ;
	}
	
	/**
	 * undescore a word
	 *
	 * @var    Array
	 * @since  11.1
	 */
	static function underscore($word)
	{
		$word = preg_replace('/(\s)+/', '_', $word);
		$word = strtolower(preg_replace('/(?<=\\w)([A-Z])/', '_\\1', $word));
		return $word;
	}
	
	/**
	 * Convert a word to table format
	 *
	 * @var    Array
	 * @since  11.1
	 */
	static function tabelize($word)
	{	
		return self::underscore($word);
	}
	
	/**
	 * Add a conutable word
	 *
	 * @var    Array
	 * @since  11.1
	 */
	static function addCountable($word)
	{
		array_push(self::$_contable, $word);
	}
}