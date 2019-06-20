<?php
	/**
	 * Action, Citrus-IT Online Boekhouding
	 *
	 * @author Ivar Clemens <post@ivarclemens.nl>
	 * @package boekhouding
	 */ 

	Zend_Loader::loadClass("Zend_Controller_Action");
	Zend_Loader::loadClass("CT_Exception_Unauthenticated");
	Zend_Loader::loadClass("CT_Exception_Unauthorized");
	Zend_Loader::loadClass("CT_Smarty");
	Zend_Loader::loadClass("CT_User");

	/**
	 * Parent class for controllers providing database and authentication.
	 *
	 * @package boekhouding
	 */
	abstract class CT_Controller_Action extends Zend_Controller_Action 
	{
		/**
		 * Local smarty instance
		 * @var CT_Smarty
		 */
		protected $_smarty;

		/**
		 * Instance of the user singleton
		 * @var CT_User
		 */
		protected $_user;

		/**
		 * Instance of the global database object
		 * @var Zend_Db
		 */
		protected $_database;

		/**
		 * Initializes database, templates and authentication for the controller.
		 *
		 * This function is being called by the Zend_Controller_Action constructor.
		 */
		public function init()
		{
			$this->_database = Zend_Registry::get('database');

			// Initialize smarty templating engine
			try {
				$this->_smarty = new CT_Smarty(
					Zend_Registry::get("config"), 
					Zend_Registry::get("formatter"));
			} catch(Exception $e) {
				throw new Exception('Kan de template module niet initializeren.');
			}

			// Initialize the user class
			try {
				$this->_user = CT_User::instance($this->_database);
				$this->_user->init();

				$this->_smarty->setUserType($this->_user->getUserType());
			} catch(Exception $e) {
				$logger = Zend_Registry::get('logger');
				$logger->log('[CT_USER] : ' . $e->getMessage(), Zend_Log::CRIT);			
			
				throw new Exception('Kan de authenticatie module niet initializeren.');
			}
		}

		/**
		 * Returns the view component instance.
		 *
		 * @return CT_Smarty Instance of the Citrus-IT smarty class.
		 */
		public function getView()
		{
			return $this->_smarty;
		}

		/**
		 * Display an error and terminate the application.
		 *
		 * @deprecated Throw an exception instead
		 *
		 * @param	string $message The message to display on the error screen.
		 */
		protected function showErrorAndExit($message) {
			throw new Exception($message);
			exit(1);
		}

		/**
		 * Implements basic authentication-level access control.
		 *
		 * Checks whether the current user is authenticated. If not,
		 * it throws an CT_Exception_Unauthorized.
		 *
		 * @return boolean	True if the user meets the requirements.
		 */
		protected function requireValidUser() {
			if($this->_user->isLoggedIn() == false)
				throw new CT_Exception_Unauthenticated('Authentication required');

			return true;
		}

		/**
		 * Implements basic group-level access control.
		 *
		 * Throws an exception if the user is not part of any of the
		 * specified groups.
		 *
		 * The $group parameter must be either the name of a group or
		 * an array containing group names.
		 *
		 * If the user does meet the conditions, this function returns true.
		 *
		 * @return	boolean		True if the user meets the requirements.
		 */
		protected function requireUserType($groups) {
			$this->requireValidUser();

			if(is_array($groups)) {
				foreach($groups as $group) {
					if($this->_user->getUserType() == $group)
						return true;
				}
			} else {
				if($this->_user->getUserType() == $groups)
					return true;
			}

			throw new CT_Exception_Unauthorized('Insufficient credentials');
		}


		/**
		 * Queries the database and shows the result in a table. This function provides
		 *  support for basic searching, pagination and sorting.
		 *
		 * @param Zend_Select $selectQuery The query to execute
		 * @param string $template The template to display after the result has been obtained
		 * @param array $searchDef Fieldsnames (keys) and a template that should be added
		 * 							to the where-clause (values) if this value is non-empty.
		 */
		protected function genericTableAction($selectQuery, $template, $searchDef = array()) {
			$db = Zend_Registry::get('database');

			foreach($searchDef as $field => $query) {
				if(array_key_exists($field, $_GET)) {
					$parameters[$field] = $this->_getParam($field);
					
					if($parameters[$field] != '')
						$selectQuery->where($db->quoteInto($query, $parameters[$field]));
				}
			}

			$page = intval($this->_getParam('page'));
			$count = intval($this->_getParam('count'));

			$sort = $this->_getParam('sort');
			
			$paginate = new CT_Paginate_Db($db, $page, $count);
			$sorter = new CT_Sort_Db($db, $sort);
			$results = $paginate->getRecords($sorter->extendQuery($selectQuery));

			CT_Smarty_Paginate::registerPlugin($this->_smarty);
		
			$this->_smarty->assign('pager', $paginate);
			$this->_smarty->assign('sorter', $sorter);
			$this->_smarty->assign('results', $results);
			
			if(count($searchDef) > 0)
				$this->_smarty->assign('parameters', $parameters);

			$this->getResponse()->appendBody(
				$this->_smarty->fetch($template));
		}


		/**
		 * Redirects the user to another page.
		 *
		 * @deprecated Redirection should not be handled directly by the action controller.
		 *
		 * @param string $target An URL to redirect to, this should be relative to the application base.
		 */
		protected function redirect($target) {
			try {
				parent::_redirect($target);
			} catch(Exception $e) {
				/* An exception could be thrown when headers are already sent
					There is nothing much we could do (maybe show a manual link instead?) */
			}
		}
	}

?>
