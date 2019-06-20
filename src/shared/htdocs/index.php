<?php
	if(file_exists('config.php')) {
		require_once('config.php');
	} else {
		header('Location: setup.php');
		exit(1);
	}

	require_once($globalDataPath . '/library/initialization.php');

	try {
		initializeEnvironment();
		startApplication();
	} catch (Exception $e) {
		try {
			displayInitError($e->getMessage());
		} catch (Exception $e) {
			header("Content-type: text/plain");
			echo("Kan de gedeelde CAS installatie niet vinden: " . $e->getMessage);
			exit(1);
		}
	}
