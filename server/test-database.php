<?php
	
	require_once ('classes/class.dbutil.php');
	require_once ('classes/class.users.php');
	require_once ('classes/class.user.php');
	require_once ('classes/class.buildings.php');
	require_once ('classes/class.building.php');
	require_once ('config.php');

	$DB = new DBUtil(DB_HOST,		// Hostname of the DB Server
				 	 DB_USER,		// DB user
				 	 DB_PASSWORD,	// DB password
				 	 DB_NAME);		// DB name

	if ($DB) {
		$UserUtil = new UserUtil($DB);
		$BuildingUtil = new BuildingUtil($DB);
		
		$user = new User(null, 'example', 'test@test.com');
		$user->setPassword('a-password');
		$user = $UserUtil->create($user);
		
		if ($user != null) {
			echo '<strong>UserUtil->create()</strong>';
			echo '<pre>';
			print_r($user);
			echo '</pre>';
		} else {
			echo "A problem ocurred while trying to create the user";
		}
		
		if ($user != null) {
			$id = $user->getId();
			
			$user2 = $UserUtil->getUserById($id);
			
			if ($user2 != null) {
				echo '<strong>UserUtil->getUserById()</strong>';
				echo '<pre>';
				print_r($user2);
				echo '</pre>';
			} else {
				echo "A problem ocurred while trying to get a user by ID";
			}
		}
		
		if ($user != null) {
			if ($UserUtil->removeUser($user)) {
				echo '<strong>UserUtil->remove()</strong>';
				echo '<br />OK';
			} else {
				echo "A problem ocurred while trying to remove the user from the database";
			}
		}

		echo "<br /><br />";
		
		$building = new Building(null, 'example', 'test@test.com');
		$building = $BuildingUtil->create($building);
		
		if ($building != null) {
			echo '<strong>BuildingUtil->create()</strong>';
			echo '<pre>';
			print_r($building);
			echo '</pre>';
		} else {
			echo "A problem ocurred while trying to create the building";
		}
		
		if ($building != null) {
			$id = $building->getId();
			
			$building2 = $BuildingUtil->getBuildingById($id);
			
			if ($building2 != null) {
				echo '<strong>BuildingUtil->getBuildingById()</strong>';
				echo '<pre>';
				print_r($building2);
				echo '</pre>';
			} else {
				echo "A problem ocurred while trying to get a building by ID";
			}
		}
		
		if ($building != null) {
			if ($BuildingUtil->removeBuilding($building)) {
				echo '<strong>BuildingUtil->remove()</strong>';
				echo '<br />OK<br/><br />';
			} else {
				echo "A problem ocurred while trying to remove the building from the database";
			}
		}
		
	} else {
		echo "There was a problem connecting to the Database";
	}
?>