<?php
	/**
	 * Determines the first step of the CAS setup
	 * procedure which has not yet been completed.
	 *
	 * @return integer The current step
	 */
	function determineSetupPhase()
	{
		global $dataPath;
		global $globalDataPath;
		global $configType;
		
		if(!file_exists('config.php'))
			return 1;

		// Loads $dataPath and $globalDataPath into the _global_ namespace!
		require_once('config.php');

		if(!file_exists($dataPath . '/config/config.xml'))
			return 2;

		// Check database
		require_once($globalDataPath . '/library/initialization.php');
		
		try {
			initializeEnvironment();
		} catch(CT_Exception_Database $e) { // Database not properly setup
			return 3;
		} catch(Exception $e) { // Error not related to the database
			displayInitError($e->getMessage());
		}		
		
		// Check tables
		$result = Zend_Registry::get('database')->query('SHOW TABLES');
		$tables = $result->fetchAll();
		
		foreach($tables as &$table)
			$table = array_pop($table);

		if(in_array('klanten', $tables) && !in_array('versie', $tables))
			return -1;

		if(!in_array('versie', $tables))
			return 4;
		
		return 5;
	}
