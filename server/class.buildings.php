<?php
/**
 * Building utility class
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

require_once ('class.building.php');

define('DEBUG_MODE', true); // Set to false to hide MySQL error messages

class BuildingUtil
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

	final public function create($building)
	{
		if (!($building instanceof Building)) {
			throw new Exception('Invalid building object');
		}

		$name = $this->DBRef->filterString($building->getName());
		$name = $this->DBRef->sanitizeString($name);

		$query = "INSERT INTO buildings (NAME, COST, XSIZE, YSIZE, PROFIT, LAPSE) ";
		$query .= "VALUES (";
		$query .= "'" . $name . "', ";
		$query .= $building->getCost() . ", ";
		$query .= $building->getXSize() . ", ";
		$query .= $building->getYSize() . ", ";
		$query .= $building->getProfit() . ", ";
		$query .= $building->getLapse();
		$query .= ")";
		
		if ($this->DBRef->ExecQuery($query)) {
			$building->setId ($this->DBRef->getInsertedId());
		}

		return $building;
	}

	final public function getBuildingById($buildingId)
	{
		$buildingId = (int)$buildingId;

		$query = "SELECT * FROM buildings WHERE ID = $buildingId ";

		$res = $this->DBRef->GetSingleResult($query);

		if (!$res || count($res) == 0) {
			return null;
		} else {
			$building = new Building($res['ID'], $res['NAME']);
			$building->setCost($res['COST']);
			$building->setXSize($res['XSIZE']);
			$building->setYSize($res['YSIZE']);
			$building->setProfit($res['PROFIT']);
			$building->setLapse($res['LAPSE']);

			return $user;
		}
	}
}
?>