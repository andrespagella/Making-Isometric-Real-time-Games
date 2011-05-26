<?php

	// Include class definitions before calling session_start()
	require_once ('classes/class.dbutil.php');
	require_once ('classes/class.users.php');
	require_once ('classes/class.user.php');
	require_once ('config.php');

	session_start();

	$error = false;

	if (count($_POST) != 0 && !isset($_SESSION['userSession'])) {
		$DB = new DBUtil(DB_HOST,		// Hostname of the DB Server
				 		 DB_USER,		// DB user
				 		 DB_PASSWORD,	// DB password
				 		 DB_NAME);		// DB name

		if ($DB) {
			$UserUtil = new UserUtil($DB);

			if (strlen($_POST['name']) == 0 || strlen($_POST['password']) == 0) {
				$error = true;
			} else {
				$user = new User(null, $_POST['name'], $_POST['email']);
				$user->setPassword($_POST['password']);

				$user = $UserUtil->create($user);

				if ($user) {
					$_SESSION['userSession'] = $user;
				} else {
					$error = true;
				}
			}
		}
	}

?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8" />
		<title>Registration Example</title>
	</head>
	<body>
		<?php if (!isset($_SESSION['userSession'])) { ?>

			<?php if ($error) { ?>
				<strong>An error ocurred while trying to register the user.</strong>
				<br />
			<?php } ?>
		
			<form action="<?php $_SERVER['PHP_SELF']?>" method="post">
				
				Username: <input type="text" name="name" /><br />

				Email: <input type="email" name="email" /><br />

				Password: <input type="password" name="password" /><br />

				<input type="submit" value="Register" />

			</form>

		<?php } else { ?>
			
			Welcome, <strong><?=$_SESSION['userSession']->getName()?></strong>. Your account balance is <?=$_SESSION['userSession']->getBalance()?>.

		<?php } ?>
	</body>
</html>