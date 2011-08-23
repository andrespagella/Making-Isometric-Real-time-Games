// Final game class

function Game(canvas, game, gridSizeW, gridSizeH) {
	this.started = true;
	this.gameContainer = game;
	this.canvas = canvas;

	// Get the 2D Context
	this.c = canvas.getContext('2d');

	// Can we run the game?
	var missingDeps = [];
	var dependencies = [Modernizr.rgba, 
						Modernizr.canvas,
						Modernizr.borderradius,
						Modernizr.boxshadow,
						Modernizr.cssgradients];

	for (var i = 0, dep = dependencies.length; i < dep; i++) {
		if (!dependencies[i]) {
			missingDeps.push(dependencies[i]);
		}
	}

	if (missingDeps.length !== 0) {
		alert("This browser doesn't include some of the technologies needed to play the game");
		this.started = false;
		return;
	}

	// Tile texture
	this.tile = new Image();
	this.tile.src = "../img/tile.png";

	// Grid dimensions
	this.grid = {
		width: gridSizeW,
		height: gridSizeH
	}

	// Tile map matrix
	this.tileMap = [];

	// Drag helper
	this.dragHelper = {
		active: false,
		x: 0,
		y: 0	
	}

	// Zoom helper, 3 zoom levels supported
	this.zoomHelper = {
		level: 1,
		NORMAL: 1,
		FAR: 0.50,
		CLOSE: 2
	}

	// Build helper
	this.buildHelper = {
		current: null
	}

	// Scroll position helper, keeps track of scrolling
	this.scrollPosition = { x: 0, y: 0 }

	// Default zoom level
	this.tile.width *= this.zoomHelper.level;
	this.tile.height *= this.zoomHelper.level;

	// Initially center the starting point horizontally and vertically
	this.scrollPosition.y -= (this.grid.height * this.zoomHelper.level) + this.scrollPosition.y;
	this.scrollPosition.x -= (this.grid.width * this.zoomHelper.level) + this.scrollPosition.x;
}

Game.prototype.handleGestureEnd = function(e) {
	alert('fd');
	e.preventDefault();

	if (Math.floor(e.scale) == 0) {
		this.zoomIn();
	} else {
		this.zoomOut();
	}

	this.draw();
}

Game.prototype.handleScroll = function(e) {
	e.preventDefault();

	var scrollValue = (e.wheelDelta == undefined) ? e.detail * -1 : e.wheelDelta;

	if (scrollValue >= 0) {
		this.zoomIn();
	} else {
		this.zoomOut();
	}

	this.draw();
}

Game.prototype.handleKeyDown = function(e) {
	switch (e.keyCode) {
		case Keys.UP:
		case Keys.W:
			this.scrollPosition.y += 20;
			break;
		case Keys.DOWN:
		case Keys.S:
			this.scrollPosition.y -= 20;
			break;
		case Keys.LEFT:
		case Keys.A:
			this.scrollPosition.x += 20;
			break;
		case Keys.RIGHT:
		case Keys.D:
			this.scrollPosition.x -= 20;
			break;
		case Keys.X:
			this.zoomIn();
			break;
		case Keys.Z:
			this.zoomOut();
			break;
		case Keys.R:
			this.rotateGrid();
			break;
	}

	this.draw();
}

Game.prototype.handleDrag = function(e) {
	e.preventDefault();

	switch (Tools.current) {
		case Tools.MOVE:
			if (this.dragHelper.active) {
				var x, y;

				if (Modernizr.touch) {
					x = e.touches[0].pageX;
					y = e.touches[0].pageY;
				} else {
					x = e.clientX;
					y = e.clientY;
				}

				// Smooth scrolling effect
				this.scrollPosition.x -= Math.round((this.dragHelper.x - x) / 18);
				this.scrollPosition.y -= Math.round((this.dragHelper.y - y) / 18);

				this.draw();
			}
			break;
	}
}

Game.prototype.handleMouseUp = function(e) {
	e.preventDefault();

	switch (Tools.current) {
		case Tools.MOVE:
			this.dragHelper.active = false;
			break;
	}
}

Game.prototype.checkIfTileIsBusy = function(obj, row, col) {
	for (var i = (row + 1) - obj.tileWidth; i <= row; i++) {
		for (var j = (col + 1) - obj.tileHeight; j <= col; j++) {
			if (this.tileMap[i] != undefined && this.tileMap[i][j] != null) {
				return true;
			}
		}
	}	

	return false;
}

