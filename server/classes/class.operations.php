<?php
/**
 * Utility class to handle operations such as purchase/selling buildings, etc.
 *
 * Developed by Mario Andres Pagella (andres.pagella@gmail.com) 
 *
 * This example is from the book JavaScript: Making Isometric Social Real-Time Games with HTML5, CSS3 and Javascript
 * See the "O'Reilly Policy on Re-Use of Code Examples from Books"
 * (http://www.oreilly.com/pub/a/oreilly/ask_tim/2001/codepolicy.html) for
 * details on how you may and may not use these examples. In most cases, it
 * suffices to simply provide suitable attribution, in the source code and
 * documentation of your program, with a comment like the following:
 * 
 * // This code is from the book JavaScript: Making Isometric Social Real-Time Games with HTML5, CSS3 and Javascript
 * // (ISBN #???). Copyright ??? by Mario Andres Pagella.
 * 
 * Please note that the examples are not production code and have not been
 * carefully testing. They are provided "as-is" and come with no warranty
 * of any kind.
 */ 

if (!class_exists('BuildingInstance')) {
	require_once ('class.buildingInstance.php');
}

define('DEBUG_MODE', true); // Set to false to hide MySQL error messages

class OperationsUtil
{
	private $DBRef;
		
	final public function __construct($DBObj)
	{						
		if (!isset($DBObj)) {
			if (DEBUG_MODE) {
				die("The DB Object is invalid");
			}
			return false;
		} else {
			$this->DBRef = $DBObj;
			return true;
		}
	}

	final public function create($buildingInstance)
	{
		if (!($buildingInstance instanceof BuildingInstance)) {
			throw new Exception('Invalid building instance object');
		}
		
		$query = "INSERT INTO building_instances (USERID, BUILDINGID, XPOS, YPOS) ";
		$query .= "VALUES (";
		$query .= $buildingInstance->getUserId() . ", ";
		$query .= $buildingInstance->getBuildingId() . ", ";
		$query .= $buildingInstance->getXPos() . ", ";
		$query .= $buildingInstance->getYPos();
		$query .= ")";
		
		if ($this->DBRef->ExecQuery($query)) {
			$buildingInstance->setId ($this->DBRef->getInsertedId());
		}

		return $buildingInstance;
	}

	final public function getProfitableBuildingsList($userId)
	{
		$userId = (int)$userId;

		$query = "SELECT b.*, COUNT(*) AS QTY ";
		$query .= "FROM building_instances bi ";
		$query .= "LEFT JOIN buildings b ON b.ID = bi.BUILDINGID ";
		$query .= "WHERE bi.USERID = $userId and b.PROFIT IS NOT NULL ";
		$query .= "GROUP BY bi.BUILDINGID ";

		return $this->DBRef->GetAllResults($query);
	}

	final public function findBuildingInstanceByXY($userId, $x = 0, $y = 0)
	{
		$userId = (int)$userId;
		$x = (int)$x;
		$y = (int)$y;

		$query = "SELECT * FROM building_instances WHERE USERID = $userId AND XPOS = $x AND YPOS = $y ";

		$res = $this->DBRef->GetSingleResult($query);

		if (!$res || count($res) == 0) {
			return null;
		} else {
			$buildingInstance = new BuildingInstance($res['ID'], $res['USERID'], $res['BUILDINGID'], $res['XPOS'], $res['YPOS']);

			return $buildingInstance;
		}
	}

	final public function getBuildingInstanceById($buildingInstanceId)
	{
		$buildingInstanceId = (int)$buildingInstanceId;

		$query = "SELECT * FROM building_instances WHERE ID = $buildingInstanceId ";

		$res = $this->DBRef->GetSingleResult($query);

		if (!$res || count($res) == 0) {
			return null;
		} else {
			$buildingInstance = new BuildingInstance($res['ID'], $res['USERID'], $res['BUILDINGID'], $res['XPOS'], $res['YPOS']);

			return $buildingInstance;
		}
	}

	final public function getBuildingInstances($userId = null)
	{
		$userId = (int)$userId;

		$query = "SELECT * FROM building_instances WHERE USERID = $userId ";

		$res = $this->DBRef->GetAllResults($query);

		if (count($res) == 0) {
			return null;
		} else {
			$arr = array();
			for ($i = 0, $x = count($res); $i < $x; $i++) {

				$buildingInstance = new BuildingInstance($res[$i]['ID'], $res[$i]['USERID'], $res[$i]['BUILDINGID'], $res[$i]['XPOS'], $res[$i]['YPOS']);

				array_push($arr, $buildingInstance);
			}

			return $arr;
		}
	}

	final public function removeInstance($buildingInstance)
	{
		$id = $buildingInstance->getId();

		$query = "DELETE FROM building_instances WHERE ID = $id ";

		return $this->DBRef->ExecQuery($query);
	}
}
?>