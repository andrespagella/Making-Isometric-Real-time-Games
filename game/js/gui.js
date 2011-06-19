// GUI

function initializeGUI(ui, canvas, g) {

	// Remove the "hide" class from the gui to show the GUI.
	ui.setAttribute("class", "");

	// Set up the event listeners
	window.addEventListener('resize', function() { g.doResize(); }, false);
	canvas.addEventListener(pointer.DOWN, function(e) { g.handleMouseDown(e); }, false);
	canvas.addEventListener(pointer.MOVE, function(e) { g.handleDrag(e); }, false);
	window.addEventListener(pointer.UP, function(e) { g.handleMouseUp(e); }, false);

	if (Modernizr.touch){
		// Detect gestures
		window.addEventListener('ongestureend', function(e) { g.handleGestureEnd(e); }, false);

		// Device orientation
		if (window.orientation) {
			window.addEventListener('orientationchange', g.handleOrientationChange, false);		
		}
	} else {
		window.addEventListener('keydown', function(e) { g.handleKeyDown(e); }, false);

		// Detect mousewheel scrolling
		window.addEventListener('mousewheel', function(e) { g.handleScroll(e); }, false);
		window.addEventListener('DOMMouseScroll', function(e) { g.handleScroll(e); }, false);
	}

	// Listen for GUI events
	ui.addEventListener(pointer.UP, function(e) {
		switch(e.target.getAttribute('id')) {
			case 'panel-toggle':
				var panelContainer = document.getElementById('panel-container');
				var classes = panelContainer.getAttribute('class');

				if (classes != null && classes.length > 0) {
					panelContainer.setAttribute('class', '');
					document.getElementById('panel-toggle').innerHTML = 'Cancel';
				} else {
					panelContainer.setAttribute('class', 'hidden');
					document.getElementById('panel-toggle').innerHTML = 'Build';
				}
				break;
			case 'select':
				selectTool(Tools.SELECT, document.getElementById('select'));
				break;
			case 'move':
				selectTool(Tools.MOVE, document.getElementById('move'));
				break;
			case 'zoomIn':
				selectTool(Tools.ZOOM_IN, document.getElementById('zoomIn'));
				break;
			case 'zoomOut':
				selectTool(Tools.ZOOM_OUT, document.getElementById('zoomOut'));
				break;
			case 'rotate':
				g.rotateGrid();
				g.draw();
				break;
			case 'demolish':
				selectTool(Tools.DEMOLISH, document.getElementById('demolish'));
				break;
			default:
				// Check if he clicked on any of the options inside the panel
				if ((e.target.tagName === 'LI' || e.target.parentNode.tagName === 'LI')) {
					var t = (e.target.tagName === 'LI') ? e.target : e.target.parentNode;
					var props = t.getAttribute("info");

					if (props !== undefined) {
						props = JSON.parse(props);

						var obj;


						switch(parseInt(props.buildingId)) {
							case 1: // Ice cream shop
								obj = new IceCreamShop();
								break;	
							case 2: // Hotel
								obj = new Hotel();
								break;
							case 3: // Cinema
								obj = new Cinema();
								break;
							default: // Tree (4) or other object.
								obj = new Tree();
								break;
						}

						g.buildHelper.current = obj;
						Tools.current = Tools.BUILD;
						break;
					}
				}

				// He didn't click on any option and actually clicked on an empty section of the UI, fallback to the canvas.
				e.srcElement = canvas;
				e.target = canvas;
				e.toElement = canvas;
				
				g.handleMouseDown(e);

				break;
		}
	}, false);
}

function refresh() {
	var processResponse = function(resp) {
    	if (resp.substr(0, 5) == 'ERROR') {
			alert("A problem ocurred while trying to sync with the service.");
		} else {
			var balanceContainer = document.getElementById('balance');

			var currBalance = parseInt(balanceContainer.innerHTML);
			var balance = parseInt(resp.substr(3, resp.length));
			balanceContainer.innerHTML = balance;
		}
	}

	sync(processResponse);

	setTimeout(function() { 
		refresh(ui); 
	}, 15000);
}

function selectTool(tool, elem) {

	// Remove the "active" class from any element inside the div#tools ul
	for (var i = 0, x = elem.parentNode.childNodes.length; i < x; i++) {
		if (elem.parentNode.childNodes[i].tagName == "LI") {
			elem.parentNode.childNodes[i].className = null;
		}
	}

	elem.setAttribute('class', 'active');

	switch(tool) {
		case Tools.SELECT:
			Tools.current = Tools.SELECT;
			break;
		case Tools.MOVE:
			Tools.current = Tools.MOVE;
			break;
		case Tools.ZOOM_IN:
			Tools.current = Tools.ZOOM_IN;
			break;
		case Tools.ZOOM_OUT:
			Tools.current = Tools.ZOOM_OUT;
			break;
		case Tools.DEMOLISH:
			Tools.current = Tools.DEMOLISH;
			break;
	}

}