Game.prototype.handleMouseDown = function(e) {
	e.preventDefault();

	switch (Tools.current) {
		case Tools.BUILD:
            if (this.buildHelper.current != null) {
            	var pos = this.translatePixelsToMatrix(e.clientX, e.clientY);

            	// Can we place the element on the grid?
            	if (!this.checkIfTileIsBusy(this.buildHelper.current, pos.row, pos.col)) {

            		var obj = this.buildHelper.current;
            		var t = this;

            		var processResponse = function(resp) {
	            		if (resp.substr(0, 3) == 'OK:') {
	            			var buildingInstanceId = resp.substr(3, resp.length);
	            			for (var i = (pos.row + 1) - obj.tileWidth; i <= pos.row; i++) {
								for (var j = (pos.col + 1) - obj.tileHeight; j <= pos.col; j++) {
									t.tileMap[i] = (t.tileMap[i] == undefined) ? [] : t.tileMap[i];

									t.tileMap[i][j] = (i === pos.row && j === pos.col) ? obj : new BuildingPortion(obj.buildingTypeId, i, j);
								}
							}
	            		} else {
	            			alert("An error ocurred while trying to purchase the building!")
	            		}

	            		t.draw();
	            	}

	            	purchase(obj.buildingTypeId, pos.row, pos.col, processResponse);
            	} else {
            		alert("Unable purchase building on this position");
            	}
        	}
			
			break;
		case Tools.MOVE:
			var x, y;

			if (Modernizr.touch) {
				x = e.touches[0].pageX;
				y = e.touches[0].pageY;
			} else {
				x = e.clientX;
				y = e.clientY;
			}

			this.dragHelper.active = true;
			this.dragHelper.x = x;
			this.dragHelper.y = y;
			break;
		case Tools.ZOOM_IN:
			this.zoomIn();
			break;
		case Tools.ZOOM_OUT:
			this.zoomOut();
			break;
		case Tools.DEMOLISH:
			
			var pos = this.translatePixelsToMatrix(e.clientX, e.clientY);

			if (this.tileMap[pos.row] != undefined && this.tileMap[pos.row][pos.col] != undefined) {
				var obj = this.tileMap[pos.row][pos.col];

				// Not a building, a building portion. Grab the reference to the original building.
				if (obj instanceof BuildingPortion) {
					pos.row += obj.x;
					pos.col += obj.y;
					obj = this.tileMap[pos.row][pos.col];
				}

				var t = this;
				var processResponse = function(resp) {
	            	if (resp.substr(0, 2) == 'OK') {
						// Check for sorrounding pixels and destroy BuildingPortions too.
						for (var i = (pos.row + 1) - obj.tileWidth; i <= pos.row; i++) {
							for (var j = (pos.col + 1) - obj.tileHeight; j <= pos.col; j++) {
								t.tileMap[i][j] = null;
							}
						}
					} else {
						alert("A problem ocurred while trying to demolish this building");
					}

					t.draw();
				}

				demolish(pos.row, pos.col, processResponse);
			}

			break;
	}
    
    this.draw();
}

Game.prototype.doResize = function() {
	this.canvas.width = document.body.clientWidth;
	this.canvas.height = document.body.clientHeight;

	this.draw();
}

Game.prototype.translatePixelsToMatrix = function(x, y) {
	var tileHeight = this.tile.height * this.zoomHelper.level;
	var tileWidth = this.tile.width * this.zoomHelper.level;

	var gridOffsetY = (this.grid.height * this.zoomHelper.level) + this.scrollPosition.y;
	var gridOffsetX = (this.grid.width * this.zoomHelper.level);

	// By default the grid appears centered horizontally
	gridOffsetX += (this.canvas.width / 2) - ((tileWidth / 2) * this.zoomHelper.level) + this.scrollPosition.x;

	var col = (2 * (y - gridOffsetY) - x + gridOffsetX) / 2;
	var row = x + col - gridOffsetX - tileHeight;
		
	col = Math.round(col / tileHeight);
	row = Math.round(row / tileHeight);
	
	return {
		row: row,
		col: col
	}
}

