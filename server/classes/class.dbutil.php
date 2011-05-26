<?php
/**
 * Simple MySQL utility class
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

define('DEBUG_MODE', true); // Set to false to hide MySQL error messages

class DBUtil
{
	private $DB_server, $DB_user, $DB_passwd, $DB_name, $DB_timeout;
	protected $connected = false;
	private $mySQLi;
		
	final public function __construct($Server, $Username, $Password, $Name, $Timeout = 30) 
	{	
		$this->DB_server  = $Server;
       	$this->DB_user    = $Username;
       	$this->DB_passwd  = $Password;
       	$this->DB_name    = $Name;
       	$this->DB_timeout = $Timeout;
       	
       	$this->mySQLi = mysqli_init();
		
		if (!$this->mySQLi) {
			die($this->mySQLi->error);
		} else {
			if ($this->DB_timeout != 0) {
				$this->mySQLi->options(MYSQLI_OPT_CONNECT_TIMEOUT, $this->DB_timeout);
			}
		}

		if ($this->connect()) {
			$this->connected = true;
		} else {
			$this->connected = false;
			
			if (DEBUG_MODE) {
				die($this->mySQLi->error);
			}
		}

		if ($this->selectDB()) {
			$this->connected = true;
			return true;
		} else {
			$this->connected = false;				
			
			if (DEBUG_MODE) {
				die($this->mySQLi->error);
			}

			return false;
		}
	}

   	final private function connect() 
   	{	   	
		if(!$this->mySQLi->real_connect($this->DB_server, $this->DB_user, $this->DB_passwd)) {
			return false;
		} else {
			return true;
		}
    }

    final private function selectDB() 
    {
		if (!$this->mySQLi->select_db($this->DB_name)) {
			return false;
		} else {
			return true;
		}   
   	}

	final public function terminate() 
	{
		return $this->mySQLi->close();
	}		
	
	final public function GetSingleResult($query) 
	{
		if (!isset($query)) {
			if (DEBUG_MODE) {
				die("Query not set");
			}

			return false;
		} else {
		
			$res = $this->mySQLi->query($query);
			
			if ($this->mySQLi->error) {
				if (DEBUG_MODE) {
					die($this->mySQLi->error);
				}
			}
			
			if (!$res) {
				return false;
			} else if ($res->num_rows != 0) {

				return $res->fetch_assoc();
			} else {
				return 0;
			}
		}
	}
	
	final public function GetAllResults($query) 
	{
		if (!isset($query)) {
			die("Query not set");
		} else {
			$arrRes = Array();
			$res = $this->mySQLi->query($query);
			
			if ($this->mySQLi->error) {
				if (DEBUG_MODE) {
					die($this->mySQLi->error);
				}
			}
			
			if (!$res) {
				return false;
			} 

			while ($row = $res->fetch_array(MYSQL_ASSOC)) {
				array_push($arrRes, $row);
			}
			
			return $arrRes;
		}
	}
	
	final public function ExecQuery($query) 
	{
		if (!isset($query)) {
			if (DEBUG_MODE) {
				die("Query not set");
			}

			return false;
		} else {
			$res = $this->mySQLi->query($query);
			
			if ($this->mySQLi->error) {
				if (DEBUG_MODE) {
					die($this->mySQLi->error);
				}
			}
			
			if (!$res) {
				return false;
			} else {
				return true;
			}
		}
	}
	
	final public function filterString($str = '') 
	{
		$str = $this->mySQLi->real_escape_string($str);
		
		return (string)$str;
	}

	final public function sanitizeString($str = '') 
	{
		$str = strip_tags($str);
		
		return (string)$str;
	}
	
    final public function getInsertedId()
    {
    	return $this->mySQLi->insert_id;
    }
}
?>