<?php
	/**
	 * Prepares the php environment for execution of CAS.
	 *
	 * @param $setupMode boolean Prepares environment for setup purposes
	 * 								therefore ignores most errors.
	 * 
	 * @throws Exception
	 */
	function initializeEnvironment()
	{
		global $dataPath, $globalDataPath;
		global $configType;

		ini_set('memory_limit', '32M');

		// Make sure the dataPath is a valid directory!
		if(!is_dir($dataPath))
			throw new Exception("Het lokale deel van de CAS installatie kan niet worden gevonden.");
			
		if(!is_dir($globalDataPath))
			throw new Exception("Het gedeelde deel van de CAS installatie kan niet worden gevonden.");

		if(array_key_exists('OS', $_ENV) && substr($_ENV['OS'], 0, 7) == 'Windows')
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
	
		if(!function_exists("simplexml_load_file"))
			throw new Exception("Kan de SimpleXML module niet vinden.");

		if(!file_exists($configFilename)) {
			header("Location: setup.php");
			throw new Exception("Configuratie bestand kan niet worden gevonden, gebruik eerste het setup programma (setup.php).");
		}
	
		// Includes the Zend framework
		@include_once 'Zend/Loader.php';
	
		if(!class_exists('Zend_Loader'))
			throw new Exception("Het Zend Framework kan niet worden geladen.");

		// Load the configuration file
		Zend_Loader::loadClass("Zend_Config");
		Zend_Loader::loadClass("Zend_Config_Xml");
		Zend_Loader::loadClass("Zend_Registry");
		Zend_Loader::loadClass("CT_Exception_Unauthorized");

		try {
			$config = new Zend_Config_Xml($configFilename, $configType);			
		} catch(Exception $e) {
			$config = new Zend_Config(array(
				"log_file" => "/tmp/cas.log", 
				"temp_dir" => "/tmp"), true);
		}

		Zend_Registry::set('config', $config);

		session_save_path(getResourceLocation($config->temp_dir, false));
		date_default_timezone_set($config->get('timezone', 'Europe/Amsterdam'));

		initLogger($config);

		Zend_Loader::loadClass('CT_Exception_Database');

		if(!initDatabase($config)) 
			throw new CT_Exception_Database("Kan de database niet initializeren. Controleer of het CAS account bestaat op de database server.");
			
		if(!initFormatter($config)) 
			throw new Exception("Kan de bedrijfs-specifieke formaat generator niet initializeren.");		
	}

	
	/**
	 * Starts the CAS-application.
	 *
	 * @throws Exception
	 */
	function startApplication()
	{
		global $globalDataPath;
	
		try {
			Zend_Loader::loadClass("Zend_Controller_Front");
			Zend_Loader::loadClass("Zend_Controller_Router_Rewrite");
	
			$config = Zend_Registry::get('config');	
	
			$router = new Zend_Controller_Router_Rewrite();
			$ctrl = Zend_Controller_Front::getInstance();
			$ctrl->setBaseUrl($config->rewrite_base);
			$ctrl->setRouter($router);
	
			$front = Zend_Controller_Front::getInstance();
			$front->setParam("noErrorHandler", false);
			$front->setParam("noViewRenderer", true);
			$front->throwExceptions(true);
	
			Zend_Controller_Front::run(joinDirStrings($globalDataPath, '/controllers'));
	
		} catch(CT_Exception_Unauthenticated $e) {
			displayLoginScreen();
		} catch(CT_Exception_Unauthorized $e) {
			throw new Exception('Helaas, u bent niet bevoegd deze actie uit te voeren!');
		} catch(Exception $e) {
			throw $e;
		}	
	}


	/**
	 * Writes an error message to standard out.
	 *
	 * @param string $message Error message to display.
	 */
	function displayInitError($message) 
	{
		try {
			if(!class_exists("Zend_Loader"))
				throw new Exception($message);

			Zend_Loader::loadClass('CT_Smarty');

			$smarty = new CT_Smarty(Zend_Registry::get("config"), NULL);
			$smarty->assign("message", $message);
			$smarty->display("error.tpl");
		} catch(Exception $e) {
			header("Content-type: text/plain");
			echo("Het volgende probleem is opgetreden tijdens het starten van CAS:\n");
			
			$msgLines = wrapline($message, 75);
			
			foreach($msgLines as $line)
				echo($line . "\n");
				
			echo("\n--\n\nDaarnaast kan de HTML-weergave niet worden gebruikt wegens een (mogelijk gerelateerd) probleem:\n");

			$msgLines = wrapline($e->getMessage(), 75);
			
			foreach($msgLines as $line)
				echo($line . "\n");
			
			exit(1);
		}
	}


	/**
	 * Writes a copy of the login screen to standard out.
	 */
	function displayLoginScreen() 
	{
		try {
			Zend_Loader::loadClass("CT_Smarty");

			$smarty = new CT_Smarty(Zend_Registry::get("config"), Zend_Registry::get("formatter"));
			$smarty->assign("message", $message);
			$smarty->display("login.tpl");
		} catch(Exception $e) {
			throw new Exception("Kan het login scherm niet weergeven.");
		}
	}


	/**
	 * Initializes basic logging.
	 *
	 * @param Zend_Config @config Configuration object containing log location.
	 * @return Zend_Log
	 */
	function initLogger($config)
	{
		global $dataPath;

		Zend_Loader::loadClass('Zend_Log');
		Zend_Loader::loadClass('Zend_Log_Writer_Stream');	

		try {
			$logFile = getResourceLocation($config->get('log_file', 'tmp/cas.log'));
		} catch(Exception $e) {
			$logFile = joinDirStrings($dataPath, $config->get('log_file', 'tmp/cas.log'));

			$file = @fopen($logFile, 'w');
			
			if($file == 0) {
				$userinfo = posix_getpwuid(posix_geteuid());
				
				throw new Exception("Het logbestand (" . realpath($logFile) . ") kan niet worden " .
					"gemaakt. Controlleer op de webserver of de CAS gebruiker (" . $userinfo['name'] .
					") voldoende rechten heeft.");
			}

			fprintf($file, "Logfile created on %s\n", date("Y-m-d h:m:s"));
			fclose($file);
		}
		
		// Setup logging facility
		$writer = new Zend_Log_Writer_Stream($logFile);

		$logger = new Zend_Log($writer);
		Zend_Registry::set('logger', $logger);

		return $logger;
	}


	/**
	 * Initializes the database and adds it to the Zend_Registry.
	 *
	 * @param Zend_Config $config Configuration object containing database settings.
	 * @return Zend_Db Initialized database
	 */
	function initDatabase($config)
	{
		// Initialize the database connection
		try {
			Zend_Loader::loadClass("Zend_Db");
			Zend_Loader::loadClass("Zend_Db_Table");

			$database = Zend_Db::factory($config->database->type, array(
				"host"     => $config->database->host,
				"username" => $config->database->username,
				"password" => $config->database->password,
				"dbname"   => $config->database->name));

			$database->getConnection();

			Zend_Registry::set('database', $database);
			Zend_Db_Table::setDefaultAdapter($database);
		} catch(Exception $e) {

			$logger = Zend_Registry::get('logger');
			$logger->log('[Initializatie] : ' . $e->getMessage(), Zend_Log::CRIT);

			return false;
		}

		return $database;
	}


	/**
	 * Initializes the style (formatter) class.
	 *
	 * @param Zend_Config $config Configuration object containing the name of the formatter to use.	 
	 * @return CT_Formatting_Formatter
	 */
	function initFormatter($config)
	{
		try {
			Zend_Loader::loadClass("CT_Formatting_Factory");

			$formatter = CT_Formatting_Factory::getFormatter
				($config->branding->get('formatter', 'Default'));

			Zend_Registry::set("formatter", $formatter);
		} catch(Exception $e) {
			return false;
		}

		return $formatter;
	}
