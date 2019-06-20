<?php
	/**
	 * Citrus-IT Administratie Systeem UnitTests Start Script
	 *
	 * This code creates an environment very similar to that
	 * created by index.php.
	 *
	 * @package boekhouding
	 */

	/**
	 * Provides location of private resources (invoices &c.)
	 * @var string
	 */
	$dataPath = getcwd() . '/../../generic/';

	/**
	 * Provides location of global resources
	 * @var string
	 */
	$globalDataPath = getcwd() . '/../';

	/**
	 * Determines which configuration to use, should be set to development
	 * @var string
	 */
	$configType = "development";

	ini_set("memory_limit","128M");

	ini_set('include_path', ini_get('include_path') . ':' . 
			$dataPath . '/library' . ':' . 
			$globalDataPath . '/library' . ':' .
			$globalDataPath . '/library/Zend/' . ':' .
			$globalDataPath . '/controllers' . ':' . 
			$globalDataPath . '/tests');

	$configFilename = $dataPath . "/config/config.xml";

	include_once 'Zend/Loader.php';
	include_once 'utility.php';
	include_once 'initialization.php';	
	include_once 'utility/CT_TestCase.php';

	// Load the configuration file
	Zend_Loader::loadClass("Zend_Config");
	Zend_Loader::loadClass("Zend_Config_Xml");
	Zend_Loader::loadClass("Zend_Registry");

	try {
		$config = new Zend_Config_Xml($configFilename, $configType);
		Zend_Registry::set('config', &$config);
	} catch(Exception $e) {
		echo("Error: " . $e->getMessage());
		exit(1);
	}

	include_once 'utility/utility.php';

	if(!initLogger($config)) {
		echo("Kan de logging module niet initializeren. ");
		exit(1);
	}

	if(!initDatabase($config)) {
		echo("Kan de database niet initializeren. ");
		exit(1);
	}

	if(!initFormatter($config)) {
		echo("Kan de bedrijfs-specifieke formaat generator niet initializeren.");
		exit(1);
	}

	resetAllTables();
	addSampleUsers();
