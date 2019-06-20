<?php
	/**
	 * Magic quote handling, Citrus-IT Online Boekhouding
	 *
	 * When the magic quoting option is enabled (in PHP) it
	 * automatically quotes all incoming strings as if they
	 * were meant to be inserted into a mysql database. Because
	 * we do not wish to make such an assumption, these functions
	 * will effectively disable the magic quotes option.
	 *
	 * @author Ivar Clemens <post@ivarclemens.nl>
	 * @package boekhouding
	 */

	/**
	 * Strips all slashes from an array.
	 *
	 * @param array|string $mixed Either a string or an array of strings.
	 * @return array|string 
	 * @internal
	 */
	function magic_quotes_strip($mixed) {
		if(is_array($mixed))
			return array_map('magic_quotes_strip', $mixed);

		return stripslashes($mixed);
	}

	/**
	 * Strips magic quotes from all PHP global variables and
	 * disables runtime quoting.
	 *
	 * The (super) globals being stripped are:
	 * _GET, _POST, _COOKIE, _REQUEST, _FILES, _ENV, _SERVER
	 */
	function disable_magic_quotes() {
		set_magic_quotes_runtime(0);
	
		if(get_magic_quotes_gpc()) {
			$_GET = magic_quotes_strip($_GET);
			$_POST = magic_quotes_strip($_POST);
			$_COOKIE = magic_quotes_strip($_COOKIE);
			$_REQUEST = array_merge($_GET, $_POST, $_COOKIE);
			$_FILES = magic_quotes_strip($_FILES);
			$_ENV = magic_quotes_strip($_ENV);
			$_SERVER = magic_quotes_strip($_SERVER);
		}
	}
