<?php
	Zend_Loader::loadClass("Zend_Config");
	Zend_Loader::loadClass("Zend_Config_Xml");
	Zend_Loader::loadClass("Zend_Registry");
	Zend_Loader::loadClass("Zend_Db");
	Zend_Loader::loadClass("Zend_Db_Table");



	require_once 'PHPUnit/Framework.php';

	class CT_Db_TestCase extends PHPUnit_Framework_TestCase {
		protected $database = null;



		/**
		 * Sets up a database connection for use in the UnitTest
		 */
		protected function setupDatabase() {
			try {
				$config = Zend_Registry::get('config');
			} catch(Exception $exception) {
				$config = new Zend_Config_Xml("../config.xml", "development");
				Zend_Registry::set('config', &$config);
			}

			try {
				$this->database = Zend_Registry::get('database');
			} catch(Exception $exception) {
				$config = Zend_Registry::get('config');
	
				$this->database = Zend_Db::factory($config->database->type, array(
					"host"     => $config->database->host,
					"username" => $config->database->username,
					"password" => $config->database->password,
					"dbname"   => $config->database->name));

				Zend_Registry::set("database", &$this->database);
				Zend_Db_Table::setDefaultAdapter($this->database);
			}
		}

		/**
	   * Clears all tables, makes testing easier
		 */
		protected function clearTables() {
			$this->database->query("DELETE FROM betalingen");
			$this->database->query("ALTER TABLE betalingen AUTO_INCREMENT = 0");

			$this->database->query("DELETE FROM facturen");
			$this->database->query("ALTER TABLE facturen AUTO_INCREMENT = 0");

			$this->database->query("DELETE FROM factuurregels");
			$this->database->query("ALTER TABLE factuurregels AUTO_INCREMENT = 0");

			$this->database->query("DELETE FROM factureren");
			$this->database->query("ALTER TABLE factureren AUTO_INCREMENT = 0");

			$this->database->query("DELETE FROM gebruikers");
			$this->database->query("ALTER TABLE gebruikers AUTO_INCREMENT = 0");

			$this->database->query("DELETE FROM herinneringen");
			$this->database->query("ALTER TABLE herinneringen AUTO_INCREMENT = 0");

			$this->database->query("DELETE FROM klanten");

			$this->database->query("DELETE FROM klantnotities");
			$this->database->query("ALTER TABLE klantnotities AUTO_INCREMENT = 0");
		}



	}

?>
