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
		if (!is_numeric($_GET['buildingId']) ||
			!is_numeric($_GET['x']) ||
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
			$BuildingUtil = new BuildingUtil($DB);
			$OperUtil = new OperationsUtil($DB);

			// Check if it's a valid building
			$building = $BuildingUtil->getBuildingById((int) $_GET['buildingId']);

			if ($building) {
				
				$user = $UserUtil->getUserById($_SESSION['userSession']->getId());

				// Does the user have enough money to purchase it?
				if ($user && $user->getBalance() >= $building->getCost()) {
					
					// Grab this user's building instances
					$inst = $OperUtil->findBuildingInstanceByXY($user->getId(), $_GET['x'], $_GET['y']);

					// Purchase
					if (!$inst) {

						// Create the building instance
						$bi = new BuildingInstance(null, $user->getId(), $building->getId(), (int)$_GET['x'], (int)$_GET['y']);
						$bi = $OperUtil->create($bi);	

						// Take the money away
						$user->setBalance($user->getBalance() - $building->getCost());

						// Update the user
						$UserUtil->update($user);

						// Print the building instance
						die('OK:' . $bi->getId()); 
					}
				}
			}

			die('ERROR');

		}
	} else {
		die('ERROR');
	}
?>