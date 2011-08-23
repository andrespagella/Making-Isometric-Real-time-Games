<?php

	// Include class definitions before calling session_start()
	require_once ('../server/classes/class.dbutil.php');
	require_once ('../server/classes/class.users.php');
	require_once ('../server/classes/class.operations.php');
	require_once ('../server/classes/class.buildingInstance.php');
	require_once ('../server/classes/class.user.php');
	require_once ('../server/config.php');

	session_start();

	if (isset($_SESSION['userSession'])) {
		// If the user is already logged in, redirect him to the game.
		header("location: game.php");
	}

	$loginError = false;
	$registerError = false;

	if (count($_POST) != 0 && isset($_POST['type'])) {

		$DB = new DBUtil(DB_HOST,		// Hostname of the DB Server
				 		 DB_USER,		// DB user
				 		 DB_PASSWORD,	// DB password
				 		 DB_NAME);		// DB name

		if ($DB) {
			
			$UserUtil = new UserUtil($DB);
			$OperUtil = new OperationsUtil($DB);

			if ($_POST['type'] == 'login') {
				$user = $UserUtil->authenticate($_POST['email'], $_POST['password']);

				if ($user) {
					$_SESSION['userSession'] = $user;
					header("location: game.php");
				} else {
					$loginError = true;
				}
			} else {
				if (strlen($_POST['name']) == 0 || strlen($_POST['password']) == 0) {
					$registerError = true;
				} else {
					$user = new User(null, $_POST['name'], $_POST['email']);
					$user->setPassword($_POST['password']);

					$user = $UserUtil->create($user);

					if ($user) {

						// Fill 10% of the grid (the size is defined in config.php) with trees for this user.
						// Hardcoded the ID (4) for simplicity.

						$amtX = round(10 * GRID_X) / 100;
						$amtY = round(10 * GRID_Y) / 100;

						for ($i = 0; $i < $amtX; $i++) {
							for ($j = 0; $j < $amtY; $j++) {
								$bi = new BuildingInstance(null, $user->getId(), 4, mt_rand(0, GRID_X), mt_rand(0, GRID_Y));
								$OperUtil->create($bi);		
							}
						}

						$_SESSION['userSession'] = $user;
						header("location: game.php");
					} else {
						$registerError = true;
					}
				}
			}

		}
	}

?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8" />
		<title>Tourist Resort</title>
		<link rel="stylesheet" href="css/site.css" />
		<script src="../utils/modernizr-1.7.min.js" charset="utf-8"></script>

		<script>
			window.onload = function () {
				var pointer = {
					UP: 'mouseup'
				};

				if (Modernizr.touch){
					pointer.UP = 'touchend';
				}

				document.body.addEventListener(pointer.UP, handleClick, false);
			}

			function handleClick(e) {
				// Make sure that the click was made on a button
				if (e.target.tagName == 'INPUT') {
					switch(e.target.getAttribute("id")) {
						case "btnSignIn":
							e.target.parentNode.setAttribute("class", "hidden");
							document.getElementById('signInArea').setAttribute("class", "");
							break;
						case "btnRegister":
							e.target.parentNode.setAttribute("class", "hidden");
							document.getElementById('registerArea').setAttribute("class", "");
							break;
						default:
							var o = e.target;

							if (o.getAttribute("class")) {
								var classes = o.getAttribute("class").split(" ");

								for (var i = 0; i < classes.length; i++) {
									if (classes[i] == 'cancel') {
										e.target.parentNode.parentNode.setAttribute("class", "hidden");
										document.getElementById('controls').setAttribute("class", "");
									}
								}
							}
							break;
					}
				}
			}
		</script>
	</head>
	<body>
		
			<img src="../img/logo-small.png" alt="Tourist Resort" />

			<div id="controls">
				<input type="button" id="btnSignIn" class="button" value="Sign In" />
				<input type="button" id="btnRegister" class="button" value="Register new user" />
			</div>

			<?php if ($loginError) { ?>
				<p>
					<strong>An error ocurred while trying to authenticate the user. Check the email and password and try again.</strong>
				</p>
			<?php } else if ($registerError) { ?>
				<p>
					<strong>An error ocurred while trying to register the user. Check the username, email and password and try again.</strong>
				</p>
			<?php } ?>

			<div id="signInArea" class="hidden">
				<form action="<?php $_SERVER['PHP_SELF']?>" method="post">
					<input type="hidden" name="type" value="login" />

					Email: <input type="email" name="email" /><br />

					Password: <input type="password" name="password" /><br />

					<input type="submit" class="button" value="Sign In" />
					<input type="button" class="button cancel" value="Cancel" />

				</form>
			</div>

			<div id="registerArea" class="hidden">
				<form action="<?php $_SERVER['PHP_SELF']?>" method="post">
					<input type="hidden" name="type" value="register" />

					Username: <input type="text" name="name" /><br />

					Email: <input type="email" name="email" /><br />

					Password: <input type="password" name="password" /><br />

					<input type="submit" class="button" value="Register" />
					<input type="button" class="button cancel" value="Cancel" />

				</form>
			</div>

	</body>
</html>