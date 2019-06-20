<?php
	/**
 	 * Citrus-IT User authentication and permissions module.
 	 *
 	 * @author     Dirk Teurlings <dirk@citrus-it.nl>
 	 * @author     Ivar Clemens <ivar@citrus-it.nl>
	 * @copyright  2005-2006 Dirk Teurlings, 2007 Citrus-IT
 	 * @package 	 boekhouding
	 */

	Zend_Loader::loadClass('CT_Db_Gebruikers');

	/**
	 * User authentication class
	 *
	 * @package		boekhouding
	 */
	class CT_User 
	{

		const Directie = 1;
		const Boekhouding = 2;
		const Klant = 3;

	
		/**
		 * Current instance of the CT_User class
		 *
		 * Static variable for use by the singleton implementation
		 *
		 * @var inst
		 */
		static private $inst = null;

		/**
		 * Holds user information retreived from database
		 *
		 * @var user_info
		 */
		private $user_info = array();

		/**
		 * Holds an instance of Zend_Db
		 *
		 * @var database
		 */
		private $database;

		/**
		 * Returns the instance of the CT_User class
		 *
		 * @param Zend_Database	An instance of the Zend_Database class
		 * @return CT_User		The instance of the CT_User class
		 */
		static function instance(&$database) 
		{
			if(CT_User::$inst == null) {
				@session_start();
				CT_User::$inst = new CT_User($database);
			}

			return CT_User::$inst;
		}

		function __construct(&$database) 
		{
			$this->user_info = array();

			if($this->isLoggedIn()) {
				$volgnummer = intval($_SESSION['user_id']);

				$ct_gebruikers = new CT_Db_Gebruikers();
				$gebruiker = $ct_gebruikers->find($volgnummer);

				if($gebruiker == false)
					throw new Exception("Er is een fout opgetreden bij het inloggen");

				$gebruikerArray = $gebruiker->toArray();

				if(count($gebruikerArray) != 1)
					throw new Exception("Er is een fout opgetreden bij het inloggen");

				$this->user_info = $gebruikerArray[0];
			}
		}

		/**
		 * Authenticates a user
		 *
 		 * @param string 		$username		The username
		 * @param string 		$password		The password 
		 *
		 * @return bool			TRUE on success, FALSE otherwise.
		 */
		public function login($username, $password) 
		{
			if($this->isLoggedIn())
				$this->logout();

			$password = md5($password) . sha1($password);

			$ct_gebruikers = new CT_Db_Gebruikers();
			$db = $ct_gebruikers->getAdapter();	

			$where = $db->quoteInto("gebruikersnaam = ? ", $username)
						 . $db->quoteInto("AND wachtwoord = ? ", $password)
						 . "AND actief = 1";

			$gebruikers = $ct_gebruikers->fetchAll($where);

			if($gebruikers->count() != 1)
				return false;

			$row = $gebruikers->current()->toArray();

			$this->user_info = $row;

			$_SESSION['logged_in'] = true;
			$_SESSION['user_id'] = $row['volgnummer'];
			$_SESSION['group_id'] = $row['type'];
			$_SESSION['browser_hash'] = md5($_SERVER['HTTP_USER_AGENT'] . $_SERVER['REMOTE_ADDR']);

			return true;
		}

		/**
		 * Resumes the current session
		 */
		public function init()
		{
			if(array_key_exists('logout', $_GET) && $_GET['logout']) {
				$this->logout();
			} 
		}

		/**
		 * Logout the user and destroys the active session
		 */
		public function logout() 
		{
			$_SESSION = array();

			if(isset($_COOKIE[session_name()]))
				setcookie(session_name(), '', time() - 42000, '/');

			@session_destroy();
			$this->user_info = array();
		}

		/**
		 * Checks if the used is logged in
		 *
		 * @return bool			TRUE if the user is logged on, FALSE otherwise.
		 */
		public function isLoggedIn() 
		{
			if($_SESSION['browser_hash'] == md5($_SERVER['HTTP_USER_AGENT'] . $_SERVER['REMOTE_ADDR']) && $_SESSION['logged_in']) 
				return true;

			return false; 
		}


		/**
		 * Returns the userid of the current user
		 *
		 * Throws exception when not logged in
		 *
		 * @return string		Userid for the current user
		 */
		public function getUserId() 
		{
			if(array_key_exists('volgnummer', $this->user_info))
				return $this->user_info['volgnummer'];

			throw new Exception("User ID is not available, is the user logged in?");
		}

		/**
		 * Returns the username of the current user
		 *
		 * Throws exception when not logged in
		 *
		 * @return string		Username for the current user
		 */
		public function getUserName() 
		{
			if(array_key_exists('gebruikersnaam', $this->user_info))
				return $this->user_info['gebruikersnaam'];

			throw new Exception("Username is not available, is the user logged in?");
		}

		/**
		 * Returns the group id of the current user
		 *
		 * @return int			GroupID, 0 is the special guest group
		 */
		public function getUserType() 
		{
			if(array_key_exists('type', $this->user_info))
				return $this->user_info['type'];
			return 0;
		}
	};