Game.prototype.draw = function(srcX, srcY, destX, destY) {
	srcX = (srcX === undefined) ? 0 : srcX;
	srcY = (srcY === undefined) ? 0 : srcY;
	destX = (destX === undefined) ? this.canvas.width : destX;
	destY = (destY === undefined) ? this.canvas.height : destY;

	this.c.clearRect (0, 0, this.canvas.width, this.canvas.height);
	this.c.fillStyle = '#0C3B00'; // Green background
	this.c.fillRect (0, 0, this.canvas.width, this.canvas.height);

	var pos_TL = this.translatePixelsToMatrix(1, 1);
	var pos_BL = this.translatePixelsToMatrix(1, this.canvas.height);
	var pos_TR = this.translatePixelsToMatrix(this.canvas.width, 1);
	var pos_BR = this.translatePixelsToMatrix(this.canvas.width, this.canvas.height);

	var startRow = pos_TL.row;
	var startCol = pos_TR.col;
	var rowCount = pos_BR.row + 1;
	var colCount = pos_BL.col + 1;

	startRow = (startRow < 0) ? 0 : startRow;
	startCol = (startCol < 0) ? 0 : startCol;

	rowCount = (rowCount > this.grid.width) ? this.grid.width : rowCount;
	colCount = (colCount > this.grid.height) ? this.grid.height : colCount;

	var tileHeight = this.tile.height * this.zoomHelper.level;
	var tileWidth = this.tile.width * this.zoomHelper.level;

	//console.time("drawTime");
	for (var row = startRow; row < rowCount; row++) {
		for (var col = startCol; col < colCount; col++) {
			var xpos = (row - col) * tileHeight + (this.grid.width * this.zoomHelper.level);
			xpos += (this.canvas.width / 2) - ((tileWidth / 2) * this.zoomHelper.level) + this.scrollPosition.x;

			var ypos = (row + col) * (tileHeight / 2) + (this.grid.height * this.zoomHelper.level) + this.scrollPosition.y;

			if (Math.round(xpos) + tileWidth >= srcX &&
				Math.round(ypos) + tileHeight >= srcY &&
				Math.round(xpos) <= destX &&
				Math.round(ypos) <= destY) {

				this.c.drawImage(this.tile, Math.round(xpos), Math.round(ypos), tileWidth, tileHeight);

				if (this.tileMap[row] != null && this.tileMap[row][col] != null) {
					
					if (this.tileMap[row][col] instanceof Tree ||
						this.tileMap[row][col] instanceof Cinema ||
						this.tileMap[row][col] instanceof IceCreamShop ||
						this.tileMap[row][col] instanceof Hotel) {
						ypos -= (this.tileMap[row][col].height * this.zoomHelper.level) - tileHeight;
						xpos -= ((this.tileMap[row][col].width * this.zoomHelper.level) / 2) - (tileWidth / 2);

						this.tileMap[row][col].sprite.setPosition(xpos, ypos);
						this.tileMap[row][col].sprite.zoomLevel = this.zoomHelper.level;
						this.tileMap[row][col].sprite.draw(this.c, true);
					}

				}
			}
		}
	}
	
	//console.timeEnd("drawTime");
}

Game.prototype.zoomIn = function() {
	switch(this.zoomHelper.level) {
		case this.zoomHelper.NORMAL:
			this.zoomHelper.level = this.zoomHelper.CLOSE;
			break;
		case this.zoomHelper.FAR:
			this.zoomHelper.level = this.zoomHelper.NORMAL;
			break;
		case this.zoomHelper.CLOSE:
			return;
	}

	// Center the view
	this.scrollPosition.y -= (this.grid.height * this.zoomHelper.level) + this.scrollPosition.y;
	this.scrollPosition.x -= (this.grid.width * this.zoomHelper.level) + this.scrollPosition.x;
}

Game.prototype.zoomOut = function() {
	switch(this.zoomHelper.level) {
		case this.zoomHelper.NORMAL:
			this.zoomHelper.level = this.zoomHelper.FAR;
			break;
		case this.zoomHelper.CLOSE:
			this.zoomHelper.level = this.zoomHelper.NORMAL;
			break;
		case this.zoomHelper.FAR:
			return;
	}

	// Center the view
	this.scrollPosition.y -= (this.grid.height * this.zoomHelper.level) + this.scrollPosition.y;
	this.scrollPosition.x -= (this.grid.width * this.zoomHelper.level) + this.scrollPosition.x;
}

Game.prototype.rotateGrid = function(mW, mH, sW, sH) {
    var m = [];

    mW = (mW === undefined) ? this.grid.width : mW;
    mH = (mH === undefined) ? this.grid.height : mH;

    sW = (sW === undefined) ? 0 : sW;
    sH = (sH === undefined) ? 0 : sH;

    for (var i = sW; i < mW; i++) {
        for (var j = sH; j < mH; j++) {
        	var row = (mW - j) - 1;

        	if (this.tileMap[row] !== undefined && this.tileMap[row][i]) {
        		m[i] = (m[i] === undefined) ? [] : m[i];
        		m[i][j] = this.tileMap[row][i];
        	}
        }
    }

    this.tileMap = m;
}