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

			$user = $UserUtil->authenticate($_POST['email'], $_POST['password']);

			if ($user) {
				$_SESSION['userSession'] = $user;
			} else {
				$error = true;
			}
		}
	}

?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8" />
		<title>Authentication Example</title>
	</head>
	<body>
		<?php if (!isset($_SESSION['userSession'])) { ?>

			<?php if ($error) { ?>
				<strong>An error ocurred while trying to authenticate the user. Check the email and password and try again.</strong>
				<br />
			<?php } ?>
		
			<form action="<?php $_SERVER['PHP_SELF']?>" method="post">
				
				Email: <input type="email" name="email" /><br />

				Password: <input type="password" name="password" /><br />

				<input type="submit" value="Sign In" />

			</form>

		<?php } else { ?>
			
			Welcome back, <strong><?=$_SESSION['userSession']->getName()?></strong>. Your account balance is <?=$_SESSION['userSession']->getBalance()?>.

		<?php } ?>
	</body>
</html>