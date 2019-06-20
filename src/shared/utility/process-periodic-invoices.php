<?php
	/**
	 * Citrus-IT Administratie Systeem Start Script
	 *
	 * @package boekhouding
	 */

	/**
	 * Provides location of private resources (invoices &c.)
	 * @var string
	 */
	$dataPath = "/home/ivar/Projects/cas/trunk/generic";

	/**
	 * Provides location of global resources
	 * @var string
	 */
	$globalDataPath = "/home/ivar/Projects/cas/trunk/shared";

	/**
	 * Determines which configuration to use, should be set to
	 * either development or production.
	 * @var string
	 */
	$configType = "development";

	/****************************************************************************
	 * Modification of this file should be limited to the above variables ONLY! *
	 *   All other configuration options should be set in the config.xml file   *
	 ****************************************************************************/

	/** 
		THE FOLLOWING CODE SETS UP THE ENVIRONMENT AND CHECKS SOME BASIC STUFF
			**/

	ini_set("memory_limit","32M");

	// Some versions of smarty do not work with E_STRICT
	// error_reporting(E_STRICT);

	$requestTime = microtime(true);

	// Make sure the dataPath is a valid directory!
	if(!is_dir($dataPath) || !is_dir($globalDataPath)) {
		echo("Error: Resource directory could not be located\n");
		exit(1);
	}

	if(substr($_ENV['OS'], 0, 7) == 'Windows')
		$INCLUDE_SEPARATOR = ';';
	else
		$INCLUDE_SEPARATOR = ':';

	ini_set('include_path', ini_get('include_path') . 
		$INCLUDE_SEPARATOR . $dataPath . '/library/' .
		$INCLUDE_SEPARATOR . $globalDataPath . '/library/' . 
		$INCLUDE_SEPARATOR . $globalDataPath . "/library/Zend/");

	$configFilename = $dataPath . "/config/config.xml";

	@include_once 'utility.php';
	@include_once 'initialization.php';

	if(!function_exists("simplexml_load_file")) {
		echo("Error: Simplexml PHP extension could not be found\n");
		exit(1);
	}

	if(!file_exists($configFilename)) {
		echo("Error: Configuration file could not be located\n");
		exit(1);
	}

	/**
		THE FOLLOWING CODE INITIALIZES KEY COMPONENTS
			**/

	// Includes the Zend framework
	@include_once 'Zend/Loader.php';

	if(!class_exists('Zend_Loader')) {
		echo("Error: ZendFramework could not be loaded\n");
		exit(1);
	}

	// Load the configuration file
	Zend_Loader::loadClass("Zend_Config");
	Zend_Loader::loadClass("Zend_Config_Xml");
	Zend_Loader::loadClass("Zend_Registry");
	Zend_Loader::loadClass("CT_Exception_Unauthorized");

	try {
		$config = new Zend_Config_Xml($configFilename, $configType);
		Zend_Registry::set('config', &$config);
	} catch(Exception $e) {
		echo("Error: Configuration file could not be parsed\n");
		exit(1);
	}

	if(!initLogger($config)) {
		echo("Kan de logging module niet initializeren\n");
		exit(1);
	}

	if(!initDatabase($config)) {
		echo("Kan de database niet initializeren\n");
		exit(1);
	}

	if(!initFormatter($config)) {
		echo("Kan de bedrijfs-specifieke formaat generator niet initializeren\n");
		exit(1);
	}

	Zend_Loader::loadClass('CT_Db_Periodiekeregels');

	$table = new CT_Db_Periodiekeregels();
	$table->processPeriodicInvoices();
