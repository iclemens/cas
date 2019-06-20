<?php
	@include_once '../utility/setup.php';

	/**
	 * @package boekhouding
	 * @subpackage UnitTests
	 */

	require_once "CT/User.php";

	class CT_UserTest extends CT_TestCase
	{
		protected function setUp() 
		{
		}

		protected function tearDown() 
		{
		}

		public function testSingleton() {
			$_SESSION = array();

			$user = CT_User::instance(Zend_Registry::get('database'));
			$user2 = CT_User::instance(Zend_Registry::get('database'));

			$this->assertEquals($user, $user2);
		}

		public function testLoginFailure() {
			$_SESSION = array();

			$user = new CT_User(Zend_Registry::get('database'));
			$result = $user->login("directie", "incorrect");

			$this->assertFalse($result);
			$this->assertFalse($user->isLoggedIn());
			$this->assertEquals($user->getUserType(), 0);
		
			try {
				$user->getUserId();
			} catch(Exception $exception) {
				return;
			}

			$this->fail("Expected exception");
		}

		public function testLoginSuccess() {
			$_SESSION = array();

			$user = new CT_User(Zend_Registry::get('database'));
			$result = $user->login("directie", "pw1directie");

			$this->assertTrue($result);
			$this->assertTrue($user->isLoggedIn());
			$this->assertEquals($user->getUserType(), 1);

			$this->assertEquals($user->getUserId(), 1);
			$this->assertEquals($user->getUserName(), "directie");
		}

		public function testLoginFromSessionFailure() {
			$_SESSION['browser_hash'] = md5('failure');
			$_SESSION['logged_in'] = true;
			$_SESSION['user_id'] = 2;

			$user = new CT_User(Zend_Registry::get('database'));

			$this->assertFalse($user->isLoggedIn());
			$this->assertEquals($user->getUserType(), 0);

			try {
				$user->getUserName();
			} catch(Exception $exception) {
				return;
			}

			$this->fail("Expected exception");
		}

		public function testLoginFromSessionSuccess() {
			if(array_key_exists('HTTP_USER_AGENT', $_SERVER) && array_key_exists('REMOTE_ADDR', $_SERVER)) {
				$_SESSION['browser_hash'] = md5($_SERVER['HTTP_USER_AGENT'] .$_SERVER['REMOTE_ADDR']);
			} else {
				$_SESSION['browser_hash'] = md5('');
			}

			$_SESSION['logged_in'] = true;
			$_SESSION['user_id'] = 2;

			$user = new CT_User(Zend_Registry::get('database'));

			$this->assertTrue($user->isLoggedIn());
			$this->assertEquals($user->getUserType(), 2);
			$this->assertEquals($user->getUserId(), 2);
			$this->assertEquals($user->getUserName(), "administratie");
		}

		public function testLoginInactive() {
			$_SESSION = array();

			$user = new CT_User(Zend_Registry::get('database'));
			$result = $user->login("60000", "pw4klant_inactief");

			$this->assertFalse($result);
			$this->assertFalse($user->isLoggedIn());
			$this->assertEquals($user->getUserType(), 0);
		}

		public function testLogout() {
			$_SESSION = array();

			$user = new CT_User(Zend_Registry::get('database'));
			$result = $user->login("60000", "pw3klant_actief");

			$this->assertTrue($result);
			$this->assertTrue($user->isLoggedIn());
			$this->assertEquals($user->getUserType(), 3);

			$user->logout();

			$this->assertFalse($user->isLoggedIn());
			$this->assertEquals($user->getUserType(), 0);
		}

		public function testLogoutFromGET() {
			$_SESSION = array();
			$_GET = array("logout" => true);

			$user = new CT_User(Zend_Registry::get('database'));
			$result = $user->login("administratie", "pw2administratie");

			$this->assertTrue($result);
			$this->assertTrue($user->isLoggedIn());
			$this->assertEquals($user->getUserType(), 2);

			$user->init();

			$this->assertFalse($user->isLoggedIn());
			$this->assertEquals($user->getUserType(), 0);
		}
	}

