<?php

	// Include class definitions before calling session_start()
	require_once ('../server/classes/class.dbutil.php');
	require_once ('../server/classes/class.users.php');
	require_once ('../server/classes/class.buildings.php');
	require_once ('../server/classes/class.operations.php');
	require_once ('../server/classes/class.user.php');
	require_once ('../server/config.php');

	session_start();

	if (!isset($_SESSION['userSession'])) {
		// If the user is not logged in, redirect him to the index.
		header("location: index.php");
	}

	$DB = new DBUtil(DB_HOST,		// Hostname of the DB Server
			 		 DB_USER,		// DB user
			 		 DB_PASSWORD,	// DB password
			 		 DB_NAME);		// DB name

	if ($DB) {
			
		$UserUtil = new UserUtil($DB);
		$BuildingUtil = new BuildingUtil($DB);
		$OperUtil = new OperationsUtil($DB);

		$user = $UserUtil->getUserById($_SESSION['userSession']->getId());
		$buildings = $BuildingUtil->listBuildings();
	}

	if (!$DB || $user == null || $buildings == null) {
		die("An unrecoverable error ocurred while trying to load the user information. Try again later.");
	} else {

		// Grab this user's building instances
		$arrBldInst = $OperUtil->getBuildingInstances($user->getId());

	}
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8" />

		<!-- We'll take care of the zoom ourselves -->
		<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />

		<!-- iPhone icon + chromeless browser -->
		<meta name="apple-mobile-web-app-capable" content="yes" />

		<!-- iPhone homescreen icon -->
		<link rel="apple-touch-icon" href="../img/touristResortIcon.png" /> 
		<link rel="apple-touch-icon-precomposed" href="../img/touristResortIcon.png"/>

		<!-- Chrome Frame -->
		<meta http-equiv="X-UA-Compatible" content="chrome=1" />

		<title>Tourist Resort</title>
		
		<link rel="stylesheet" href="css/ui-style.css" />
		<link rel="stylesheet" href="css/game.css" />

		<script src="../utils/modernizr-1.7.min.js" charset="utf-8"></script>		
		<script src="../examples/soundutil.js" charset="utf-8"></script>
		<script src="../examples/sprite.js" charset="utf-8"></script>

		<script src="js/comm.js" charset="utf-8"></script>
		<script src="js/gui.js" charset="utf-8"></script>
		<script src="js/game.js" charset="utf-8"></script>
		<script src="js/buildings.js" charset="utf-8"></script>
		<script src="js/resourceLoader.js" charset="utf-8"></script>
		<script src="js/misc.js" charset="utf-8"></script>

		<script>

			var Keys = {
				UP: 38,
				DOWN: 40,
				LEFT: 37,
				RIGHT: 39,
				W: 87,
				A: 65,
				S: 83,
				D: 68,
				Z: 90,
				X: 88,
				R: 82
			}

			var Tools = {
				current: 4, // Default tool
				/* - */
				MOVE: 0,
				ZOOM_IN: 1,
				ZOOM_OUT: 2,
				DEMOLISH: 3,
				SELECT: 4,
				BUILD: 5
			}

			var GameState = {
				_current: null,
				LOADING: 0,
				LOADED: 1,
				TITLESCREEN: 2,
				PLAYING: 3
			}

			var pointer = {
				DOWN: 'mousedown',
				UP: 'mouseup',
				MOVE: 'mousemove'
			};

			if (Modernizr.touch){
				pointer.DOWN = 'touchstart';
				pointer.UP = 'touchend';
				pointer.MOVE = 'touchmove';
			}


			window.onload = function () {
				var canvas = document.getElementById('gameCanvas');
				var game = document.getElementById('game');
				var g = null;
				var su = null;

				canvas.width = document.body.clientWidth;
				canvas.height = document.body.clientHeight;

				handleGameState();

				function handleGameState(nextState) {
					if (nextState !== undefined) {
						GameState._current = nextState;
					}

					switch(GameState._current) {
						case GameState.LOADING:
							preloadResources(canvas, function() {
								handleGameState(GameState.LOADED);
							});
							break;
						case GameState.LOADED:
							
							// Initialize the game object
							g = new Game(canvas, game, <?php echo GRID_X; ?>, <?php echo GRID_Y; ?>);

							// Initialize the sound util
							su = new SoundUtil();

							if (!g.started) {
								return;
							}
							
							Game.prototype.initializeGrid = function() {
								
								// Re-use the same image object for all sprite objects.
								var spritesheet = new Image();
								spritesheet.src = '../img/spritesheet.png';

								// Reuse the same sprite object for each class of object
								
								// Cinema sprite
								var cs = new Sprite(spritesheet, 0, 0, 0, 0, 1, 0);
								
								// Tree sprite
								var ts = new Sprite(spritesheet, 0, 0, 0, 0, 1, 0);

								// Ice cream shop sprite
								var icss = new Sprite(spritesheet, 0, 0, 0, 0, 1, 0);

								// Hotel sprite
								var hs = new Sprite(spritesheet, 0, 0, 0, 0, 1, 0);

								var obj;

<?php 
								/**
								 * TECHNICAL NOTE
								 * All the buildings that we own are stored in the database server, however,
								 * we need to be able to access them efficiently via Javascript. One approach would
								 * have been to load a list of all our buildings, convert it to a JSON object, print it
								 * somewhere (either on this same file or including an external file) and then parsing it
								 * with JavaScript, but i found that it was terribly inefficient and took a very long time
								 * and resources to initialize. Printing and initializing the objects directly (using an
								 * approach similar to the one below is noticeably faster.
								 * Additionally, the reason why it's being done here (on the same file as the rest of the
								 * HTML code) is to minimize the number of requests (which is great for mobile devices with
								 * high bandwidth and low latency)
								 *
								 * Although it looks a bit weird, I used this approach to appeal amateur developers as it
								 * seemed to me that it'd be easier to understand.
								 */

								for ($i = 0, $len = count($arrBldInst); $i < $len; $i++) {
									// Position information of the building instance
									$xpos = $arrBldInst[$i]->getXPos();
									$ypos = $arrBldInst[$i]->getYPos();

									// Also, we need to access additional information about each building
									$building = $BuildingUtil->getBuildingById($arrBldInst[$i]->getBuildingId());

									switch($arrBldInst[$i]->getBuildingId()) { 
										case 1: // Ice cream shop
											echo "obj = new IceCreamShop(" . $arrBldInst[$i]->getId() .", icss);";
											break;
										case 2: // Hotel
											echo "obj = new Hotel(" . $arrBldInst[$i]->getId() . ", hs);";
											break;
										case 3: // Cinema
											echo "obj = new Cinema(" . $arrBldInst[$i]->getId() . ", cs);";
											break;
										case 4: // Tree
											echo "obj = new Tree(" . $arrBldInst[$i]->getId() . ", ts);";
											break;
									}

									echo "this.tileMap[" . $xpos . "] = (!this.tileMap[" . $xpos . "]) ? [] : this.tileMap[" . $xpos . "];";
									echo "this.tileMap[" . $xpos . "][" . $ypos . "] = obj;";

									// If a building occupies more than one tile, we'll need to create building portion objects
									if ($building->getXSize() > 1 || $building->getYSize() > 1) {

										for ($bx = (($xpos + 1) - $building->getXSize()); $bx < $xpos; $bx++) {
											for ($by = (($ypos + 1) - $building->getYSize()); $by < $ypos; $by++) {
												if ($bx !== $xpos && $by !== $ypos) {
													echo "this.tileMap[" . $bx . "] = (!this.tileMap[" . $bx . "]) ? [] : this.tileMap[" . $bx . "];";
													echo "this.tileMap[" . $bx . "][" . $by . "] = new BuildingPortion(obj.buildingTypeId, " . ($xpos - $bx) .", " . ($ypos - $by) . ");";
												}
											}	
										}

									}
								}
?>
							}

							g.initializeGrid();

							handleGameState(GameState.TITLESCREEN);
							
							break;
						case GameState.TITLESCREEN:
							showIntro(canvas, true);

							document.body.addEventListener(pointer.DOWN, function(e) { 
								document.body.removeEventListener(pointer.DOWN, arguments.callee, false);
								transitionTo(canvas, function() {
									showCredits(canvas, undefined, function() {
										transitionTo(canvas, function() {
											handleGameState(GameState.PLAYING);
										});
									});
								});
							}, false);
							
							break;
						case GameState.PLAYING:
							var ui = document.getElementById('ui');
							initializeGUI(ui, canvas, g);

							// Initiate the sync loop.
							refresh(ui);


							//su.play(sources, 0, 156000, globalVolume, false);
							g.doResize();
							break;
						default:
							showIntro(canvas, false);
							handleGameState(GameState.LOADING);
							break;
					}	
				};					
			}

		</script>
    </head>
    <body>

    	<div id="game">

			<canvas id="gameCanvas" width="1" height="1"></canvas>
			<div id="ui" class="hidden">
				<div id="top">
					Account Balance: <span id="balance"><?php echo $user->getBalance(); ?></span> Coins
				</div>
				<div id="tools">
					<ul>
						<li id="select" class="active"></li>
						<li id="move"></li>
						<li id="zoomIn"></li>
						<li id="zoomOut"></li>
						<li id="rotate"></li>
						<li id="demolish"></li>
					</ul>
				</div>

				<div id="panel-container" class="hidden">
					<a href="javascript:void(0)" id="panel-toggle">Build</a>
					<div id="panel">
						<h3>Choose a building:</h3>
						<ul id="buildings">

							<?php for ($i = 0, $len = count($buildings); $i < $len; $i++) { ?>

								<li info='{"buildingId": "<?php echo $buildings[$i]->getId(); ?>"}'>
									<h2><?php echo $buildings[$i]->getName() . ' (' . $buildings[$i]->getXSize() . ' x ' . $buildings[$i]->getYSize() . ')'; ?></h2>
									<p>
										<?php 
											if ($buildings[$i]->getProfit() > 0) { 
												echo "Provides " . $buildings[$i]->getProfit() . " coins every " . $buildings[$i]->getLapse() . " seconds.";
											} else {
												echo "Decoration (Doesn't generate any profits over time";
											}
										?>
										<br />
										<span>Costs: <?php echo $buildings[$i]->getCost(); ?> coins</span>
									</p>
								</li>

							<?php } ?>

						</ul>
					</div>
				</div>

				<div id="overlays" class="hidden">
					<div class="cloud"></div>
				</div>

			</div>

		</div>
    </body>
</html>