<?php
/**
 * import all framework classes
 * 
 * @since 11.1
 */

$basePath = dirname(__FILE__);

JLoader::import('inflector.inflector',$basePath);
JLoader::import('database.helpers.abstract',$basePath);
JLoader::import('database.databasequeryexception',$basePath);
JLoader::import('database.databasequeryhelper',$basePath);
JLoader::import('database.table',$basePath);
JLoader::import('database.databasequery',$basePath);

//load language
JFactory::getLanguage()->load('lib_jorm');