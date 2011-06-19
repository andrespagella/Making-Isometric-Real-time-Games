// Miscellaneous objects and functions

function transitionTo(canvas, callback, alphaVal) {
	var c = canvas.getContext('2d');

	// If the function hasn't received any parameters, start with 0.02
	alphaVal = (alphaVal == undefined) ? 0.02 : parseFloat(alphaVal) + 0.02;
	
	// Set the color to white				
	c.fillStyle = '#FFFFFF';

	// Set the Global Alpha
	c.globalAlpha = alphaVal;

	// Make a rectangle as big as the canvas
	c.fillRect(0, 0, canvas.width, canvas.height);
	
	if (alphaVal < 1.0) {
		setTimeout(function() {
			transitionTo(canvas, callback, alphaVal);
		}, 50);
	} else {
		if (callback != undefined && typeof(callback) === "function") {
			callback();
		}
	}
}

function preloadResources(canvas, callback) {
	var c = canvas.getContext('2d');

	var rl = new ResourceLoader(printProgressBar, callback);
				
	rl.addResource('../img/tile.png', null, ResourceType.IMAGE);
	rl.addResource('../img/ui-icons.png', null, ResourceType.IMAGE);
	rl.addResource('../img/spritesheet.png', null, ResourceType.IMAGE);

	rl.addResource('../sounds/title.ogg', 'audio/ogg', ResourceType.SOUND);
	rl.addResource('../sounds/title.mp3', 'audio/mp3', ResourceType.SOUND);

	rl.startPreloading();

	printProgressBar();

	function printProgressBar() {
		var percent = Math.floor((rl.resourcesLoaded * 100) / rl.resources.length);

		var cwidth = Math.floor((percent * (canvas.width - 1)) / 100);

		c.fillStyle = '#000000';
		c.fillRect(0, canvas.height - 30, canvas.width, canvas.height);

		c.fillStyle = '#FFFFFF';
		c.fillRect(1, canvas.height - 28, cwidth, canvas.height - 6);
	}
}

function showCredits(canvas, alphaVal, callback) {
	var c = canvas.getContext('2d');
	var phrase = "Developed by you";
	var mt = c.measureText (phrase);
	var xCoord = (canvas.width / 2) - (mt.width / 2);
	var yCoord = (canvas.height / 2) - 10;

	alphaVal = (alphaVal == undefined) ? 0.02 : parseFloat(alphaVal) + 0.02;

	// Clear the canvas
	c.clearRect (0, 0, canvas.width, canvas.height);

	// Set the Global Alpha
	c.globalAlpha = alphaVal;

	// Set the color to black
	c.fillStyle = '#000000';
	c.font = 'bold 20px Arial, sans-serif';					
	c.fillText (phrase, xCoord, yCoord);

	if (alphaVal < 1.0) {
		setTimeout(function() {
			showCredits(canvas, alphaVal, callback);
		}, 30);
	} else {
		if (callback !== undefined && typeof(callback) === "function") {
			setTimeout(function() {
				callback();	
			}, 2000);
		}
	}
}

function showIntro (canvas, showPhrase) {
	var c = canvas.getContext('2d');
	var phrase = "Click or tap the screen to start the game";

	// Clear the canvas
	c.clearRect (0, 0, canvas.width, canvas.height);
	
	// Make a nice blue gradient
	var grd = c.createLinearGradient(0, canvas.height, canvas.width, 0);
	grd.addColorStop(0, '#ceefff');
	grd.addColorStop(1, '#52bcff');
	
	c.fillStyle = grd;
	c.fillRect(0, 0, canvas.width, canvas.height);

	var logoImg = new Image();
	logoImg.src = '../img/logo.png';

	// Store the original width value so that we can keep the same width/height ratio later
	var originalWidth = logoImg.width;

	// Compute the new width and height values
	logoImg.width = Math.round((50 * document.body.clientWidth) / 100);
	logoImg.height = Math.round((logoImg.width * logoImg.height) / originalWidth);
	
	// Create an small utility object
	var logo = {
		img: logoImg,
		x: (canvas.width/2) - (logoImg.width/2),
		y: (canvas.height/2) - (logoImg.height/2)
	}

	// Present the image
	c.drawImage(logo.img, logo.x, logo.y, logo.img.width, logo.img.height);

	if (showPhrase) {
		// Change the color to black
		c.fillStyle = '#000000';
		c.font = 'bold 16px Arial, sans-serif';
							
		var textSize = c.measureText (phrase);
		var xCoord = (canvas.width / 2) - (textSize.width / 2);
		
		c.fillText (phrase, xCoord, (logo.y + logo.img.height) + 50);
	}
}