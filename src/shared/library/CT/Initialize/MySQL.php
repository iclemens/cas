<?php
	/**
	 * Database update functionality for CAS (MySQL specifics)
 	 *
 	 * PHP version 5
 	 *
 	 * @author     Ivar Clemens <post@ivarclemens.nl>
 	 * @copyright  2008 Ivar Clemens
	 * @package    boekhouding
	 */

	Zend_Loader::loadClass('Zend_Db');
	Zend_Loader::loadClass('CT_Db_Versie');
	Zend_Loader::loadClass('CT_Initialize');

	class CT_Initialize_MySQL extends CT_Initialize
	{
		
		/**
		 * Initialize names of MySQL specific directories.
		 * 
		 * @param string $location The base locations of SQL updates.
		 */
		public function __construct($location)
		{
			$this->installLocation = getResourceLocation(
				joinDirStrings($location, 'mysql-install'));				

			$this->updateLocation = getResourceLocation(
				joinDirStrings($location, 'mysql-update'));
		}


		/**
		 * Creates a new connection to the MySQL database.
		 * 
		 * @throws Exception If the connection cannot be established.
		 * 
		 * @param array $creds The database credentials
		 * @param string $role Selects which username/password combination to use
		 *
		 * @return Zend_Db Database connection 					
		 */
		private function setupConnection($creds, $dbname = null, $role = 'admin')
		{
			$config = Zend_Registry::get('config');
			
			if($dbname == null)
				$dbname = $config->database->name;
			
			// Connect to the database
			try {
				$database = Zend_Db::factory($config->database->type, array(
					"host"     => $config->database->host,
					"username" => $creds[$role . '_username'],
					"password" => $creds[$role . '_password'],
					"dbname"   => $dbname));
					
				$database->getConnection();

				return $database;
			} catch(Exception $e) {
				throw new Exception('Kan de database verbinding niet tot stand brengen, klopt het wachtwoord?');
			}			
		}


		/**
		 * Creates a new database and user for Project CAS.
		 * 
		 * @param array $creds Administrator credentials.
		 */
		public function createDatabase($creds)
		{
			$config = Zend_Registry::get('config');			
			$database = $this->setupConnection($creds, 'mysql', 'admin');		
					
			$result = $database->fetchAll(
				$database->quoteInto('SELECT schema_name FROM information_schema.schemata WHERE schema_name = ?', $config->database->name));
			
			if(count($result) != 0) {
				throw new Exception('De database bestaat al, het overschrijven van bestaande databases wordt niet ondersteund.');
			} else {				
				$database->query('CREATE DATABASE IF NOT EXISTS ' . $config->database->name . ' DEFAULT CHARACTER SET utf8 DEFAULT COLLATE utf8_unicode_ci'); 
			}

			$database->query($database->quoteInto(
				'GRANT ALL PRIVILEGES ON ' . $config->database->name . '.* ' .
				"TO '" . $config->database->username . 
				"'@'localhost' IDENTIFIED BY ?", $config->database->password));
			
			// Only add non-local account when mysql is not running
			//  on the webserver.
			if($config->database->host != 'localhost') {	
				$database->query($database->quoteInto(
					'GRANT ALL PRIVILEGES ON ' . $config->database->name . '.* ' .
					"TO '" . $config->database->username . 
					"'@'%' IDENTIFIED BY ?", $config->database->password));
			}

			$database->query('FLUSH PRIVILEGES');			
		}


		/**
		 * Creates database tables in the selected database.
		 * 
		 * @param array $creds The database credentials to use 
		 */
		public function createTables($creds)
		{
			$config = Zend_Registry::get('config');			
			$database = $this->setupConnection($creds, null, 'creator');

			$scriptName = joinDirStrings($this->installLocation, 'create-tables.sql');
			$script = $this->readMySQLScript($scriptName);

			try {
				$database->beginTransaction();

				foreach($script as $line) {
					$database->query($line);
				}

				$database->commit();
			} catch(Exception $e) {
				echo($e->getMessage()); exit(1);
				$database->rollBack();
				throw new Exception('Het aanmaken van de tabellen is mislukt!');
			}
		}

		/**
		 * Removes empty lines from an array.
		 *
		 * @param array $lines Array to remove the empty lines from.
		 *
		 * @return array Array without the empty lines.
		 */
		private function removeEmptyLines($lines)
		{
			$tmp = array();

			foreach($lines as $line) {
				$length = strlen($line);

				for($i = 0; $i < $length; $i++) {
					$ch = substr($line, $i, 1);

					if(!ctype_space(substr($line, $i, 1))) {
						$tmp[] = $line;
						break;
					}
				}
			}

			return $tmp;
		}


		/**
		 * Reads a MySQL script and returns each statement as a
		 * seperate entry of an array.
		 *
		 * @param string $filename Name of the script to read.
		 *
		 * @return array The SQL script
		 */
		public function readMySQLScript($filename)
		{
			$script = file($filename);

			// Strip comments
			foreach($script as &$line) {
				$commentStart = strpos($line, '--');

				if($commentStart === false)
					continue;

				if($commentStart == 0) {
					$line = '';
				} elseif($commentStart > 0) {
					$line = substr($line, 0, $commentStart);
				}
			}

			$scriptString = implode(' ', $script);
			$script = explode(';', $scriptString);

			return $this->removeEmptyLines($script);
		}


		/**
		 * Applies a single patch to the database.
		 * 
		 * @param string $patchName Filename of the patch to install
		 */
		public function applySinglePatch($patchName)
		{
			$database = Zend_Registry::get('database');
			$filename = joinDirStrings($this->updateLocation, $patchName);
			$patch = $this->readMySQLScript($filename);
						
			try {
				$database->beginTransaction();

				foreach($patch as $line)
					$database->query($line);

				CT_Db_Versie::setVersion(substr($patchName, 0, 12));

				$database->commit();
			} catch(Exception $e) {
				$database->rollBack();				
				throw new Exception('Het bijwerken van de MySQL database is mislukt.');
			}
		}
	}
