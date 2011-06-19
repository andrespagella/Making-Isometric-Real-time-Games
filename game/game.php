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

		<script charset="utf-8">

			// Enums
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
				pointer.DOWN = 'ontouchstart';
				pointer.UP = 'ontouchend';
				pointer.MOVE = 'ontouchmove';
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
							g = new Game(canvas, game, <?=GRID_X?>, <?=GRID_Y?>);

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

								var obj = null;

								<?php for ($i = 0, $len = count($arrBldInst); $i < $len; $i++) { ?>
									this.tileMap[<?=$arrBldInst[$i]->getXPos()?>] = (!this.tileMap[<?=$arrBldInst[$i]->getXPos()?>]) ? [] : this.tileMap[<?=$arrBldInst[$i]->getXPos()?>];

									<?php 
										switch($arrBldInst[$i]->getBuildingId()) { 
											case 1: // Ice cream shop
									?>
												obj = new IceCreamShop(<?=$arrBldInst[$i]->getId()?>, icss);
									<?php												
												break;
											case 2: // Hotel
									?>
												obj = new Hotel(<?=$arrBldInst[$i]->getId()?>, hs);
									<?php												
												break;
											case 3: // Cinema
									?>
												obj = new Cinema(<?=$arrBldInst[$i]->getId()?>, cs);
									<?php												
												break;
											case 4: // Tree
									?>
												obj = new Tree(<?=$arrBldInst[$i]->getId()?>, ts);
									<?php												
												break;
										}
									?>

									this.tileMap[<?=$arrBldInst[$i]->getXPos()?>][<?=$arrBldInst[$i]->getYPos()?>] = obj;
									for (var i = (<?=$arrBldInst[$i]->getXPos()?> + 1) - obj.tileWidth; i <= <?=$arrBldInst[$i]->getXPos()?>; i++) {
										for (var j = (<?=$arrBldInst[$i]->getYPos()?> + 1) - obj.tileHeight; j <= <?=$arrBldInst[$i]->getYPos()?>; j++) {
											this.tileMap[i] = (this.tileMap[i] == undefined) ? [] : this.tileMap[i];

											if (i !== <?=$arrBldInst[$i]->getXPos()?> || j !== <?=$arrBldInst[$i]->getYPos()?>) {
												this.tileMap[i][j] = new BuildingPortion(obj.buildingTypeId, <?=$arrBldInst[$i]->getXPos()?> - i, <?=$arrBldInst[$i]->getYPos()?> - j);
											}
										}
									}

								<?php } ?>
							}

							g.initializeGrid();

							//handleGameState(GameState.TITLESCREEN);
							handleGameState(GameState.PLAYING);

							break;
						case GameState.TITLESCREEN:
							showIntro(canvas, true);

							window.addEventListener(pointer.DOWN, function(e) { 
								window.removeEventListener(pointer.DOWN, arguments.callee, false);
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

    	<div id="game" class="rot0deg">

			<canvas id="gameCanvas" width="1" height="1"></canvas>
			<div id="ui" class="hidden">
				<div id="top">
					Account Balance: <span id="balance"><?=$user->getBalance()?></span> Coins
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

								<li info='{"buildingId": "<?=$buildings[$i]->getId()?>"}'>
									<h2><?=$buildings[$i]->getName() . ' (' . $buildings[$i]->getXSize() . ' x ' . $buildings[$i]->getYSize() . ')'?></h2>
									<p>
										<?php if ($buildings[$i]->getProfit() > 0) { ?>
											Provides <?=$buildings[$i]->getProfit()?> coins every <?=$buildings[$i]->getLapse()?> seconds.
										<?php } else { ?>
											Decoration (Doesn't generate any profit over time)
										<?php } ?>
										<br />
										<span>Costs: <?=$buildings[$i]->getCost()?> coins</span>
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