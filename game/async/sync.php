<?php

	header('Content-Type: application/json');

	// Include class definitions before calling session_start()
	require_once ('../../server/classes/class.dbutil.php');
	require_once ('../../server/classes/class.users.php');
	require_once ('../../server/classes/class.user.php');
	require_once ('../../server/classes/class.buildings.php');
	require_once ('../../server/classes/class.building.php');
	require_once ('../../server/classes/class.operations.php');
	require_once ('../../server/config.php');

	session_start();

	$error = false;

	if (isset($_SESSION['userSession'])) {
		$DB = new DBUtil(DB_HOST,		// Hostname of the DB Server
				 		 DB_USER,		// DB user
				 		 DB_PASSWORD,	// DB password
				 		 DB_NAME);		// DB name

		if ($DB) {
			$UserUtil = new UserUtil($DB);

			$user = $UserUtil->getUserById($_SESSION['userSession']->getId());

			if ($user) {
				$OperUtil = new OperationsUtil($DB);
				$BuildingUtil = new BuildingUtil($DB);

				$balance = $UserUtil->getBalanceByUserId($user->getId());
				
				$arrBuildings = $OperUtil->getProfitableBuildingsList($user->getId());

				$t = time() - $user->getLastUpdate();

				for ($i = 0; $i < count($arrBuildings); $i++) {
					$res = floor($t / (int)$arrBuildings[$i]['LAPSE']);
					$res = $res * (int)$arrBuildings[$i]['PROFIT'];
					$balance += $res * (int)$arrBuildings[$i]['QTY'];
				}

				$user->setBalance($balance);
				$UserUtil->update($user);

				die('OK:' . $balance);
			} else {
				die('ERROR');		
			}
		}
	} else {
		die('ERROR');
	}
?>