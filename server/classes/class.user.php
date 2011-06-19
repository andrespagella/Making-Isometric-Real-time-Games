<?php
/**
 * User class
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

class User
{
	private $id, $name, $email, $password = "", $balance = 2000, $config = "", $creationTime = 0, $lastUpdate = 0;

	final public function __construct($id, $name, $email)
	{						
		if ($id != null) {
			$this->setId($id);
		}
		
		$this->setName($name);
		$this->setEmail($email);
	}

	final public function getId() { return (int)$this->id; }
	final public function setId($id) { $this->id = (int)$id; }

	final public function getName() { return $this->name; }
	final public function setName($name) { $this->name = $name; }

	final public function getEmail() { return $this->email; }
	final public function setEmail($email) { 
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			throw new Exception('Invalid email address');
		} else {
			$this->email = $email;
		}
	}

	final public function getPassword() { return $this->password; }
	final public function setPassword($password) { $this->password = sha1($password); }

	final public function getBalance() { return (int)$this->balance; }
	final public function setBalance($balance) { $this->balance = (int)$balance; }

	final public function getConfig() { return $this->config; }
	final public function setConfig($config) { $this->config = $config; }

	final public function getCreationTime() { return (int)$this->creationTime; }
	final public function setCreationTime($time) { $this->creationTime = (int)$time; }

	final public function getLastUpdate() { return (int)$this->lastUpdate; }
	final public function setLastUpdate($time) { $this->lastUpdate = (int)$time; }
}
?>