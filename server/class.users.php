<?php
/**
 * User utility class
 *
 * Developed by Mario Andres Pagella (andres.pagella@gmail.com) 
 *
 * These example is from the book JavaScript: Making Isometric Social Real-Time Games with HTML5, CSS3 and Javascript
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

require_once ('class.user.php');

define('DEBUG_MODE', true); // Set to false to hide MySQL error messages

class UserUtil
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

	final public function getBalanceByUserId($userId)
	{
		$userId = (int)$userId;
	
		$query = "SELECT balance FROM users WHERE ID = $userId ";
		
		return $this->DBRef->GetSingleResult($query);
	}

	final public function create($user)
	{
		if (!($user instanceof User)) {
			throw new Exception('Invalid user object');
		}

		$username = $this->DBRef->filterString($user->getName());
		$password = $this->DBRef->filterString($user->getPassword());
		$email = $this->DBRef->filterString($user->getEmail());

		$t = time();
		$user->setCreationTime($t);
		$user->setLastUpdate($t);

		$query = "INSERT INTO users (NAME, PASSWORD, EMAIL, BALANCE, CONFIG, CREATIONTIME, LASTUPDATE) ";
		$query .= "VALUES (";
		$query .= "'" . $username . "', ";
		$query .= "'" . $password . "', ";
		$query .= "'" . $email . "', ";
		$query .= $user->getBalance() . ", ";
		$query .= "'" . $user->getConfig() . "', ";
		$query .= $user->getCreationTime() . ", ";
		$query .= $user->getLastUpdate();
		$query .= ")";
		
		if ($this->DBRef->ExecQuery($query)) {
			$user->setId ($this->DBRef->getInsertedId());
		}

		return $user;
	}

	final public function getUserById($userId)
	{
		$userId = (int)$userId;

		$query = "SELECT * FROM users WHERE ID = $userId ";

		$res = $this->DBRef->GetSingleResult($query);

		if (!$res || count($res) == 0) {
			return null;
		} else {
			$user = new User((int)$res['ID'], $res['NAME'], $res['EMAIL']);
			$user->setPassword($res['PASSWORD']);
			$user->setBalance((int)$res['BALANCE']);
			$user->setConfig($res['CONFIG']);
			$user->setCreationTime((int)$res['CREATIONTIME']);
			$user->setLastUpdate((int)$res['LASTUPDATE']);

			return $user;
		}
	}
}
?>