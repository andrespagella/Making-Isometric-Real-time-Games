<?php

	header('Content-Type: application/json');

	// Include class definitions before calling session_start()
	require_once ('../../server/classes/class.dbutil.php');
	require_once ('../../server/classes/class.users.php');
	require_once ('../../server/classes/class.buildings.php');
	require_once ('../../server/classes/class.operations.php');
	require_once ('../../server/classes/class.user.php');
	require_once ('../../server/config.php');

	session_start();

	if (count($_GET) != 0 && isset($_SESSION['userSession'])) {

		// Check that all parameters have valid values
		if (!is_numeric($_GET['x']) ||
			!is_numeric($_GET['y']) ||
			intval($_GET['x']) < 0 ||
			intval($_GET['y']) < 0 ||
			intval($_GET['x']) > GRID_X ||
			intval($_GET['y']) > GRID_Y) {
			die('ERROR');
		}

		$DB = new DBUtil(DB_HOST,		// Hostname of the DB Server
				 		 DB_USER,		// DB user
				 		 DB_PASSWORD,	// DB password
				 		 DB_NAME);		// DB name

		if ($DB) {
			$UserUtil = new UserUtil($DB);
			$OperUtil = new OperationsUtil($DB);

			$user = $UserUtil->getUserById($_SESSION['userSession']->getId());
			
			// Is there anything in that coordinate?
			$inst = $OperUtil->findBuildingInstanceByXY($user->getId(), $_GET['x'], $_GET['y']);

			if ($inst) {
				$OperUtil->removeInstance($inst);

				die('OK');
			} else {
				die('ERROR');
			}

		}
	} else {
		die('ERROR');
	}
?>