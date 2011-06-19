<?php
/**
 * User utility class
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

if (!class_exists('User')) {
	require_once ('class.user.php');
}

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
	
		$query = "SELECT BALANCE FROM users WHERE ID = $userId ";
		
		$res = $this->DBRef->GetSingleResult($query);

		return $res['BALANCE'];
	}

	final public function create($user)
	{
		if (!($user instanceof User)) {
			throw new Exception('Invalid user object');
		}

		$username = $this->DBRef->filterString($user->getName());
		$username = $this->DBRef->sanitizeString($username);

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

	final public function update($user)
	{
		if (!($user instanceof User)) {
			throw new Exception('Invalid user object');
		}

		$username = $this->DBRef->filterString($user->getName());
		$username = $this->DBRef->sanitizeString($username);

		$password = $this->DBRef->filterString($user->getPassword());
		$email = $this->DBRef->filterString($user->getEmail());

		$t = time();
		$user->setLastUpdate($t);

		$query = "UPDATE users SET ";
		$query .= "NAME = '" . $username . "', ";
		$query .= "EMAIL = '" . $email . "', ";
		$query .= "BALANCE = " . $user->getBalance() . ", ";
		$query .= "CONFIG = '" . $user->getConfig() . "', ";
		$query .= "LASTUPDATE = " . $user->getLastUpdate() ." ";
		$query .= "WHERE ID = " . $user->getId();
		
		$this->DBRef->ExecQuery($query);
	}

	final public function getUserById($userId)
	{
		$userId = (int)$userId;

		$query = "SELECT * FROM users WHERE ID = $userId ";

		$res = $this->DBRef->GetSingleResult($query);

		if (!$res || count($res) == 0) {
			return null;
		} else {
			$user = new User($res['ID'], $res['NAME'], $res['EMAIL']);
			$user->setPassword($res['PASSWORD']);
			$user->setBalance($res['BALANCE']);
			$user->setConfig($res['CONFIG']);
			$user->setCreationTime($res['CREATIONTIME']);
			$user->setLastUpdate($res['LASTUPDATE']);

			return $user;
		}
	}

	final public function listUsers($start = null, $limit = null)
	{
		$query = "SELECT * FROM users ";

		if ($start != null && $limit != null) {
			$start = (int)$start;
			$limit = (int)$limit;

			$query .= "LIMIT $start, $limit ";
		}

		$res = $this->DBRef->GetAllResults($query);

		if (count($res) == 0) {
			return null;
		} else {
			$arr = array();
			for ($i = 0, $x = count($res); $i < $x; $i++) {
				
				$user = new User($res[$i]['ID'], $res[$i]['NAME'], $res[$i]['EMAIL']);
				$user->setPassword($res[$i]['PASSWORD']);
				$user->setBalance($res[$i]['BALANCE']);
				$user->setConfig($res[$i]['CONFIG']);
				$user->setCreationTime($res[$i]['CREATIONTIME']);
				$user->setLastUpdate($res[$i]['LASTUPDATE']);

				array_push($arr, $user);
			}

			return $arr;
		}
	}

	final public function removeUser($user)
	{
		$id = $user->getId();

		$query = "DELETE FROM users WHERE ID = $id ";

		return $this->DBRef->ExecQuery($query);
	}

	final public function authenticate($email, $password) {
		$email = (isset($email)) ? $email : "";
		$password = (isset($password)) ? $password : "";

		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			return null;
		}

		if (strlen($password) == 0) {
			return null;
		}

		$email = $this->DBRef->filterString($email);
		$password = sha1($password);

		$query = "SELECT * FROM users WHERE ";
		$query .= "EMAIL = '$email' AND ";
		$query .= "PASSWORD = '$password' ";

		$res = $this->DBRef->GetSingleResult($query);

		if (count($res) == 0 || $res == 0) {
			return null;
		} else {
			$user = new User((int)$res['ID'], $res['NAME'], $res['EMAIL']);
			$user->setPassword($res['PASSWORD']);
			$user->setBalance($res['BALANCE']);
			$user->setConfig($res['CONFIG']);
			$user->setCreationTime($res['CREATIONTIME']);
			$user->setLastUpdate($res['LASTUPDATE']);

			return $user;
		}
	}
}
?>